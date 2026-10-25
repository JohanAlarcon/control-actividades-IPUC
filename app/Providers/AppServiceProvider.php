<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Support\Scope\CurrentScope;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::define('is-pastor', fn (User $quien) => $quien->is_pastor);
    }
}
