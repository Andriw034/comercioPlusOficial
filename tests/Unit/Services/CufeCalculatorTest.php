<?php

namespace Tests\Unit\Services;

use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentTax;
use App\Models\Store;
use App\Models\User;
use App\Services\ElectronicInvoicing\CufeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CufeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private CufeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CufeCalculator();
    }

    private function crearDocumento(array $overrides = []): ElectronicDocument
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);

        return ElectronicDocument::create(array_merge([
            'store_id'                     => $store->id,
            'document_type'                => ElectronicDocument::TYPE_INVOICE,
            'prefix'                       => 'FE',
            'number'                       => 1,
            'dian_status'                  => ElectronicDocument::STATUS_DRAFT,
            'issuer_nit'                   => '900123456',
            'issuer_name'                  => 'Test Store',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1023456789',
            'customer_name'                => 'Test Customer',
            'subtotal'                     => 100000,
            'tax_total'                    => 19000,
            'discount_total'               => 0,
            'total'                        => 119000,
            'currency'                     => 'COP',
        ], $overrides));
    }

    // ─── Tests ───

    public function test_cufe_is_96_characters(): void
    {
        $doc = $this->crearDocumento();

        $cufe = $this->calculator->calculate($doc);

        $this->assertEquals(96, strlen($cufe), "CUFE debe ser 96 caracteres (SHA-384 hex)");
    }

    public function test_cufe_is_hexadecimal(): void
    {
        $doc = $this->crearDocumento();

        $cufe = $this->calculator->calculate($doc);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{96}$/', $cufe);
    }

    public function test_cufe_is_deterministic(): void
    {
        $doc = $this->crearDocumento();

        $cufe1 = $this->calculator->calculate($doc);
        $cufe2 = $this->calculator->calculate($doc);

        $this->assertEquals($cufe1, $cufe2, 'CUFE debe ser determinístico para el mismo documento');
    }

    public function test_cufe_changes_with_different_total(): void
    {
        $doc1 = $this->crearDocumento(['total' => 100000, 'number' => 1]);
        $doc2 = $this->crearDocumento(['total' => 200000, 'number' => 2]);

        $cufe1 = $this->calculator->calculate($doc1);
        $cufe2 = $this->calculator->calculate($doc2);

        $this->assertNotEquals($cufe1, $cufe2);
    }

    public function test_cufe_changes_with_different_nit(): void
    {
        $doc1 = $this->crearDocumento(['issuer_nit' => '900111111', 'number' => 1]);
        $doc2 = $this->crearDocumento(['issuer_nit' => '900222222', 'number' => 2]);

        $cufe1 = $this->calculator->calculate($doc1);
        $cufe2 = $this->calculator->calculate($doc2);

        $this->assertNotEquals($cufe1, $cufe2);
    }

    public function test_cufe_changes_with_different_number(): void
    {
        $doc1 = $this->crearDocumento(['number' => 100]);
        $doc2 = $this->crearDocumento(['number' => 200]);

        $cufe1 = $this->calculator->calculate($doc1);
        $cufe2 = $this->calculator->calculate($doc2);

        $this->assertNotEquals($cufe1, $cufe2);
    }

    public function test_raw_string_contains_expected_components(): void
    {
        $doc = $this->crearDocumento([
            'prefix'                  => 'FE',
            'number'                  => 42,
            'issuer_nit'              => '900123456',
            'customer_identification' => '1023456789',
            'subtotal'                => 100000,
            'total'                   => 119000,
        ]);

        $raw = $this->calculator->getRawString($doc);

        $this->assertStringContainsString('FE42', $raw);           // NumFac
        $this->assertStringContainsString('100000.00', $raw);      // ValFac
        $this->assertStringContainsString('119000.00', $raw);      // ValTot
        $this->assertStringContainsString('900123456', $raw);      // NitOFE
        $this->assertStringContainsString('1023456789', $raw);     // NumAdq
        $this->assertStringContainsString('01', $raw);             // CodImp1 IVA
        $this->assertStringContainsString('04', $raw);             // CodImp2 INC
        $this->assertStringContainsString('03', $raw);             // CodImp3 ICA
    }

    public function test_cufe_includes_tax_amounts_from_relation(): void
    {
        $doc = $this->crearDocumento();

        ElectronicDocumentTax::create([
            'electronic_document_id' => $doc->id,
            'tax_type'               => 'iva',
            'tax_rate'               => 19,
            'taxable_amount'         => 100000,
            'tax_amount'             => 19000,
        ]);

        $raw = $this->calculator->getRawString($doc);

        // IVA amount should appear after code 01
        $this->assertStringContainsString('0119000.00', $raw);
        // INC and ICA should be 0.00
        $this->assertStringContainsString('040.00', $raw);
        $this->assertStringContainsString('030.00', $raw);
    }

    public function test_cude_is_96_characters(): void
    {
        $doc = $this->crearDocumento([
            'document_type' => ElectronicDocument::TYPE_CREDIT_NOTE,
        ]);

        $cude = $this->calculator->calculateCude($doc);

        $this->assertEquals(96, strlen($cude));
    }

    public function test_cude_is_hexadecimal(): void
    {
        $doc = $this->crearDocumento([
            'document_type' => ElectronicDocument::TYPE_CREDIT_NOTE,
        ]);

        $cude = $this->calculator->calculateCude($doc);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{96}$/', $cude);
    }

    public function test_environment_id_in_raw_string(): void
    {
        config(['invoicing.environment' => 'test']);
        $doc = $this->crearDocumento();

        $raw = $this->calculator->getRawString($doc);

        // Last character should be environment (2 for test)
        $this->assertStringEndsWith('2', $raw);
    }
}
