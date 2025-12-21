<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class RedirectAfterLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request); // No debería pasar, pero por si acaso
        }

        // Evitar redirección infinita en rutas de dashboard/admin
        if ($request->routeIs('dashboard') || $request->routeIs('admin.*')) {
            return $next($request);
        }

        // Detectar rol
        $isAdmin = false;
        $isMerchant = false;

        if (method_exists($user, 'hasRole')) {
            // Spatie
            $isAdmin = $user->hasRole('admin') || $user->hasRole('administrator');
            $isMerchant = $user->hasRole('comerciante') || $user->hasRole('merchant');
        } else {
            // Columna simple
            $roleValue = strtolower((string)($user->role ?? ''));
            $isAdmin = in_array($roleValue, ['admin', 'administrator']);
            $isMerchant = in_array($roleValue, ['comerciante', 'merchant']);
        }

        // Prioridad: Admin > Comerciante con tienda > Comerciante sin tienda
        if ($isAdmin) {
            return redirect()->route('admin.dashboard');
        }

        if ($isMerchant) {
            // Verificar tienda
            $hasStore = false;

            if (isset($user->store_id) && !empty($user->store_id)) {
                $hasStore = true;
            }

            if (!$hasStore) {
                $hasStore = Store::where('user_id', $user->id)->exists();
            }

            if ($hasStore) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('store.create')
                    ->with('info', 'Bienvenido! Primero crea tu tienda para comenzar.');
            }
        }

        // Usuario sin rol específico → dashboard genérico
        return redirect()->route('dashboard');
    }
}
