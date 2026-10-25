<?php

namespace App\Providers;

use App\Support\Scope\CurrentScope;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola instancia por peticion: el ambito se resuelve una vez y lo
        // comparten los filtros de todos los modelos.
        $this->app->singleton(CurrentScope::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
