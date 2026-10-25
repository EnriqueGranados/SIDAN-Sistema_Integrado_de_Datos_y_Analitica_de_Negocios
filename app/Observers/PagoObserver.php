<?php
namespace App\Observers;
use App\Models\Pago;
use App\Services\AvisosOrdenService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PagoObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Pago $pago): void
    {
        if ($pago->wasChanged('estado') && $pago->estado === 'confirmado' && $pago->proveedor === 'wompi' && $pago->orden) {
            app(AvisosOrdenService::class)->programar($pago->orden, 'abono', 'wompi-'.$pago->id_pago);
        }
    }
}
