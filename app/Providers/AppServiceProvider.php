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
        // Ignora migraciones del paquete Spatie
        // if (class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
        //     \Spatie\Permission\PermissionServiceProvider::ignoreMigrations();
        // }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
