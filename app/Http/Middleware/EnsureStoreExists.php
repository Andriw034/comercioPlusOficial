<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureStoreExists
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $hasStore = Store::where('user_id', Auth::id())->exists();
            // Permitir acceder a la creación si no tiene tienda
            if (!$hasStore && !$request->routeIs('store.create') && !$request->routeIs('store.store')) {
                return redirect()->route('store.create');
            }
        }
        return $next($request);
    }
}
