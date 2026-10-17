
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migración requiere la columna creada por la migración anterior.
        if (!Schema::hasColumn('tbl_detalles_orden', 'costo_unitario_historico')) {
            throw new RuntimeException(
                'Primero ejecuta la migracion de costo_unitario_historico.'
            );
        }

        // Registrar importes financieros únicamente al confirmar el pago.
        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->decimal('venta_neta_confirmada', 12, 2)->nullable();
            $table->decimal('costo_total_confirmado', 12, 2)->nullable();
            $table->decimal('ganancia_bruta_confirmada', 12, 2)->nullable();
            $table->timestampTz('fecha_confirmacion_financiera')->nullable();
        });

        // Función ejecutada cuando una orden pasa al estado pagada.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION sidan_registrar_rentabilidad_confirmada()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                fecha_pago TIMESTAMPTZ;
            BEGIN
                -- Solo órdenes de productos realmente pagadas.
                IF NEW.tipo_orden <> 'productos'
                   OR NEW.estado <> 'pagada'
                   OR OLD.estado = 'pagada' THEN
                    RETURN NEW;
                END IF;

                -- Comprobar que existe un pago confirmado
                -- por el mismo importe total de la orden.
                SELECT p.fecha_confirmacion
                INTO fecha_pago
                FROM tbl_pagos AS p
                WHERE p.id_orden = NEW.id_orden
                  AND p.estado = 'confirmado'
                  AND p.monto = NEW.total
                ORDER BY p.id_pago DESC
                LIMIT 1;

                -- No permitir contabilizar una venta sin pago válido.
                IF NOT FOUND THEN
                    RAISE EXCEPTION
                        'SIDAN: la orden % no tiene un pago confirmado por su importe total.',
                        NEW.id_orden;
                END IF;

                -- Registrar los importes definitivos de los artículos.
                UPDATE tbl_detalles_orden AS d
                SET
                    venta_neta_confirmada =
                        d.subtotal - COALESCE(d.descuento, 0),

                    costo_total_confirmado =
                        CASE
                            WHEN d.costo_unitario_historico IS NOT NULL
                            THEN d.cantidad * d.costo_unitario_historico
                            ELSE NULL
                        END,

                    ganancia_bruta_confirmada =
                        CASE
                            WHEN d.costo_unitario_historico IS NOT NULL
                            THEN d.subtotal - COALESCE(d.descuento, 0)
                                 - d.cantidad * d.costo_unitario_historico
                            ELSE NULL
                        END,

                    fecha_confirmacion_financiera =
                        COALESCE(fecha_pago, NOW())

                WHERE d.id_orden = NEW.id_orden
                  AND d.id_item_actividad IS NOT NULL
                  AND d.fecha_confirmacion_financiera IS NULL;

                RETURN NEW;
            END;
            $$;
        SQL);

        // El trigger actúa cuando cambia el estado de la orden a pagada.
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_sidan_rentabilidad_confirmada
            AFTER UPDATE OF estado ON tbl_ordenes
            FOR EACH ROW
            WHEN (
                OLD.estado IS DISTINCT FROM NEW.estado
                AND NEW.estado = 'pagada'
            )
            EXECUTE FUNCTION sidan_registrar_rentabilidad_confirmada();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_sidan_rentabilidad_confirmada ON tbl_ordenes;'
        );

        DB::unprepared(
            'DROP FUNCTION IF EXISTS sidan_registrar_rentabilidad_confirmada();'
        );

        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->dropColumn([
                'venta_neta_confirmada',
                'costo_total_confirmado',
                'ganancia_bruta_confirmada',
                'fecha_confirmacion_financiera',
            ]);
        });
    }
};
