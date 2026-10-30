<?php
namespace App\Observers;
use App\Models\AbonoTransferencia;
use App\Services\AvisosOrdenService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AbonoTransferenciaObserver implements ShouldHandleEventsAfterCommit
{
    public function created(AbonoTransferencia $abono): void
    {
        $this->avisar($abono);
        if ($abono->estado === 'pendiente' && $abono->metodo === 'transferencia' && filled($abono->comprobante_path)) {
            $orden = $abono->pago?->orden;
            if ($orden) app(AvisosOrdenService::class)->programar($orden, 'comprobante_recibido', (string)$abono->id_abono);
        }
    }
    public function updated(AbonoTransferencia $abono): void
    {
        if ($abono->wasChanged('estado')) $this->avisar($abono);
    }
    private function avisar(AbonoTransferencia $abono): void
    {
        if ($abono->estado !== 'confirmado' || (float) $abono->monto_recibido <= 0) return;
        $orden = $abono->pago?->orden;
        if ($orden) app(AvisosOrdenService::class)->programar($orden, 'abono', (string) $abono->id_abono);
    }
}
