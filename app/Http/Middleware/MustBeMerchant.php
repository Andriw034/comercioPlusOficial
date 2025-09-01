<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MustBeMerchant
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isMerchant()) {
            abort(403, 'Acceso denegado. Solo los comerciantes pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
