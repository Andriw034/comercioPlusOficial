<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $allowed = collect($roles)->map(fn ($r) => strtolower($r))->toArray();
        if (in_array('merchant', $allowed, true) && $user->isMerchant()) {
            return $next($request);
        }
        if (in_array('client', $allowed, true) && $user->isClient()) {
            return $next($request);
        }

        return response()->json(['message' => 'No autorizado'], 403);
    }
}
