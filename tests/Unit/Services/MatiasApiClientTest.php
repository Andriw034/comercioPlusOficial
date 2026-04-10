<?php

namespace Tests\Unit\Services;

use App\Services\ElectronicInvoicing\MatiasApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MatiasApiClientTest extends TestCase
{
    private MatiasApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'invoicing.matias_api.base_url' => 'https://api.matias-api.com',
            'invoicing.matias_api.api_key'  => 'test-api-key-123',
            'invoicing.environment'         => 'test',
        ]);
        $this->client = new MatiasApiClient();
    }

    // ─── sendInvoice ───

    public function test_send_invoice_returns_success(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/invoice' => Http::response([
                'track_id' => 'abc-123-track',
                'cufe'     => str_repeat('a', 96),
                'message'  => 'Documento recibido correctamente',
            ], 200),
        ]);

        $result = $this->client->sendInvoice('<Invoice>test</Invoice>');

        $this->assertTrue($result['success']);
        $this->assertEquals('abc-123-track', $result['track_id']);
        $this->assertEquals(str_repeat('a', 96), $result['cufe']);
        $this->assertEquals('Documento recibido correctamente', $result['message']);
        $this->assertArrayHasKey('raw_response', $result);
    }

    public function test_send_invoice_handles_server_error(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/invoice' => Http::response('Internal Server Error', 500),
        ]);

        $result = $this->client->sendInvoice('<Invoice>test</Invoice>');

        $this->assertFalse($result['success']);
        $this->assertNull($result['track_id']);
        $this->assertNull($result['cufe']);
        $this->assertStringContainsString('500', $result['message']);
    }

    public function test_send_invoice_handles_connection_error(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/invoice' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $result = $this->client->sendInvoice('<Invoice>test</Invoice>');

        $this->assertFalse($result['success']);
        $this->assertNull($result['track_id']);
        $this->assertStringContainsString('Connection refused', $result['message']);
    }

    public function test_send_invoice_sends_base64_encoded_xml(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/invoice' => Http::response(['track_id' => 'x'], 200),
        ]);

        $xml = '<Invoice>contenido test</Invoice>';
        $this->client->sendInvoice($xml);

        Http::assertSent(function ($request) use ($xml) {
            $body = $request->data();
            return $body['xml'] === base64_encode($xml)
                && $body['test_mode'] === true;
        });
    }

    // ─── sendCreditNote ───

    public function test_send_credit_note_uses_correct_endpoint(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/credit-note' => Http::response([
                'track_id' => 'cn-track',
                'cufe'     => str_repeat('b', 96),
                'message'  => 'OK',
            ], 200),
        ]);

        $result = $this->client->sendCreditNote('<CreditNote>test</CreditNote>');

        $this->assertTrue($result['success']);
        $this->assertEquals('cn-track', $result['track_id']);
    }

    // ─── getDocumentStatus ───

    public function test_get_status_returns_data(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/status/track-123' => Http::response([
                'status'   => 'approved',
                'message'  => 'Documento validado por la DIAN',
                'is_valid' => true,
            ], 200),
        ]);

        $result = $this->client->getDocumentStatus('track-123');

        $this->assertTrue($result['success']);
        $this->assertEquals('approved', $result['status']);
        $this->assertTrue($result['is_valid']);
    }

    public function test_get_status_handles_not_found(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/status/bad-id' => Http::response('Not Found', 404),
        ]);

        $result = $this->client->getDocumentStatus('bad-id');

        $this->assertFalse($result['success']);
        $this->assertNull($result['status']);
    }

    // ─── downloadSignedXml ───

    public function test_download_signed_xml_returns_decoded(): void
    {
        $signedXml = '<Invoice signed="true">content</Invoice>';

        Http::fake([
            'api.matias-api.com/api/ubl2.1/status/xml/track-123' => Http::response([
                'xml' => base64_encode($signedXml),
            ], 200),
        ]);

        $result = $this->client->downloadSignedXml('track-123');

        $this->assertEquals($signedXml, $result);
    }

    public function test_download_signed_xml_returns_null_on_error(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/status/xml/bad' => Http::response('Error', 500),
        ]);

        $result = $this->client->downloadSignedXml('bad');

        $this->assertNull($result);
    }

    public function test_download_signed_xml_returns_null_when_no_xml_key(): void
    {
        Http::fake([
            'api.matias-api.com/api/ubl2.1/status/xml/track-123' => Http::response([
                'message' => 'No xml yet',
            ], 200),
        ]);

        $result = $this->client->downloadSignedXml('track-123');

        $this->assertNull($result);
    }

    // ─── Production mode ───

    public function test_send_invoice_passes_test_mode_false_in_production(): void
    {
        config(['invoicing.environment' => 'production']);

        Http::fake([
            'api.matias-api.com/api/ubl2.1/invoice' => Http::response(['track_id' => 'x'], 200),
        ]);

        $this->client->sendInvoice('<Invoice>prod</Invoice>');

        Http::assertSent(function ($request) {
            return $request->data()['test_mode'] === false;
        });
    }

    // ─── Authorization header ───

    public function test_sends_bearer_token(): void
    {
        Http::fake([
            'api.matias-api.com/*' => Http::response(['track_id' => 'x'], 200),
        ]);

        $this->client->sendInvoice('<Invoice>test</Invoice>');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-api-key-123');
        });
    }
}
