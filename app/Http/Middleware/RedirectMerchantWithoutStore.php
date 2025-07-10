<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class RedirectMerchantWithoutStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Detectar si es comerciante (compatibilidad con Spatie o columna 'role')
        $isMerchant = false;
        if (method_exists($user, 'hasRole')) {
            // Spatie
            $isMerchant = $user->hasRole('comerciante') || $user->hasRole('merchant');
        } else {
            // Columna simple en users.role
            $roleValue = strtolower((string)($user->role ?? ''));
            $isMerchant = in_array($roleValue, ['comerciante', 'merchant']);
        }

        if ($isMerchant) {
            // Verificar si ya tiene tienda
            $hasStore = false;

            // 1) por columna directa
            if (isset($user->store_id) && !empty($user->store_id)) {
                $hasStore = true;
            }

            // 2) por relación user_id en stores
            if (!$hasStore) {
                $hasStore = Store::where('user_id', $user->id)->exists();
            }

            if (!$hasStore && !$request->routeIs('store.create') && !$request->routeIs('store.create.post')) {
                // Obligar a ir a crear tienda
                return redirect()->route('store.create')
                    ->with('info', 'Primero crea tu tienda para continuar.');
            }
        }

        return $next($request);
    }
}
