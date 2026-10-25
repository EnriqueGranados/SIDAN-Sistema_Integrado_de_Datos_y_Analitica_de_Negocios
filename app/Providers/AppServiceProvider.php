<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Orden::observe(\App\Observers\OrdenObserver::class);
        \App\Models\Pago::observe(\App\Observers\PagoObserver::class);
        \App\Models\AbonoTransferencia::observe(\App\Observers\AbonoTransferenciaObserver::class);
    }
}
