<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_items_actividad', function (Blueprint $t) {
            $t->boolean('permite_anticipo')->default(false);
            $t->string('tipo_anticipo', 15)->nullable();
            $t->decimal('valor_anticipo', 12, 2)->nullable();
        });
        Schema::table('tbl_detalles_orden', function (Blueprint $t) {
            $t->boolean('permite_anticipo')->default(false);
            $t->string('tipo_anticipo', 15)->nullable();
            $t->decimal('valor_anticipo', 12, 2)->nullable();
            $t->unsignedInteger('cantidad_entregada')->default(0);
        });
        Schema::table('tbl_ordenes', function (Blueprint $t) {
            $t->string('modalidad_pago', 20)->default('completo');
            $t->decimal('minimo_inicial', 12, 2)->nullable();
            $t->timestampTz('limite_saldo_en')->nullable();
            $t->string('cierre_financiero_estado', 32)->default('no_aplica');
        });

        // Conservamos esta tabla y sus identificadores: también representa abonos en efectivo.
        // No renombrarla evita romper el historial y los comprobantes ya emitidos.
        DB::statement('ALTER TABLE tbl_abonos_transferencia ALTER COLUMN comprobante_path DROP NOT NULL');
        Schema::table('tbl_abonos_transferencia', function (Blueprint $t) {
            $t->string('metodo', 20)->default('transferencia');
        });
        Schema::create('tbl_aplicaciones_abono', function (Blueprint $t) {
            $t->id('id_aplicacion');
            $t->foreignId('id_abono')->constrained('tbl_abonos_transferencia','id_abono')->restrictOnDelete();
            $t->foreignId('id_detalle_orden')->constrained('tbl_detalles_orden','id_detalle_orden')->restrictOnDelete();
            $t->decimal('monto', 12, 2);
            $t->timestampsTz();
            $t->unique(['id_abono', 'id_detalle_orden'], 'uq_abono_por_detalle');
        });
        Schema::create('tbl_entregas_detalle', function (Blueprint $t) {
            $t->id('id_entrega');
            $t->foreignId('id_detalle_orden')->constrained('tbl_detalles_orden','id_detalle_orden')->restrictOnDelete();
            $t->unsignedInteger('cantidad');
            $t->unsignedBigInteger('entregado_por');
            $t->text('observacion')->nullable();
            $t->timestampsTz();
        });
        Schema::create('tbl_cierres_orden', function (Blueprint $t) {
            $t->id('id_cierre');
            $t->foreignId('id_orden')->unique()->constrained('tbl_ordenes','id_orden')->restrictOnDelete();
            $t->unsignedBigInteger('autorizado_por');
            $t->decimal('dinero_recibido', 12, 2);
            $t->decimal('importe_productos_entregados', 12, 2);
            $t->decimal('anticipo_sin_entrega', 12, 2);
            $t->string('clasificacion', 32); // pendiente_clasificacion o ingreso_no_ejercido
            $t->text('justificacion');
            $t->timestampsTz();
        });
        // Integridad monetaria y de cantidades para impedir doble asignación.
        DB::statement('ALTER TABLE tbl_aplicaciones_abono ADD CONSTRAINT chk_aplicacion_positiva CHECK (monto > 0)');
        DB::statement('ALTER TABLE tbl_entregas_detalle ADD CONSTRAINT chk_entrega_positiva CHECK (cantidad > 0)');
        DB::statement("ALTER TABLE tbl_items_actividad ADD CONSTRAINT chk_item_anticipo CHECK ((NOT permite_anticipo AND tipo_anticipo IS NULL AND valor_anticipo IS NULL) OR (permite_anticipo AND tipo_anticipo IN ('porcentaje','monto_fijo') AND valor_anticipo > 0 AND (tipo_anticipo <> 'porcentaje' OR valor_anticipo <= 100)))");
        // La rentabilidad de productos físicos se reconoce al entregarse.
        // Los servicios conservan el comportamiento previo de pago confirmado.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION sidan_registrar_rentabilidad_confirmada()
            RETURNS TRIGGER LANGUAGE plpgsql AS $$
            DECLARE fecha_pago TIMESTAMPTZ;
            BEGIN
                IF NEW.tipo_orden <> 'productos' OR NEW.estado <> 'pagada'
                   OR OLD.estado='pagada' THEN RETURN NEW; END IF;
                SELECT p.fecha_confirmacion INTO fecha_pago FROM tbl_pagos p
                WHERE p.id_orden=NEW.id_orden AND p.estado='confirmado' AND p.monto=NEW.total
                ORDER BY p.id_pago DESC LIMIT 1;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'SIDAN: pago no confirmado para orden %',NEW.id_orden;
                END IF;
                UPDATE tbl_detalles_orden d SET
                    venta_neta_confirmada=d.subtotal-COALESCE(d.descuento,0),
                    costo_total_confirmado=CASE WHEN d.costo_unitario_historico IS NULL THEN NULL
                        ELSE d.cantidad*d.costo_unitario_historico END,
                    ganancia_bruta_confirmada=CASE WHEN d.costo_unitario_historico IS NULL THEN NULL
                        ELSE d.subtotal-COALESCE(d.descuento,0)-d.cantidad*d.costo_unitario_historico END,
                    fecha_confirmacion_financiera=COALESCE(fecha_pago, NOW())
                WHERE d.id_orden=NEW.id_orden AND d.id_item_actividad IS NOT NULL
                    AND d.fecha_confirmacion_financiera IS NULL
                    AND (d.tipo_concepto<>'producto' OR d.cantidad_entregada>=d.cantidad);
                RETURN NEW;
            END;
            $$;
        SQL);
        // Los pagos históricos en efectivo ahora son abonos auditables, sin inventar comprobantes bancarios.
        DB::table('tbl_pagos')->where('proveedor','efectivo')->orderBy('id_pago')->chunkById(100, function ($pagos) {
            foreach ($pagos as $pago) {
                if (DB::table('tbl_abonos_transferencia')->where('id_pago',$pago->id_pago)->exists()) continue;
                DB::table('tbl_abonos_transferencia')->insert([
                    'id_pago'=>$pago->id_pago, 'metodo'=>'efectivo', 'comprobante_path'=>null,
                    'estado'=>match($pago->estado) {'confirmado'=>'confirmado','rechazado'=>'rechazado',default=>'pendiente'},
                    'monto_recibido'=>$pago->estado==='confirmado'?$pago->monto:null,
                    'revisado_por'=>$pago->revisado_por, 'revisado_en'=>$pago->revisado_en,
                    'created_at'=>$pago->created_at??now(), 'updated_at'=>$pago->updated_at??now(),
                ]);
            }
        },'id_pago');
        // Reconciliar abonos históricos en orden determinista sin cambiar importes o su estado.
        $ids = DB::table('tbl_pagos')->whereIn('proveedor',['efectivo','transferencia'])->pluck('id_pago');
        foreach ($ids as $idPago) {
            $pago=DB::table('tbl_pagos')->where('id_pago',$idPago)->first();
            $detalles=DB::table('tbl_detalles_orden')->where('id_orden',$pago->id_orden)->orderBy('id_detalle_orden')->get();
            $saldo=[];
            foreach($detalles as $d) $saldo[$d->id_detalle_orden]=max(0,(int)round(((float)$d->subtotal-(float)($d->descuento??0))*100));
            foreach (DB::table('tbl_abonos_transferencia')->where('id_pago',$idPago)->where('estado','confirmado')->orderBy('id_abono')->get() as $abono) {
                $restante=(int)round((float)$abono->monto_recibido*100);
                foreach ($detalles as $d) {
                    if ($restante<=0) break;
                    $asignar=min($restante,$saldo[$d->id_detalle_orden]);
                    if ($asignar<=0) continue;
                    DB::table('tbl_aplicaciones_abono')->insert(['id_abono'=>$abono->id_abono,'id_detalle_orden'=>$d->id_detalle_orden,'monto'=>number_format($asignar/100,2,'.',''),'created_at'=>now(),'updated_at'=>now()]);
                    $restante-=$asignar; $saldo[$d->id_detalle_orden]-=$asignar;
                }
            }
        }
    }

    public function down(): void
    {
        // NO automatizar rollback si ya existen cobros / entregas: perdería el historial.
        throw new RuntimeException('Reversión manual supervisada requerida: hay posibles abonos y entregas reales.');
    }
};
