<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresDianEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $store = $user->store;

        if (!$store || !$store->dian_enabled) {
            return response()->json([
                'message'      => 'La facturación electrónica DIAN no está habilitada para tu tienda.',
                'hint'         => 'Puedes activarla en Configuración > Facturación DIAN si tu negocio está formalizado ante la DIAN.',
                'dian_enabled' => false,
            ], 403);
        }

        return $next($request);
    }
}
