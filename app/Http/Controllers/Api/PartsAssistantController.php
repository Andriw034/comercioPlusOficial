<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PartsAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartsAssistantController extends Controller
{
    public function __construct(private readonly PartsAssistantService $assistant)
    {
    }

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q'        => ['required', 'string', 'min:2', 'max:200'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        $result = $this->assistant->search($data['q'], $data['store_id'] ?? null);

        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => $result['sin_resultados_por'] === null
                ? 'Compatibilidades encontradas'
                : 'Sin compatibilidades verificadas para esa consulta',
        ]);
    }
}
