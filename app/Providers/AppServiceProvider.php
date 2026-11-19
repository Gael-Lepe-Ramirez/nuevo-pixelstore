<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <-- Importante para la paginación
use Illuminate\Support\Facades\Gate; // <-- Importante para la seguridad
use Illuminate\Support\Facades\URL;

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
        // 1. Esto arregla las flechas gigantes para usar Bootstrap
        Paginator::useBootstrap();

        // En producción (Railway) todos los enlaces se generan con https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // 2. Esto define quién es el administrador para los botones de editar/eliminar
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });
    }
}