<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Proveedor Anthropic (Claude).
 *
 * Requiere saldo de API: la suscripcion Claude Pro es de claude.ai y NO habilita
 * esta API. Si la cuenta esta sin credito, Anthropic responde 400 y el asistente
 * queda caido — el motivo real queda en el log, no se le muestra al cliente.
 */
class AnthropicGenerator implements AiTextGenerator
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    public function generate(string $system, array $history, string $userContent): string
    {
        $key = config('services.anthropic.key');

        if (empty($key)) {
            throw new RuntimeException('Falta configurar ANTHROPIC_API_KEY en el .env');
        }

        $messages   = $history;
        $messages[] = ['role' => 'user', 'content' => $userContent];

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
            'content-type'      => 'application/json',
        ])->timeout(60)->retry(1, 500, throw: false)->post(self::ENDPOINT, [
            'model'      => config('services.anthropic.model'),
            'max_tokens' => config('services.anthropic.max_tokens', 900),
            'system'     => $system,
            'messages'   => $messages,
        ]);

        if (! $response->successful()) {
            // El body trae el motivo real (modelo retirado, credito agotado, etc.).
            // Sin esto en el log el fallo es indistinguible desde afuera.
            Log::warning('Anthropic API error', [
                'status' => $response->status(),
                'model'  => config('services.anthropic.model'),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('El asistente no esta disponible en este momento.');
        }

        $text = $response->json('content.0.text');

        return is_string($text) && $text !== '' ? $text : self::SIN_RESPUESTA;
    }
}
