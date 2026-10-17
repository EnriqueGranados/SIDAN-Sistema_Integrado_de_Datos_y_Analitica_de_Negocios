<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('sidan:vencer-ordenes', function () {
    $cantidad=app(\App\Services\VencimientosOrdenService::class)->sincronizar();
    $this->info("Órdenes vencidas: {$cantidad}");
})->purpose('Revisar vencimientos sin eliminar dinero recibido');

// Requiere ejecutar php artisan schedule:run cada minuto en el servidor.
\Illuminate\Support\Facades\Schedule::command('sidan:vencer-ordenes')->everyFifteenMinutes()->withoutOverlapping();
