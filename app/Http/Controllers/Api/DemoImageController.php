<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PexelsApi;
use Illuminate\Http\Request;

class DemoImageController extends Controller
{
    /**
     * GET /api/demo/images?query=motorcycle+parts&limit=12
     * Retorna JSON normalizado para el front (sin exponer API key).
     */
    public function index(Request $request, PexelsApi $pexels)
    {
        // Si no hay API key, devolver vacío (no exponer error sensible)
        if (!config('services.pexels.key')) {
            return response()->json([
                'error' => false,
                'status' => 200,
                'data' => [],
                'message' => 'PEXELS_API_KEY no configurada: retornando lista vacía.',
            ]);
        }

        $query = (string) $request->query('query', 'motorcycle parts');
        $limit = (int) $request->query('limit', 12);

        $result = $pexels->search($query, $limit);

        return response()->json($result, $result['status'] ?? 200);
    }
}
