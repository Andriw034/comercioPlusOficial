<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DianConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        return response()->json([
            'dian_enabled'       => $store->dian_enabled,
            'dian_nit'           => $store->dian_nit,
            'dian_business_name' => $store->dian_business_name,
            'dian_provider'      => $store->dian_provider,
            'dian_enabled_at'    => $store->dian_enabled_at,
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nit'           => 'required|string|max:20',
            'business_name' => 'required|string|max:255',
            'provider'      => 'required|in:saphety,carvajal,factory,alegra,other',
            'api_key'       => 'nullable|string',
            'api_url'       => 'nullable|url',
        ]);

        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $credentials = [];
        if (isset($validated['api_key'])) {
            $credentials['api_key'] = $validated['api_key'];
        }
        if (isset($validated['api_url'])) {
            $credentials['api_url'] = $validated['api_url'];
        }

        $store->enableDian(
            $validated['nit'],
            $validated['business_name'],
            $validated['provider'],
            $credentials
        );

        return response()->json([
            'message' => 'Facturación DIAN habilitada correctamente',
            'data'    => $store->fresh(),
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $store->disableDian();

        return response()->json([
            'message' => 'Facturación DIAN deshabilitada',
            'data'    => $store->fresh(),
        ]);
    }
}
