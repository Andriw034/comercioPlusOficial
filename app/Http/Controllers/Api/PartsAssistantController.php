<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClaudeAssistantService;
use App\Services\PartsAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

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

    /**
     * Pregunta conversacional a Claude (IA real) usando el catalogo de la tienda.
     * Publico: lo usan los clientes desde la pagina de la tienda.
     */
    public function ask(Request $request, ClaudeAssistantService $claude): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        try {
            $result = $claude->ask($data['question'], $data['store_id']);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
