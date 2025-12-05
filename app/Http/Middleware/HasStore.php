<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasStore
{
    /**
     * Requiere que un comerciante tenga tienda (Store/PublicStore).
     * Si no la tiene, lo envía a crearla.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si es comerciante y no tiene tienda (ni pública), lo mandamos a crear tienda
        if ($user->esComerciante() && !$user->hasStore() && !$user->hasPublicStore()) {
            return redirect()
                ->route('store.create')
                ->with('info', 'Primero crea tu tienda para continuar.');
        }

        return $next($request);
    }
}
