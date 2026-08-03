<?php

namespace Tests\Feature\Ai;

use App\Services\Ai\AiTextGenerator;
use App\Services\Ai\AnthropicGenerator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Formato de la API de Anthropic. No toca base de datos ni rutas: solo verifica
 * que el cuerpo que sale por HTTP sea el que Anthropic espera.
 */
class AnthropicGeneratorTest extends TestCase
{
    private AnthropicGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.anthropic.key'   => 'test-key',
            'services.anthropic.model' => 'claude-opus-5',
        ]);

        $this->generator = new AnthropicGenerator();
    }

    private function fakeAnthropic(string $text = 'Listo.'): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => $text]],
            ], 200),
        ]);
    }

    public function test_arma_el_cuerpo_que_espera_anthropic(): void
    {
        $this->fakeAnthropic('Si, tenemos.');

        $respuesta = $this->generator->generate(
            'Eres el vendedor.',
            [['role' => 'user', 'content' => 'hola'], ['role' => 'assistant', 'content' => 'que tal']],
            'que pastillas tienes',
        );

        $this->assertSame('Si, tenemos.', $respuesta);

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            // La persona va en su propio campo, no mezclada con la pregunta.
            $this->assertSame('Eres el vendedor.', $body['system']);
            $this->assertSame('claude-opus-5', $body['model']);

            // El historial va tal cual, con la pregunta nueva al final.
            $this->assertCount(3, $body['messages']);
            $this->assertSame('assistant', $body['messages'][1]['role']);
            $this->assertSame('que pastillas tienes', $body['messages'][2]['content']);
            $this->assertSame('user', $body['messages'][2]['role']);

            return $request->hasHeader('x-api-key', 'test-key');
        });
    }

    public function test_sin_clave_no_llama_a_la_api(): void
    {
        config(['services.anthropic.key' => null]);
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Falta configurar ANTHROPIC_API_KEY en el .env');

        try {
            $this->generator->generate('sistema', [], 'hola');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_un_error_de_la_api_no_le_filtra_detalles_al_cliente(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'error' => ['message' => 'credit balance is too low'],
            ], 400),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El asistente no esta disponible en este momento.');

        $this->generator->generate('sistema', [], 'hola');
    }

    public function test_una_respuesta_sin_texto_pide_reformular(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => []], 200)]);

        $this->assertSame(
            AiTextGenerator::SIN_RESPUESTA,
            $this->generator->generate('sistema', [], 'hola'),
        );
    }
}
