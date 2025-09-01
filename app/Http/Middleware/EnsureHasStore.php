<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHasStore
{
    /**
     * Si el usuario autenticado no tiene tienda, lo redirige a crear tienda.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay usuario (por si acaso), sigue el flujo normal de auth
        if (!$user) {
            return $next($request);
        }

        // Permitir acceso siempre a las rutas de crear/guardar tienda para evitar bucles
        $routeName = optional($request->route())->getName();
        $allowed = [
            'store.create',
            'store.store',
            // agrega aquí otras rutas que deban ser accesibles sin tener tienda
        ];
        if (in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        // Si no tiene tienda, redirige a crear tienda
        if (!$user->store) {
            return redirect()->route('store.create')
                ->with('status', 'Crea tu tienda para empezar');
        }

        return $next($request);
    }
}
