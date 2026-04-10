<?php

namespace Tests\Feature;

use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentItem;
use App\Models\ElectronicDocumentTax;
use App\Models\Store;
use App\Models\User;
use App\Services\ElectronicInvoicingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DianIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private ElectronicInvoicingService $service;
    private User $merchant;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(ElectronicInvoicingService::class);
        $this->merchant = User::factory()->create(['role' => 'merchant']);
        $this->store    = Store::factory()->create([
            'user_id'      => $this->merchant->id,
            'dian_enabled' => true,
        ]);
    }

    private function crearFactura(): ElectronicDocument
    {
        return $this->service->createInvoice($this->store, [
            'issuer_nit'                   => '900123456',
            'issuer_name'                  => $this->store->name,
            'issuer_email'                 => 'info@test.co',
            'issuer_phone'                 => '3001234567',
            'issuer_address'               => 'Calle 10 # 5-30',
            'issuer_city'                  => 'Bogotá',
            'issuer_department'            => 'Bogotá D.C.',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1098765432',
            'customer_name'                => 'Carlos Pérez',
            'customer_email'               => 'carlos@test.com',
            'customer_phone'               => '3109876543',
            'customer_address'             => 'Carrera 7 # 20-10',
            'customer_city'                => 'Bogotá',
            'customer_department'          => 'Bogotá D.C.',
            'payment_method'               => 'contado',
            'payment_means'                => 'efectivo',
            'items' => [
                [
                    'description' => 'Pastillas de freno Brembo',
                    'code'        => 'REP-001',
                    'quantity'    => 2,
                    'unit_price'  => 45000,
                    'tax_rate'    => 19,
                ],
                [
                    'description' => 'Aceite motor 10W-40',
                    'code'        => 'REP-002',
                    'quantity'    => 1,
                    'unit_price'  => 32000,
                    'discount'    => 2000,
                    'tax_rate'    => 19,
                ],
            ],
        ], $this->merchant->id);
    }

    // ─── sendToDian: flujo exitoso ───

    public function test_send_to_dian_generates_cufe_xml_and_sends(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response([
                'track_id' => 'TRACK-INT-001',
                'cufe'     => str_repeat('c', 96),
                'message'  => 'Documento recibido correctamente',
            ], 200),
        ]);

        $doc = $this->crearFactura();
        $this->assertEquals(ElectronicDocument::STATUS_DRAFT, $doc->dian_status);

        $result = $this->service->sendToDian($doc, $this->merchant->id);

        // Result assertions
        $this->assertTrue($result['success']);
        $this->assertEquals('TRACK-INT-001', $result['track_id']);
        $this->assertNotNull($result['cufe']);
        $this->assertEquals(96, strlen($result['cufe']));

        // Document updated
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_PENDING, $doc->dian_status);
        $this->assertEquals('TRACK-INT-001', $doc->dian_track_id);
        $this->assertEquals(96, strlen($doc->cufe));
        $this->assertNotNull($doc->xml_content);
        $this->assertStringContainsString('UBL 2.1', $doc->xml_content);
        $this->assertStringContainsString('CUFE-SHA384', $doc->xml_content);

        // Audit log created
        $log = $doc->logs()->where('action', 'sent_to_dian')->first();
        $this->assertNotNull($log);
        $this->assertEquals(ElectronicDocument::STATUS_DRAFT, $log->status_from);
        $this->assertEquals(ElectronicDocument::STATUS_PENDING, $log->status_to);
        $this->assertEquals($this->merchant->id, $log->user_id);

        // Matias API was called with base64 XML
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/ubl2.1/invoice')
                && isset($request->data()['xml'])
                && base64_decode($request->data()['xml']) !== false;
        });
    }

    public function test_send_to_dian_xml_contains_correct_data(): void
    {
        Http::fake([
            '*/api/ubl2.1/*' => Http::response(['track_id' => 'T1', 'message' => 'OK'], 200),
        ]);

        $doc = $this->crearFactura();
        $this->service->sendToDian($doc, $this->merchant->id);

        $doc->refresh();
        $xml = $doc->xml_content;

        // Verify XML has all key sections
        $this->assertStringContainsString('AccountingSupplierParty', $xml);
        $this->assertStringContainsString('AccountingCustomerParty', $xml);
        $this->assertStringContainsString('900123456', $xml);       // issuer NIT
        $this->assertStringContainsString('1098765432', $xml);      // customer CC
        $this->assertStringContainsString('InvoiceLine', $xml);
        $this->assertStringContainsString('LegalMonetaryTotal', $xml);
        $this->assertStringContainsString('TaxTotal', $xml);
        $this->assertStringContainsString('COP', $xml);
    }

    // ─── sendToDian: error API ───

    public function test_send_to_dian_handles_api_error(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response('XML inválido', 422),
        ]);

        $doc = $this->crearFactura();
        $result = $this->service->sendToDian($doc, $this->merchant->id);

        $this->assertFalse($result['success']);

        // Document stays draft
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_DRAFT, $doc->dian_status);
        $this->assertNull($doc->dian_track_id);

        // Error log created
        $log = $doc->logs()->where('action', 'send_failed')->first();
        $this->assertNotNull($log);
    }

    public function test_send_to_dian_handles_connection_error(): void
    {
        Http::fake([
            '*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $doc = $this->crearFactura();
        $result = $this->service->sendToDian($doc, $this->merchant->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Connection refused', $result['message']);
    }

    // ─── sendToDian: validaciones de estado ───

    public function test_cannot_send_approved_document(): void
    {
        $doc = $this->crearFactura();
        ElectronicDocument::where('id', $doc->id)->update([
            'dian_status'      => ElectronicDocument::STATUS_APPROVED,
            'dian_approved_at' => now(),
        ]);
        $doc->refresh();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Solo documentos en estado draft o pending');

        $this->service->sendToDian($doc);
    }

    public function test_cannot_send_cancelled_document(): void
    {
        $doc = $this->crearFactura();
        ElectronicDocument::where('id', $doc->id)->update([
            'dian_status' => ElectronicDocument::STATUS_CANCELLED,
        ]);
        $doc->refresh();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->sendToDian($doc);
    }

    // ─── sendToDian: credit note uses sendCreditNote endpoint ───

    public function test_credit_note_uses_credit_note_endpoint(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response(['track_id' => 'T1', 'message' => 'OK'], 200),
            '*/api/ubl2.1/credit-note' => Http::response(['track_id' => 'T-CN', 'message' => 'OK'], 200),
        ]);

        $doc = $this->crearFactura();
        $this->service->sendToDian($doc, $this->merchant->id);

        // Simulate approval
        ElectronicDocument::where('id', $doc->id)->update([
            'dian_status'      => ElectronicDocument::STATUS_APPROVED,
            'dian_approved_at' => now(),
        ]);
        $doc->refresh();

        // Create credit note
        $cn = $this->service->createCreditNote($doc, 'Devolución', [], $this->merchant->id);
        $this->assertEquals(ElectronicDocument::TYPE_CREDIT_NOTE, $cn->document_type);

        // Send credit note
        $result = $this->service->sendToDian($cn, $this->merchant->id);
        $this->assertTrue($result['success']);

        // Verify credit-note endpoint was used
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'credit-note');
        });
    }

    // ─── checkDianStatus ───

    public function test_check_status_updates_to_approved(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response(['track_id' => 'T-APPROVE', 'message' => 'OK'], 200),
            '*/api/ubl2.1/status/T-APPROVE' => Http::response([
                'status'   => 'approved',
                'message'  => 'Validado por la DIAN',
                'is_valid' => true,
            ], 200),
            '*/api/ubl2.1/status/xml/T-APPROVE' => Http::response([
                'xml' => base64_encode('<SignedInvoice>content</SignedInvoice>'),
            ], 200),
        ]);

        $doc = $this->crearFactura();
        $this->service->sendToDian($doc, $this->merchant->id);
        $doc->refresh();

        $this->assertEquals(ElectronicDocument::STATUS_PENDING, $doc->dian_status);

        // Check status
        $result = $this->service->checkDianStatus($doc, $this->merchant->id);

        $this->assertTrue($result['success']);
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_APPROVED, $doc->dian_status);
        $this->assertNotNull($doc->dian_approved_at);
        $this->assertNotNull($doc->xml_signed);

        // Approval log
        $log = $doc->logs()->where('action', 'approved_by_dian')->first();
        $this->assertNotNull($log);
    }

    public function test_check_status_updates_to_rejected(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response(['track_id' => 'T-REJECT', 'message' => 'OK'], 200),
            '*/api/ubl2.1/status/T-REJECT' => Http::response([
                'status'   => 'rejected',
                'message'  => 'NIT inválido',
                'is_valid' => false,
            ], 200),
        ]);

        $doc = $this->crearFactura();
        $this->service->sendToDian($doc, $this->merchant->id);
        $doc->refresh();

        $result = $this->service->checkDianStatus($doc, $this->merchant->id);

        $this->assertTrue($result['success']);
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_REJECTED, $doc->dian_status);
        $this->assertEquals('NIT inválido', $doc->dian_response_message);
    }

    public function test_check_status_without_track_id_returns_error(): void
    {
        $doc = $this->crearFactura();

        $result = $this->service->checkDianStatus($doc);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('track_id', $result['message']);
    }

    // ─── Full lifecycle: create → send → approve → credit note ───

    public function test_full_lifecycle(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response(['track_id' => 'T-FULL', 'message' => 'OK'], 200),
            '*/api/ubl2.1/status/T-FULL' => Http::response([
                'status'   => 'approved',
                'message'  => 'Aprobado',
                'is_valid' => true,
            ], 200),
            '*/api/ubl2.1/status/xml/T-FULL' => Http::response([
                'xml' => base64_encode('<SignedXML/>'),
            ], 200),
            '*/api/ubl2.1/credit-note' => Http::response(['track_id' => 'T-CN-FULL', 'message' => 'OK'], 200),
        ]);

        // 1. Create
        $doc = $this->crearFactura();
        $this->assertEquals(ElectronicDocument::STATUS_DRAFT, $doc->dian_status);

        // 2. Send
        $sendResult = $this->service->sendToDian($doc, $this->merchant->id);
        $this->assertTrue($sendResult['success']);
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_PENDING, $doc->dian_status);

        // 3. Check status → approved
        $this->service->checkDianStatus($doc, $this->merchant->id);
        $doc->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_APPROVED, $doc->dian_status);

        // 4. Create credit note
        $cn = $this->service->createCreditNote($doc, 'Devolución total', [], $this->merchant->id);
        $this->assertEquals(ElectronicDocument::TYPE_CREDIT_NOTE, $cn->document_type);
        $this->assertEquals(ElectronicDocument::STATUS_DRAFT, $cn->dian_status);
        $this->assertEquals($doc->id, $cn->reference_document_id);

        // 5. Send credit note
        $cnResult = $this->service->sendToDian($cn, $this->merchant->id);
        $this->assertTrue($cnResult['success']);
        $cn->refresh();
        $this->assertEquals(ElectronicDocument::STATUS_PENDING, $cn->dian_status);

        // Verify total logs count across lifecycle
        $totalLogs = $doc->logs()->count() + $cn->logs()->count();
        $this->assertGreaterThanOrEqual(4, $totalLogs); // created + sent + approved + cn_created + cn_sent
    }

    // ─── API Controller integration ───

    public function test_send_endpoint_returns_cufe_and_track_id(): void
    {
        Http::fake([
            '*/api/ubl2.1/*' => Http::response([
                'track_id' => 'T-API-001',
                'cufe'     => str_repeat('f', 96),
                'message'  => 'Recibido',
            ], 200),
        ]);

        $token = $this->merchant->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        // Create via API
        $createRes = $this->postJson('/api/merchant/invoicing', [
            'issuer_nit'                   => '900123456',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1098765432',
            'customer_name'                => 'Test',
            'items' => [[
                'description' => 'Test item',
                'quantity'    => 1,
                'unit_price'  => 10000,
                'tax_rate'    => 19,
            ]],
        ], $headers);

        $docId = $createRes->json('data.id');

        // Send via API
        $sendRes = $this->postJson("/api/merchant/invoicing/{$docId}/send", [], $headers);

        $sendRes->assertOk()
            ->assertJsonPath('data.dian_status', 'pending')
            ->assertJsonPath('track_id', 'T-API-001')
            ->assertJsonStructure(['cufe']);
    }

    public function test_check_status_endpoint(): void
    {
        Http::fake([
            '*/api/ubl2.1/invoice' => Http::response(['track_id' => 'T-STATUS', 'message' => 'OK'], 200),
            '*/api/ubl2.1/status/T-STATUS' => Http::response([
                'status'   => 'approved',
                'message'  => 'Aprobado',
                'is_valid' => true,
            ], 200),
            '*/api/ubl2.1/status/xml/T-STATUS' => Http::response(['xml' => base64_encode('<X/>')], 200),
        ]);

        $token = $this->merchant->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        // Create & send
        $createRes = $this->postJson('/api/merchant/invoicing', [
            'issuer_nit'                   => '900123456',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1098765432',
            'customer_name'                => 'Test',
            'items' => [['description' => 'Item', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 19]],
        ], $headers);
        $docId = $createRes->json('data.id');
        $this->postJson("/api/merchant/invoicing/{$docId}/send", [], $headers);

        // Check status
        $statusRes = $this->getJson("/api/merchant/invoicing/{$docId}/status", $headers);

        $statusRes->assertOk()
            ->assertJsonPath('data.dian_status', 'approved');
    }
}
