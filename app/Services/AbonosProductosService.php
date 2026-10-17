<?php

namespace App\Services;

use App\Models\AbonoTransferencia;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** El único origen de verdad del reparto son las aplicaciones confirmadas. */
class AbonosProductosService
{
    public function centavos(mixed $valor): int { return (int) round((float) $valor * 100); }
    public function dinero(int $valor): string { return number_format($valor/100, 2, '.', ''); }

    public function neto(DetalleOrden $d): int
    {
        return max(0, $this->centavos($d->subtotal) - $this->centavos($d->descuento ?? 0));
    }

    public function minimo(DetalleOrden $d): int
    {
        $total=$this->neto($d);
        if (!$d->permite_anticipo || !$d->tipo_anticipo) return $total;
        $valor=$this->centavos($d->valor_anticipo);
        if ($d->tipo_anticipo==='porcentaje') {
            return min($total, intdiv($total*$valor + 9999,10000)); // redondeo arriba al centavo
        }
        return min($total, $valor * (int)$d->cantidad); // monto fijo por unidad
    }

    public function minimoOrden(Orden $orden): int
    {
        return $orden->detalles->sum(fn($d)=>$this->minimo($d));
    }

    
    public function aplicado(DetalleOrden $detalle): int
    {
        $orden = $detalle->orden()
            ->with('pago')
            ->first();

        if (
            $orden?->estado === 'pagada'
            && $orden->pago?->proveedor === 'wompi'
            && $orden->pago?->estado === 'confirmado'
        ) {
            return $this->neto($detalle);
        }

        return $this->centavos(
            DB::table('tbl_aplicaciones_abono')
                ->where(
                    'id_detalle_orden',
                    $detalle->id_detalle_orden
                )
                ->sum('monto')
        );
    }


    public function faltanteAnticipo(Orden $orden): int
    {
        return (int) $orden->detalles->sum(fn($d)=>max(0,$this->minimo($d)-$this->aplicado($d)));
    }

    /** Ejecutar bajo bloqueo de orden; no repartir comprobantes aún no acreditados. */
    public function distribuir(Orden $orden, AbonoTransferencia $abono): void
    {
        if ($abono->estado!=='confirmado') throw ValidationException::withMessages(['pago'=>'El abono no está confirmado.']);
        if (DB::table('tbl_aplicaciones_abono')->where('id_abono',$abono->id_abono)->exists()) {
            throw ValidationException::withMessages(['pago'=>'El abono ya fue aplicado a los productos.']);
        }
        $restante=$this->centavos($abono->monto_recibido);
        if ($orden->tipo_orden!=='productos') return; // Inscripciones no contienen productos entregables.
        $detalles=$orden->detalles->sortBy('id_detalle_orden')->values();
        // 1) Pago completo obligado, 2) anticipo mínimo, 3) resto de los artículos parciales.
        foreach ([1,2,3] as $etapa) {
            foreach ($detalles as $detalle) {
                if ($restante<=0) break;
                $esParcial=(bool)$detalle->permite_anticipo;
                if (($etapa===1 && $esParcial) || ($etapa>1 && !$esParcial)) continue;
                $objetivo=$etapa===2 ? $this->minimo($detalle) : $this->neto($detalle);
                $anterior=$this->aplicado($detalle);
                $yaAplicadoEnAbono=$this->centavos(DB::table('tbl_aplicaciones_abono')
                    ->where('id_abono',$abono->id_abono)->where('id_detalle_orden',$detalle->id_detalle_orden)->sum('monto'));
                $asignar=min($restante,max(0,$objetivo-$anterior));
                if ($asignar===0) continue;
                DB::table('tbl_aplicaciones_abono')->updateOrInsert(
                    ['id_abono'=>$abono->id_abono,'id_detalle_orden'=>$detalle->id_detalle_orden],
                    ['monto'=>$this->dinero($yaAplicadoEnAbono+$asignar), 'updated_at'=>now(), 'created_at'=>now()]
                );
                $restante-=$asignar;
            }
        }
        if ($restante!==0) {
            throw ValidationException::withMessages(['pago'=>'No se pudo distribuir completamente el abono. Revisá los totales de la orden.']);
        }
    }

    /**
     * El plazo de un pedido de productos es el cierre de ventas (venta_hasta)
     * más cercano entre los productos incluidos. NO depende de cuándo
     * comienza la actividad ni se reduce automáticamente a siete días.
     *
     * Para inscripciones se conserva el límite propio de inscripción/actividad
     * y el máximo de siete días que existía en el flujo anterior.
     */
    public function limite(Orden $orden): \Carbon\CarbonInterface
    {
        $ahoraUtc = now('UTC');
        $limiteUtc = null;

        if ($orden->tipo_orden === 'productos') {
            $orden->loadMissing('detalles');
            $idsItems = $orden->detalles
                ->pluck('id_item_actividad')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($idsItems) {
                $items = \App\Models\ItemActividad::query()
                    ->whereIn('id_item_actividad', $idsItems)
                    ->get(['id_item_actividad', 'venta_hasta']);

                foreach ($items as $item) {
                    if (!$item->venta_hasta) {
                        continue;
                    }

                    $cierreUtc = $item->venta_hasta->copy()->utc();
                    if ($limiteUtc === null || $cierreUtc->lt($limiteUtc)) {
                        $limiteUtc = $cierreUtc;
                    }
                }
            }

            // Solo cuando NINGÚN producto tiene cierre configurado:
            // se conserva un plazo predeterminado de 7 días.
            $limiteUtc ??= $ahoraUtc->copy()->addDays(7);
        } else {
            // Las inscripciones mantienen una regla independiente.
            $limiteUtc = $ahoraUtc->copy()->addDays(7);
            foreach ([
                $orden->actividad?->inscripcion_hasta,
                $orden->actividad?->realizacion_desde,
            ] as $fecha) {
                if (!$fecha) {
                    continue;
                }

                $cierreUtc = \Carbon\Carbon::parse($fecha)->utc();
                if ($cierreUtc->lt($limiteUtc)) {
                    $limiteUtc = $cierreUtc;
                }
            }
        }

        if ($limiteUtc->lessThanOrEqualTo($ahoraUtc)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'orden' => 'La fecha límite de disponibilidad ya venció. No se puede iniciar un pago nuevo.',
            ]);
        }

        return $limiteUtc;
    }

    public function entregar(Orden $orden, DetalleOrden $detalle, int $cantidad, int $admin, ?string $nota): void
    {
        if ($detalle->id_orden!==$orden->id_orden || $orden->tipo_orden!=='productos') {
            throw ValidationException::withMessages(['entrega'=>'El producto no pertenece a la orden.']);
        }
        if ($this->aplicado($detalle)<$this->neto($detalle) && $this->neto($detalle)>0) {
            throw ValidationException::withMessages(['entrega'=>'El producto debe estar completamente pagado.']);
        }
        if ($cantidad<1 || $cantidad!==(int)$detalle->cantidad-(int)$detalle->cantidad_entregada) {
            throw ValidationException::withMessages(['entrega'=>'Cantidad de entrega inválida.']);
        }
        DB::table('tbl_entregas_detalle')->insert([
            'id_detalle_orden'=>$detalle->id_detalle_orden,'cantidad'=>$cantidad,
            'entregado_por'=>$admin,'observacion'=>$nota,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $detalle->increment('cantidad_entregada',$cantidad);
        // Si el comprador retira un producto que ya tenía pago total después
        // del cierre, mantener el desglose financiero consistente.
        $cierre=DB::table('tbl_cierres_orden')->where('id_orden',$orden->id_orden)->first();
        if ($cierre && $cierre->clasificacion==='pendiente_clasificacion') {
            $monto=$this->neto($detalle);
            DB::table('tbl_cierres_orden')->where('id_cierre',$cierre->id_cierre)->update([
                'importe_productos_entregados'=>$this->dinero(min(
                    $this->centavos($cierre->dinero_recibido),
                    $this->centavos($cierre->importe_productos_entregados)+$monto
                )),
                'anticipo_sin_entrega'=>$this->dinero(max(0,
                    $this->centavos($cierre->anticipo_sin_entrega)-$monto
                )),
                'updated_at'=>now(),
            ]);
        }
        // Solo el producto completo, no el anticipo, se contabiliza como venta.
        // La confirmación posterior de toda la orden respeta el timestamp existente.
        if ($detalle->fecha_confirmacion_financiera===null) {
            $costo=$detalle->costo_unitario_historico===null ? null : $this->centavos($detalle->costo_unitario_historico)*(int)$detalle->cantidad;
            $venta=$this->neto($detalle);
            $detalle->update([
                'venta_neta_confirmada'=>$this->dinero($venta),
                'costo_total_confirmado'=>$costo===null?null:$this->dinero($costo),
                'ganancia_bruta_confirmada'=>$costo===null?null:$this->dinero($venta-$costo),
                'fecha_confirmacion_financiera'=>now(),
            ]);
        }
    }
}
