<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasStore
{
    /**
     * Si el usuario es comerciante y no tiene tienda, redirige a crear tienda.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si no estÃ¡ logueado, que siga el flujo normal de auth.
        if (!$user) {
            return $next($request);
        }

        // Evitar bucle: permitir acceder/guardar la creaciÃ³n de tienda.
        if ($request->routeIs('store.create') || $request->routeIs('store.store')) {
            return $next($request);
        }

        // Detectar si es comerciante (soporta Spatie y/o columna 'role')
        $isMerchant = false;
        if (method_exists($user, 'hasRole')) {
            $isMerchant = $user->hasRole('comerciante') || $user->hasRole('merchant');
        } else {
            $roleValue = strtolower((string) ($user->role ?? ''));
            $isMerchant = in_array($roleValue, ['comerciante', 'merchant']);
        }

        if ($isMerchant) {
            // 1) si el user ya tiene store_id asignado
            $hasStore = isset($user->store_id) && !empty($user->store_id);

            // 2) verificar en tabla stores por user_id
            if (!$hasStore) {
                $hasStore = Store::where('user_id', $user->id)->exists();
            }

            if (!$hasStore) {
                return redirect()
                    ->route('store.create')
                    ->with('info', 'Primero crea tu tienda para continuar.');
            }
        }

        return $next($request);
    }
}
