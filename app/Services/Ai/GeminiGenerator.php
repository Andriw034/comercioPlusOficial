<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Proveedor Google Gemini.
 *
 * Es el que tiene plan gratuito sin tarjeta de credito, asi que es el que permite
 * probar el asistente sin gastar. Contrapartida: en el plan gratis Google usa el
 * contenido para mejorar sus productos. Para preguntas de repuestos no es problema;
 * si algun dia se manejan datos sensibles del cliente hay que pasar a plan pago o
 * volver a Anthropic (AI_PROVIDER=anthropic).
 *
 * El formato de Google se parece al de Anthropic pero con otros nombres, y las
 * diferencias no perdonan:
 *   - el turno del asistente se llama "model", no "assistant"
 *   - el texto va envuelto en `parts`, no suelto en `content`
 *   - el modelo viaja en la URL, no en el cuerpo
 */
class GeminiGenerator implements AiTextGenerator
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function generate(string $system, array $history, string $userContent): string
    {
        $key = config('services.gemini.key');

        if (empty($key)) {
            throw new RuntimeException('Falta configurar GEMINI_API_KEY en el .env');
        }

        $model = $this->model();

        $response = Http::withHeaders([
            'x-goog-api-key' => $key,
            'content-type'   => 'application/json',
        ])->timeout(60)->retry(1, 500, throw: false)
            ->post(sprintf(self::ENDPOINT, $model), $this->body($system, $history, $userContent));

        if (! $response->successful()) {
            // La clave nunca se registra: va en cabecera, no en la URL, justamente
            // para que no termine en los logs de nadie.
            Log::warning('Gemini API error', [
                'status' => $response->status(),
                'model'  => $model,
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('El asistente no esta disponible en este momento.');
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (is_string($text) && $text !== '') {
            return $text;
        }

        // Gemini puede responder 200 y aun asi no traer texto: si agoto el presupuesto
        // razonando (finishReason MAX_TOKENS) o si un filtro de seguridad corto la
        // respuesta. Sin esto en el log, desde afuera se ve igual que un fallo mudo.
        Log::warning('Gemini respondio sin texto', [
            'model'         => $model,
            'finish_reason' => $response->json('candidates.0.finishReason'),
            'block_reason'  => $response->json('promptFeedback.blockReason'),
        ]);

        return self::SIN_RESPUESTA;
    }

    /**
     * La URL ya trae el segmento `models/`. Si la variable de entorno lo repite
     * ("models/gemini-3.6-flash") Google responde 404 y el motivo no es evidente.
     */
    private function model(): string
    {
        $model = trim((string) config('services.gemini.model'));

        return ltrim(preg_replace('#^models/#', '', $model) ?? $model, '/');
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    private function body(string $system, array $history, string $userContent): array
    {
        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $turn['content']]],
            ];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $userContent]]];

        $generationConfig = [
            'maxOutputTokens' => (int) config('services.gemini.max_tokens', 1200),
        ];

        // Gemini 3.x razona por defecto y ese razonamiento consume el presupuesto de
        // salida. Para "tienes pastillas para una AKT?" no aporta y encarece: bajarlo
        // deja mas tokens para la respuesta de verdad. Es opcional a proposito, por si
        // el modelo configurado no acepta el parametro.
        $thinking = trim((string) config('services.gemini.thinking', ''));

        if ($thinking !== '') {
            $generationConfig['thinking_level'] = $thinking;
        }

        return [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents'           => $contents,
            'generationConfig'   => $generationConfig,
        ];
    }
}
