<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ElectronicDocument;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class ElectronicInvoicingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $merchant;
    private Store $store;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = User::factory()->create(['role' => 'merchant']);
        $this->store = Store::factory()->create([
            'user_id'      => $this->merchant->id,
            'dian_enabled' => true,
        ]);
        $this->token = $this->merchant->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
    }

    private function invoicePayload(array $overrides = []): array
    {
        return array_merge([
            'issuer_nit'                   => '901234567-8',
            'issuer_name'                  => $this->store->name,
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1098765432',
            'customer_name'                => 'Carlos Pérez',
            'customer_email'               => 'carlos@test.com',
            'payment_method'               => 'contado',
            'payment_means'                => 'efectivo',
            'items' => [
                [
                    'description' => 'Pastillas de freno Brembo',
                    'quantity'    => 2,
                    'unit_price'  => 45000,
                    'tax_rate'    => 19,
                ],
                [
                    'description' => 'Aceite motor 10W-40',
                    'quantity'    => 1,
                    'unit_price'  => 32000,
                    'discount'    => 2000,
                    'tax_rate'    => 19,
                ],
            ],
        ], $overrides);
    }

    // ─── Crear factura ───

    /** @test */
    public function puede_crear_factura_electronica()
    {
        $response = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.dian_status', 'draft')
            ->assertJsonPath('data.document_type', 'invoice')
            ->assertJsonPath('data.customer_name', 'Carlos Pérez');

        $this->assertDatabaseCount('electronic_documents', 1);
        $this->assertDatabaseCount('electronic_document_items', 2);
    }

    /** @test */
    public function factura_calcula_totales_correctamente()
    {
        $response = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());

        $data = $response->json('data');

        // Item 1: 2 * 45000 = 90000, IVA 19% = 17100
        // Item 2: 1 * 32000 - 2000 = 30000, IVA 19% = 5700
        // Subtotal = 120000, Tax = 22800, Total = 142800
        $this->assertEquals(120000, (float) $data['subtotal']);
        $this->assertEquals(22800, (float) $data['tax_total']);
        $this->assertEquals(142800, (float) $data['total']);
        $this->assertEquals(2000, (float) $data['discount_total']);
    }

    /** @test */
    public function crear_factura_requiere_items()
    {
        $payload = $this->invoicePayload();
        unset($payload['items']);

        $response = $this->postJson('/api/merchant/invoicing', $payload, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    /** @test */
    public function crear_factura_requiere_autenticacion()
    {
        $response = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function crear_factura_valida_tipo_identificacion()
    {
        $payload = $this->invoicePayload(['customer_identification_type' => 'INVALIDO']);

        $response = $this->postJson('/api/merchant/invoicing', $payload, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors('customer_identification_type');
    }

    // ─── Listar ───

    /** @test */
    public function puede_listar_documentos()
    {
        // Crear 3 facturas
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        }

        $response = $this->getJson('/api/merchant/invoicing', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    /** @test */
    public function puede_filtrar_por_estado()
    {
        $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());

        $response = $this->getJson('/api/merchant/invoicing?status=draft', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);

        $response = $this->getJson('/api/merchant/invoicing?status=approved', $this->authHeaders());
        $response->assertJsonPath('meta.total', 0);
    }

    // ─── Detalle ───

    /** @test */
    public function puede_ver_detalle_documento()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        $response = $this->getJson("/api/merchant/invoicing/{$docId}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $docId)
            ->assertJsonStructure(['data' => ['items', 'taxes', 'logs']]);
    }

    /** @test */
    public function no_puede_ver_documento_de_otra_tienda()
    {
        $otherUser = User::factory()->create(['role' => 'merchant']);
        $otherStore = Store::factory()->create(['user_id' => $otherUser->id]);

        $doc = ElectronicDocument::create([
            'store_id'                     => $otherStore->id,
            'document_type'                => 'invoice',
            'prefix'                       => 'FE',
            'number'                       => 1,
            'dian_status'                  => 'draft',
            'issuer_nit'                   => '999',
            'issuer_name'                  => 'Otra tienda',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '111',
            'customer_name'                => 'Otro',
            'subtotal'                     => 0,
            'tax_total'                    => 0,
            'discount_total'               => 0,
            'total'                        => 0,
        ]);

        $response = $this->getJson("/api/merchant/invoicing/{$doc->id}", $this->authHeaders());

        $response->assertStatus(403);
    }

    // ─── Actualizar ───

    /** @test */
    public function puede_actualizar_documento_draft()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        $response = $this->putJson("/api/merchant/invoicing/{$docId}", [
            'customer_name' => 'Nombre Actualizado',
            'notes'         => 'Nota de prueba',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.customer_name', 'Nombre Actualizado')
            ->assertJsonPath('data.notes', 'Nota de prueba');
    }

    // ─── Enviar a DIAN ───

    /** @test */
    public function puede_enviar_documento_a_dian()
    {
        Http::fake([
            '*/api/ubl2.1/*' => Http::response([
                'track_id' => 'TRACK-TEST-001',
                'cufe'     => str_repeat('a', 96),
                'message'  => 'Documento recibido',
            ], 200),
        ]);

        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        $response = $this->postJson("/api/merchant/invoicing/{$docId}/send", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.dian_status', 'pending')
            ->assertJsonStructure(['track_id', 'cufe']);

        // Verify CUFE was generated (96 hex chars)
        $doc = ElectronicDocument::find($docId);
        $this->assertEquals(96, strlen($doc->cufe));
        $this->assertEquals('TRACK-TEST-001', $doc->dian_track_id);
    }

    /** @test */
    public function no_puede_enviar_documento_aprobado()
    {
        Http::fake([
            '*/api/ubl2.1/*' => Http::response([
                'track_id' => 'TRACK-TEST-002',
                'message'  => 'OK',
            ], 200),
        ]);

        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        // draft → pending via API
        $this->postJson("/api/merchant/invoicing/{$docId}/send", [], $this->authHeaders());

        // pending → approved (simular)
        ElectronicDocument::where('id', $docId)->update([
            'dian_status'      => 'approved',
            'dian_approved_at' => now(),
        ]);

        // approved → send (inválido)
        $response = $this->postJson("/api/merchant/invoicing/{$docId}/send", [], $this->authHeaders());

        $response->assertStatus(422);
    }

    // ─── Anular ───

    /** @test */
    public function puede_anular_documento_aprobado_reciente()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        // Simular aprobación
        ElectronicDocument::where('id', $docId)->update([
            'dian_status'    => 'approved',
            'cufe'           => 'CUFE-CANCEL-TEST',
            'dian_approved_at' => now(),
        ]);

        $response = $this->postJson("/api/merchant/invoicing/{$docId}/cancel", [
            'reason' => 'Error en datos del cliente',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.dian_status', 'cancelled');
    }

    /** @test */
    public function no_puede_anular_documento_draft()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        $response = $this->postJson("/api/merchant/invoicing/{$docId}/cancel", [], $this->authHeaders());

        $response->assertStatus(422);
    }

    // ─── Nota crédito ───

    /** @test */
    public function puede_crear_nota_credito()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        // Simular aprobación
        ElectronicDocument::where('id', $docId)->update([
            'dian_status'    => 'approved',
            'cufe'           => 'CUFE-NC-TEST',
            'dian_approved_at' => now(),
        ]);

        $response = $this->postJson("/api/merchant/invoicing/{$docId}/credit-note", [
            'reason' => 'Devolución de producto defectuoso',
        ], $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.document_type', 'credit_note')
            ->assertJsonPath('data.dian_status', 'draft');

        $this->assertDatabaseCount('electronic_documents', 2);
    }

    // ─── Logs ───

    /** @test */
    public function puede_ver_logs_de_documento()
    {
        $createResponse = $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $docId = $createResponse->json('data.id');

        $response = $this->getJson("/api/merchant/invoicing/{$docId}/logs", $this->authHeaders());

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    // ─── Stats ───

    /** @test */
    public function puede_ver_estadisticas()
    {
        $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());
        $this->postJson('/api/merchant/invoicing', $this->invoicePayload(), $this->authHeaders());

        $response = $this->getJson('/api/merchant/invoicing/stats', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.total_documents', 2)
            ->assertJsonPath('data.currency', 'COP');
    }
}
