<?php
namespace App\Services;

use App\Jobs\EnviarAvisoOrden;
use App\Models\Orden;

class AvisosOrdenService
{
    public function programar(Orden $orden, string $evento, string $referencia = ''): void
    {
        if (!config('sidan-avisos.habilitados')) return;
        if (!filter_var($orden->correo_contacto, FILTER_VALIDATE_EMAIL)) return;
        // Nunca enviar dentro de una transacción; usar la cola después de commit.
        EnviarAvisoOrden::dispatch((int) $orden->id_orden, $evento, $referencia)->afterCommit();
    }
}
