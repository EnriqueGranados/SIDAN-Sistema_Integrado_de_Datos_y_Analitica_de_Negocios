<?php

namespace App\Services;

use App\Models\Orden;
use App\Models\Pago;
use App\Models\AbonoTransferencia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Regularización exclusivamente presencial, hasta 30 días después del vencimiento original. */
class RegularizacionVencidasService
{
    public const DIAS = 30;

    public function plazo(Orden $orden): ?\Carbon\CarbonInterface
    {
        $original = $orden->limite_saldo_en ?? $orden->expira_en;
        return $original?->copy()->addDays(self::DIAS);
    }

    public function vigente(Orden $orden): bool
    {
        $limite = $orden->limite_saldo_en ?? $orden->expira_en;
        $final = $this->plazo($orden);
        return $orden->estado === 'vencida'
            && in_array($orden->cierre_financiero_estado, ['regularizacion', 'pendiente_revision'], true)
            && $limite && $limite->lessThanOrEqualTo(now('UTC'))
            && $final && now('UTC')->lessThan($final);
    }

    private function denegar(string $mensaje): never
    {
        throw ValidationException::withMessages(['orden' => $mensaje]);
    }

    /** Debe ejecutarse bajo bloqueo de orden y transacción. */
    public function registrarEfectivo(Orden $orden, int $centavos, int $admin, ?string $observacion): int
    {
        if (!$this->vigente($orden)) {
            $this->denegar('La regularización presencial ya no está disponible para esta orden.');
        }
        $pago = Pago::where('id_orden', $orden->id_orden)->lockForUpdate()->first();
        if (!$pago || !in_array($pago->proveedor, ['efectivo', 'transferencia'], true)
            || !in_array($pago->estado, ['pendiente', 'parcial'], true)) {
            $this->denegar('La orden no tiene un pago manual pendiente de regularización.');
        }
        // Un comprobante anterior sin revisar se concilia o rechaza primero: evita acreditar dos veces.
        if ($pago->abonos()->where('estado', 'pendiente')->where('metodo', 'transferencia')->exists()) {
            $this->denegar('Primero resolvé los comprobantes de transferencia pendientes de esta orden.');
        }
        $gestion = app(GestionOrdenService::class);
        $reparto = app(AbonosProductosService::class);
        $saldo = $gestion->centavos($orden->total) - $pago->montoAcreditadoCentavos();
        if ($centavos <= 0 || $centavos > $saldo) {
            $this->denegar('El efectivo debe ser mayor que cero y no superar el saldo pendiente.');
        }
        // Stock liberado al vencer: si un artículo pasa a estar íntegramente pagado,
        // hay que comprobar que todavía se puede reservar, antes de registrar el dinero.
        $orden->loadMissing('detalles');
        $anteriores = [];
        foreach ($orden->detalles as $d) {
            $anteriores[$d->id_detalle_orden] = $reparto->aplicado($d) >= $reparto->neto($d);
        }
        // Las filas de efectivo pendientes en la caja son placeholders; se usan o se crean sin duplicar abonos.
        $abono = $pago->abonos()->where('estado', 'pendiente')->where('metodo', 'efectivo')
            ->lockForUpdate()->first();
        if (!$abono) $abono = $pago->abonos()->create(['metodo' => 'efectivo', 'estado' => 'pendiente']);
        $abono->update([
            'metodo' => 'efectivo', 'estado' => 'confirmado',
            'monto_recibido' => $gestion->dinero($centavos),
            'observacion_revision' => $observacion ?: 'Cobro presencial de orden vencida',
            'revisado_por' => $admin, 'revisado_en' => now('UTC'),
        ]);
        $reparto->distribuir($orden, $abono);
        $this->verificarStockTransicion($orden, $anteriores);
        $faltante = $gestion->centavos($orden->total) - $pago->montoAcreditadoCentavos();
        $pago->update([
            'estado' => $faltante === 0 ? 'confirmado' : 'parcial',
            'fecha_confirmacion' => $faltante === 0 ? now('UTC') : $pago->fecha_confirmacion,
            'revisado_por' => $admin, 'revisado_en' => now('UTC'),
        ]);
        if ($faltante === 0) $this->completar($orden, $pago, $admin);
        return $faltante;
    }

    /** Validar existencias liberadas por el vencimiento antes de acreditar como íntegramente pagado. */
    public function verificarStockTransicion(Orden $orden, array $anteriores): void
    {
        $reparto = app(AbonosProductosService::class);
        if ($orden->tipo_orden === 'productos') {
            $stock = app(DisponibilidadService::class);
            foreach ($orden->detalles as $d) {
                if (($anteriores[$d->id_detalle_orden] ?? false) || $reparto->aplicado($d) < $reparto->neto($d)) continue;
                $necesarias = max(0, (int)$d->cantidad - (int)$d->cantidad_entregada);
                if ($necesarias === 0) continue;
                // Al estar aplicado el nuevo abono, cantidadComprometida ya incluye esta línea.
                // El stock real debe no quedar comprometido por encima de stock_total.
                foreach ([['tbl_items_actividad', 'id_item_actividad', $d->id_item_actividad],
                          ['tbl_variantes_item', 'id_variante_item', $d->id_variante_item]] as [$tabla, $columna, $id]) {
                    if (!$id) continue;
                    $limite = DB::table($tabla)->where($columna, $id)->value('stock_total');
                    if ($limite === null) continue;
                    $comprometidas = $stock->cantidadComprometida('detalle.' . $columna, (int)$id);
                    if ($comprometidas > (int)$limite) {
                        $this->denegar('No hay stock suficiente para completar el producto ' . $d->nombre_concepto . '.');
                    }
                }
            }
        }
    }

    /** Para un comprobante histórico conciliado ANTES de la nueva regularización. */
    public function completar(Orden $orden, Pago $pago, int $admin): void
    {
        if ($pago->montoAcreditadoCentavos() !== app(GestionOrdenService::class)->centavos($orden->total)) {
            $this->denegar('La orden todavía tiene un saldo pendiente.');
        }
        $pago->update(['estado' => 'confirmado', 'fecha_confirmacion' => now('UTC'),
            'revisado_por' => $admin, 'revisado_en' => now('UTC')]);
        $pago->abonos()->where('estado', 'pendiente')->where('metodo', 'efectivo')
            ->update(['estado' => 'rechazado', 'observacion_revision' => 'Orden completamente pagada',
                'revisado_por' => $admin, 'revisado_en' => now('UTC')]);
        $orden->update(['estado' => 'pagada', 'cierre_financiero_estado' => 'regularizada',
            'finalizada_en' => now('UTC'), 'finalizada_por' => $admin,
            'motivo_finalizacion' => 'Pago total completado en regularización presencial']);
        if ($orden->tipo_orden === 'inscripcion' && $orden->inscripcion) {
            $orden->inscripcion->update(['estado' => 'confirmada', 'fecha_confirmacion' => now('UTC'), 'expira_en' => null]);
        }
        if (!DB::table('tbl_comprobantes')->where('id_pago', $pago->id_pago)->exists()) {
            DB::table('tbl_comprobantes')->insert([
                'id_pago' => $pago->id_pago,
                'numero' => 'SIDAN-REC-' . $pago->id_pago,
                'tipo' => 'recibo_interno', 'monto' => $pago->monto,
                'fecha_emision' => now('UTC'), 'es_productiva' => true,
                'created_at' => now('UTC'), 'updated_at' => now('UTC'),
            ]);
        }
    }

    /** Cierra únicamente nuevas posibilidades de cobro, nunca elimina dinero ni entregas. */
    public function finalizar(Orden $orden, ?int $admin, string $motivo): void
    {
        if ($orden->estado !== 'vencida' ||
            !in_array($orden->cierre_financiero_estado, ['regularizacion', 'pendiente_revision'], true)) {
            $this->denegar('La orden no está en regularización.');
        }
        $orden->update([
            'cierre_financiero_estado' => 'finalizada',
            'finalizada_en' => now('UTC'), 'finalizada_por' => $admin,
            'motivo_finalizacion' => $motivo,
        ]);
        // Dejar intacto el historial, incluidos comprobantes antiguos no conciliados.
    }
}
