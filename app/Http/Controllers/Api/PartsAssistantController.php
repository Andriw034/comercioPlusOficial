<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PartsAssistantService;
use App\Services\StoreAssistantService;
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
     * Pregunta conversacional a la IA usando el catalogo de la tienda.
     * Publico: lo usan los clientes desde la pagina de la tienda.
     *
     * `history` son los turnos previos del chat: sin ellos el asistente no entiende
     * un "y para la 150?" despues de una pregunta sobre otra moto.
     */
    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question'          => ['required', 'string', 'min:2', 'max:500'],
            'store_id'          => ['required', 'integer', 'exists:stores,id'],
            'history'           => ['sometimes', 'array', 'max:20'],
            'history.*.role'    => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        try {
            // El servicio se resuelve aca adentro y no por inyeccion en la firma
            // porque al armarlo se elige el proveedor de IA: si AI_PROVIDER esta mal
            // configurado, el fallo tiene que salir como 503 con el motivo y no como
            // un error 500 sin explicacion antes de entrar al try.
            $result = app(StoreAssistantService::class)
                ->ask($data['question'], $data['store_id'], $data['history'] ?? []);
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
