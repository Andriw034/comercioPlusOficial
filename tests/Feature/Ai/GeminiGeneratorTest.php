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
            'services.gemini.key'             => 'test-key',
            'services.gemini.model'           => 'gemini-3.5-flash',
            'services.gemini.thinking_budget' => '0',
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

            // El campo correcto es thinkingConfig.thinkingBudget: `thinking_level`
            // hacia fallar TODAS las peticiones con 400 Unknown name.
            $this->assertSame(0, $body['generationConfig']['thinkingConfig']['thinkingBudget']);
            $this->assertArrayNotHasKey('thinking_level', $body['generationConfig']);

            // La clave viaja en cabecera, no en la URL: asi no queda en los logs.
            $this->assertStringContainsString('models/gemini-3.5-flash:generateContent', $request->url());
            $this->assertStringNotContainsString('test-key', $request->url());

            return $request->hasHeader('x-goog-api-key', 'test-key');
        });
    }

    public function test_el_modelo_admite_el_prefijo_models_sin_romper_la_url(): void
    {
        // La URL ya trae "models/": si la variable de entorno lo repite, Google
        // responde 404 y el motivo no se entiende desde afuera.
        config(['services.gemini.model' => 'models/gemini-3.5-flash']);
        $this->fakeGemini();

        $this->generator->generate('sistema', [], 'hola');

        Http::assertSent(function (Request $request): bool {
            $this->assertStringNotContainsString('models/models/', $request->url());

            return true;
        });
    }

    public function test_sin_presupuesto_de_razonamiento_no_manda_el_parametro(): void
    {
        // Es un ajuste de costo, no un requisito: si un modelo futuro no acepta
        // presupuesto cero se vacia la variable y la llamada sigue siendo valida.
        config(['services.gemini.thinking_budget' => '']);
        $this->fakeGemini();

        $this->generator->generate('sistema', [], 'hola');

        Http::assertSent(function (Request $request): bool {
            $this->assertArrayNotHasKey('thinkingConfig', $request->data()['generationConfig']);

            return true;
        });
    }

    public function test_reintenta_cuando_google_esta_saturado(): void
    {
        // El plan gratuito devuelve 503 UNAVAILABLE ("high demand") de a ratos. Sin
        // reintento el cliente de la tienda ve un chat roto por algo pasajero.
        Http::fakeSequence()
            ->push(['error' => ['status' => 'UNAVAILABLE']], 503)
            ->push(['candidates' => [['content' => ['parts' => [['text' => 'Si, tenemos.']]]]]], 200);

        $this->assertSame('Si, tenemos.', $this->generator->generate('sistema', [], 'hola'));

        Http::assertSentCount(2);
    }

    public function test_no_reintenta_un_error_que_no_se_arregla_solo(): void
    {
        // Una peticion mal armada (400) o una clave invalida no mejoran reintentando:
        // gastar tres intentos solo hace esperar mas al cliente.
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['status' => 'INVALID_ARGUMENT'],
            ], 400),
        ]);

        try {
            $this->generator->generate('sistema', [], 'hola');
            $this->fail('Se esperaba una excepcion.');
        } catch (RuntimeException) {
            Http::assertSentCount(1);
        }
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

    public function test_un_error_de_la_api_dice_el_motivo_sin_filtrar_detalles(): void
    {
        // El codigo y el `status` son enumerados y ayudan a diagnosticar sin entrar a
        // los logs; el mensaje largo de Google NO puede llegarle al cliente.
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Unknown name "thinking_level" at generation_config',
                    'status'  => 'INVALID_ARGUMENT',
                ],
            ], 400),
        ]);

        try {
            $this->generator->generate('sistema', [], 'hola');
            $this->fail('Se esperaba una excepcion.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'El asistente no esta disponible en este momento (google respondio 400 INVALID_ARGUMENT).',
                $e->getMessage(),
            );
            $this->assertStringNotContainsString('thinking_level', $e->getMessage());
        }
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
