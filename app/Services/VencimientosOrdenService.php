<?php

namespace App\Services;

use App\Models\Orden;
use Illuminate\Support\Facades\DB;

class VencimientosOrdenService
{
    public function sincronizar(): int
    {
        $cantidad=0;
        // Procesar órdenes una a una para coordinar con el bloqueo existente.
        Orden::where('estado','pendiente_pago')->whereNotNull('limite_saldo_en')
            ->where('limite_saldo_en','<=',now())->orderBy('id_orden')
            ->chunkById(100, function ($ordenes) use (&$cantidad) {
                foreach ($ordenes as $orden) {
                    DB::transaction(function () use ($orden,&$cantidad) {
                        $actual=Orden::whereKey($orden->id_orden)->lockForUpdate()->first();
                        if (!$actual || $actual->estado!=='pendiente_pago' || !$actual->limite_saldo_en
                            || $actual->limite_saldo_en->isFuture()) return;
                        $pago=$actual->pago;
                        // Una transferencia enviada antes del plazo puede estar esperando conciliación.
                        if ($pago?->proveedor==='transferencia' && $pago->abonos()->where('estado','pendiente')->exists()) return;
                        $acreditado=$pago && in_array($pago->proveedor,['efectivo','transferencia'],true)
                            ? $pago->montoAcreditadoCentavos() : 0;
                        $actual->update([
                            'estado'=>'vencida',
                            'expira_en'=>now(),
                            'cierre_financiero_estado'=>$acreditado>0?'pendiente_revision':'no_aplica',
                        ]);
                        if ($actual->tipo_orden==='inscripcion' && $actual->inscripcion?->estado==='pendiente_pago') {
                            $actual->inscripcion->update(['estado'=>'vencida','expira_en'=>now()]);
                        }
                        $cantidad++;
                    },3);
                }
            });
        return $cantidad;
    }
}
