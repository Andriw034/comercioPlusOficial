<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;

class EnsureStoreExists
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay user, dejar que redireccione el auth middleware
        if (!$user) return $next($request);

        // Solo obliga a comerciantes/admin; clientes no pasan por admin/*
        $isMerchant = $user->hasRole('admin_comerciante') || $user->hasRole('merchant') || $user->role === 'merchant';
        $isAdmin    = $user->hasRole('admin') || $user->role === 'admin';

        if ($isMerchant || $isAdmin) {
            $hasStore = \App\Models\Store::where('user_id', $user->id)->exists();
            if (!$hasStore) {
                return redirect()->route(Route::has('store.create') ? 'store.create' : 'store.wizard')
                    ->with('warning', 'Primero crea tu tienda para acceder al panel.');
            }
        }

        return $next($request);
    }
}
