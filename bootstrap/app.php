<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Middleware para el grupo 'web', se añade al final de la pila.
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Registro de alias para middleware.
        // Esto permite usar 'has.store' en los archivos de rutas.
        $middleware->alias([
            'has.store' => \App\Http\Middleware\HasStore::class,
            // Aquí puedes añadir otros alias que necesites en el futuro.
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configuración para el manejo de excepciones.
        // Puedes personalizar cómo se reportan o renderizan las excepciones aquí.
    })->create();
