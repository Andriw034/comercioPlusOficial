<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
// (Opcional) Si prefieres importar también tu middleware personalizado:
// use App\Http\Middleware\HasStore;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Alias de middleware por nombre (aquí registramos has.store)
        $middleware->alias([
            'has.store' => \App\Http\Middleware\HasStore::class,
        ]);

        // ✅ Tu configuración previa de middleware web (se mantiene)
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
