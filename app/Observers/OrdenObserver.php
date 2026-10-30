<?php
namespace App\Observers;
use App\Models\Orden;
use App\Services\AvisosOrdenService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrdenObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Orden $orden): void
    {
        app(AvisosOrdenService::class)->programar($orden, 'creada');
    }
    public function updated(Orden $orden): void
    {
        $avisos = app(AvisosOrdenService::class);
        if ($orden->wasChanged('estado')) {
            match ($orden->estado) {
                'pagada' => $avisos->programar($orden, 'pagada'),
                'vencida' => $avisos->programar($orden, 'vencida'),
                default => null,
            };
        }
        if ($orden->wasChanged('cierre_financiero_estado') && $orden->cierre_financiero_estado === 'finalizada') {
            $avisos->programar($orden, 'finalizada');
        }
    }
}
