<?php

namespace Tests\Feature\Ai;

use App\Services\Ai\AiTextGenerator;
use App\Services\Ai\GeminiGenerator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Formato de la API de Google. Es donde estan las trampas: Gemini llama "model" al
 * turno del asistente, envuelve el texto en `parts` y lleva el modelo en la URL.
 */
class GeminiGeneratorTest extends TestCase
{
    private GeminiGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.key'      => 'test-key',
            'services.gemini.model'    => 'gemini-3.6-flash',
            'services.gemini.thinking' => 'minimal',
        ]);

        $this->generator = new GeminiGenerator();
    }

    private function fakeGemini(string $text = 'Listo.'): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => $text]]], 'finishReason' => 'STOP'],
                ],
            ], 200),
        ]);
    }

    public function test_arma_el_cuerpo_que_espera_google(): void
    {
        $this->fakeGemini('Si, tenemos.');

        $respuesta = $this->generator->generate(
            'Eres el vendedor.',
            [['role' => 'user', 'content' => 'hola'], ['role' => 'assistant', 'content' => 'que tal']],
            'que pastillas tienes',
        );

        $this->assertSame('Si, tenemos.', $respuesta);

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            // La persona va en system_instruction, no mezclada con la pregunta.
            $this->assertSame('Eres el vendedor.', $body['system_instruction']['parts'][0]['text']);

            // Google llama "model" a lo que Anthropic llama "assistant".
            $this->assertCount(3, $body['contents']);
            $this->assertSame('user', $body['contents'][0]['role']);
            $this->assertSame('model', $body['contents'][1]['role']);
            $this->assertSame('que tal', $body['contents'][1]['parts'][0]['text']);

            // La pregunta nueva va al final, siempre como user.
            $this->assertSame('user', $body['contents'][2]['role']);
            $this->assertSame('que pastillas tienes', $body['contents'][2]['parts'][0]['text']);

            $this->assertSame('minimal', $body['generationConfig']['thinking_level']);

            // La clave viaja en cabecera, no en la URL: asi no queda en los logs.
            $this->assertStringContainsString('models/gemini-3.6-flash:generateContent', $request->url());
            $this->assertStringNotContainsString('test-key', $request->url());

            return $request->hasHeader('x-goog-api-key', 'test-key');
        });
    }

    public function test_el_modelo_admite_el_prefijo_models_sin_romper_la_url(): void
    {
        // La URL ya trae "models/": si la variable de entorno lo repite, Google
        // responde 404 y el motivo no se entiende desde afuera.
        config(['services.gemini.model' => 'models/gemini-3.6-flash']);
        $this->fakeGemini();

        $this->generator->generate('sistema', [], 'hola');

        Http::assertSent(function (Request $request): bool {
            $this->assertStringNotContainsString('models/models/', $request->url());

            return true;
        });
    }

    public function test_sin_nivel_de_razonamiento_no_manda_el_parametro(): void
    {
        // Es un ajuste de costo, no un requisito: si el modelo no lo acepta se vacia
        // la variable y la llamada tiene que seguir siendo valida.
        config(['services.gemini.thinking' => '']);
        $this->fakeGemini();

        $this->generator->generate('sistema', [], 'hola');

        Http::assertSent(function (Request $request): bool {
            $this->assertArrayNotHasKey('thinking_level', $request->data()['generationConfig']);

            return true;
        });
    }

    public function test_sin_clave_no_llama_a_la_api(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Falta configurar GEMINI_API_KEY en el .env');

        try {
            $this->generator->generate('sistema', [], 'hola');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_un_error_de_la_api_no_le_filtra_detalles_al_cliente(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'API key not valid'],
            ], 400),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El asistente no esta disponible en este momento.');

        $this->generator->generate('sistema', [], 'hola');
    }

    public function test_una_respuesta_sin_texto_pide_reformular(): void
    {
        // Gemini puede responder 200 sin texto si agoto el presupuesto razonando.
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['finishReason' => 'MAX_TOKENS']],
            ], 200),
        ]);

        $this->assertSame(
            AiTextGenerator::SIN_RESPUESTA,
            $this->generator->generate('sistema', [], 'hola'),
        );
    }
}
