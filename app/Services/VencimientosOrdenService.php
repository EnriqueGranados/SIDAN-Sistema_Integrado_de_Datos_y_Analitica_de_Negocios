<?php

namespace App\Services;

use App\Models\Orden;
use Illuminate\Support\Facades\DB;

class VencimientosOrdenService
{
    public function sincronizar(): int
    {
        $cantidad = 0;
        // Procesar también las órdenes antiguas que todavía usan expira_en.
        Orden::where('estado', 'pendiente_pago')
            ->where(function ($q) {
                $q->where('limite_saldo_en', '<=', now('UTC'))
                    ->orWhere(fn ($legacy) => $legacy->whereNull('limite_saldo_en')
                        ->whereNotNull('expira_en')->where('expira_en', '<=', now('UTC')));
            })->orderBy('id_orden')->chunkById(100, function ($ordenes) use (&$cantidad) {
                foreach ($ordenes as $o) {
                    DB::transaction(function () use ($o, &$cantidad) {
                        $orden = Orden::whereKey($o->id_orden)->lockForUpdate()->first();
                        if (!$orden || $orden->estado !== 'pendiente_pago') return;
                        $limite = $orden->limite_saldo_en ?? $orden->expira_en;
                        if (!$limite || $limite->isFuture()) return;
                        $pago = $orden->pago;
                        $dinero = $pago && in_array($pago->proveedor, ['efectivo', 'transferencia'], true)
                            ? $pago->montoAcreditadoCentavos() : 0;
                        // Sin fondos acreditados, una transferencia ya enviada sigue
                        // pendiente de conciliación: no bloquear esa revisión previa.
                        if ($dinero === 0 && $pago?->proveedor === 'transferencia'
                            && $pago->abonos()->where('estado', 'pendiente')->exists()) return;
                        $orden->update([
                            'estado' => 'vencida',
                            // NUNCA sustituir expira_en por now(): el original rige los 30 días.
                            'cierre_financiero_estado' => $dinero > 0 ? 'regularizacion' : 'no_aplica',
                        ]);
                        if ($orden->tipo_orden === 'inscripcion' && $orden->inscripcion?->estado === 'pendiente_pago') {
                            $orden->inscripcion->update(['estado' => 'vencida']);
                        }
                        $cantidad++;
                    }, 3);
                }
            });
        // Migración lógica, no destructiva, de vencidas con abonos previos.
        Orden::where('estado', 'vencida')
            ->whereIn('cierre_financiero_estado', ['no_aplica', 'pendiente_revision'])
            ->whereHas('pago', fn ($q) => $q->whereIn('proveedor', ['efectivo', 'transferencia'])
                ->whereHas('abonos', fn ($a) => $a->where('estado', 'confirmado')->where('monto_recibido', '>', 0)))
            ->orderBy('id_orden')->chunkById(100, function ($ordenes) {
                foreach ($ordenes as $o) {
                    DB::transaction(function () use ($o) {
                        $actual = Orden::whereKey($o->id_orden)->lockForUpdate()->first();
                        if ($actual && in_array($actual->cierre_financiero_estado, ['no_aplica','pendiente_revision'], true)
                            && $actual->pago?->montoAcreditadoCentavos() > 0) {
                            $actual->update(['cierre_financiero_estado' => 'regularizacion']);
                        }
                    }, 3);
                }
            });
        // La fecha de cierre es el VENCIMIENTO ORIGINAL + 30 días, no la fecha del comando.
        Orden::where('estado', 'vencida')
            ->where('cierre_financiero_estado', 'regularizacion')
            ->where(function ($q) {
                $limite = now('UTC')->subDays(RegularizacionVencidasService::DIAS);
                $q->where('limite_saldo_en', '<=', $limite)
                    ->orWhere(fn ($legacy) => $legacy->whereNull('limite_saldo_en')
                        ->where('expira_en', '<=', $limite));
            })->orderBy('id_orden')->chunkById(100, function ($ordenes) use (&$cantidad) {
                foreach ($ordenes as $o) {
                    DB::transaction(function () use ($o, &$cantidad) {
                        $actual = Orden::whereKey($o->id_orden)->lockForUpdate()->first();
                        if ($actual && $actual->estado === 'vencida'
                            && $actual->cierre_financiero_estado === 'regularizacion'
                            && !app(RegularizacionVencidasService::class)->vigente($actual)) {
                            app(RegularizacionVencidasService::class)->finalizar($actual, null,
                                'Finalizó el plazo de 30 días después del vencimiento de la orden.');
                            $cantidad++;
                        }
                    }, 3);
                }
            });
        return $cantidad;
    }
}
