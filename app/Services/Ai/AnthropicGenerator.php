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
    use RetriesTransientFailures;

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
        ])->timeout(45)->retry(self::INTENTOS, self::ESPERA_MS, $this->reintentable(), throw: false)->post(self::ENDPOINT, [
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

            // El tipo de error de Anthropic es un enumerado (invalid_request_error,
            // authentication_error, rate_limit_error...): no filtra nada y evita tener
            // que entrar a leer los logs del servidor para saber que paso.
            $motivo = trim($response->status().' '.(string) $response->json('error.type'));

            throw new RuntimeException(
                "El asistente no esta disponible en este momento (anthropic respondio {$motivo})."
            );
        }

        $text = $response->json('content.0.text');

        return is_string($text) && $text !== '' ? $text : self::SIN_RESPUESTA;
    }
}
