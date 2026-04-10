<?php

namespace Tests\Unit\Services;

use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentItem;
use App\Models\ElectronicDocumentTax;
use App\Models\Store;
use App\Models\User;
use App\Services\ElectronicInvoicing\XmlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class XmlGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private XmlGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new XmlGenerator();
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
            'issuer_name'                  => 'Repuestos Moto S.A.S',
            'issuer_email'                 => 'info@repuestos.co',
            'issuer_phone'                 => '3001234567',
            'issuer_address'               => 'Calle 10 # 5-30',
            'issuer_city'                  => 'Bogotá',
            'issuer_department'            => 'Bogotá D.C.',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1023456789',
            'customer_name'                => 'Juan Pérez',
            'customer_email'               => 'juan@email.com',
            'customer_phone'               => '3109876543',
            'customer_address'             => 'Carrera 7 # 20-10',
            'customer_city'                => 'Bogotá',
            'customer_department'          => 'Bogotá D.C.',
            'subtotal'                     => 100000,
            'tax_total'                    => 19000,
            'discount_total'               => 0,
            'total'                        => 119000,
            'currency'                     => 'COP',
            'payment_method'               => 'Contado',
            'payment_means'                => '10',
        ], $overrides));
    }

    private function addItems(ElectronicDocument $doc): void
    {
        ElectronicDocumentItem::create([
            'electronic_document_id' => $doc->id,
            'line_number'            => 1,
            'code'                   => 'REP-001',
            'description'            => 'Pastillas de freno delanteras',
            'unit_measure'           => 'EA',
            'quantity'               => 2,
            'unit_price'             => 25000,
            'discount'               => 0,
            'tax_amount'             => 9500,
            'line_total'             => 50000,
            'tax_type'               => 'iva',
            'tax_rate'               => 19,
        ]);

        ElectronicDocumentItem::create([
            'electronic_document_id' => $doc->id,
            'line_number'            => 2,
            'code'                   => 'REP-002',
            'description'            => 'Aceite motor 10W-40 1L',
            'unit_measure'           => 'EA',
            'quantity'               => 2,
            'unit_price'             => 25000,
            'discount'               => 0,
            'tax_amount'             => 9500,
            'line_total'             => 50000,
            'tax_type'               => 'iva',
            'tax_rate'               => 19,
        ]);
    }

    private function addTaxes(ElectronicDocument $doc): void
    {
        ElectronicDocumentTax::create([
            'electronic_document_id' => $doc->id,
            'tax_type'               => 'iva',
            'tax_rate'               => 19,
            'taxable_amount'         => 100000,
            'tax_amount'             => 19000,
        ]);
    }

    // ─── Tests ───

    public function test_generates_valid_xml_string(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);
        $this->addTaxes($doc);

        $xml = $this->generator->generate($doc);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
    }

    public function test_xml_is_parseable_dom(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);
        $this->addTaxes($doc);

        $xml = $this->generator->generate($doc);

        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($xml);

        $this->assertTrue($loaded, 'XML debe ser válido y parseable');
    }

    public function test_xml_has_correct_namespaces(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', $xml);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2', $xml);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2', $xml);
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2', $xml);
    }

    public function test_xml_contains_ubl_version(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>', $xml);
        $this->assertStringContainsString('<cbc:CustomizationID>10</cbc:CustomizationID>', $xml);
        $this->assertStringContainsString('<cbc:ProfileID>DIAN 2.1</cbc:ProfileID>', $xml);
    }

    public function test_xml_contains_document_id(): void
    {
        $doc = $this->crearDocumento(['prefix' => 'FE', 'number' => 42]);
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:ID>FE42</cbc:ID>', $xml);
    }

    public function test_xml_contains_invoice_type_code(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>', $xml);
    }

    public function test_xml_contains_currency(): void
    {
        $doc = $this->crearDocumento(['currency' => 'COP']);
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>', $xml);
    }

    public function test_xml_contains_supplier_party(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('cac:AccountingSupplierParty', $xml);
        $this->assertStringContainsString('Repuestos Moto S.A.S', $xml);
        $this->assertStringContainsString('900123456', $xml);
    }

    public function test_xml_contains_customer_party(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('cac:AccountingCustomerParty', $xml);
        $this->assertStringContainsString('1023456789', $xml);
    }

    public function test_xml_contains_payment_means(): void
    {
        $doc = $this->crearDocumento(['payment_means' => '10']);
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('PaymentMeansCode', $xml);
        $this->assertStringContainsString('>10<', $xml);
    }

    public function test_xml_contains_tax_total(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);
        $this->addTaxes($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('cac:TaxTotal', $xml);
        $this->assertStringContainsString('19000.00', $xml);
    }

    public function test_xml_contains_legal_monetary_total(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('cac:LegalMonetaryTotal', $xml);
        $this->assertStringContainsString('100000.00', $xml);  // subtotal
        $this->assertStringContainsString('119000.00', $xml);  // total
    }

    public function test_xml_contains_invoice_lines(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('cac:InvoiceLine', $xml);
        $this->assertStringContainsString('Pastillas de freno delanteras', $xml);
        $this->assertStringContainsString('Aceite motor 10W-40 1L', $xml);
        $this->assertStringContainsString('REP-001', $xml);
    }

    public function test_xml_has_ubl_extensions_placeholder(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('ext:UBLExtensions', $xml);
        $this->assertStringContainsString('ext:ExtensionContent', $xml);
    }

    public function test_credit_note_uses_correct_root_and_type(): void
    {
        $doc = $this->crearDocumento([
            'document_type' => ElectronicDocument::TYPE_CREDIT_NOTE,
            'prefix'        => 'NC',
        ]);
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<CreditNote', $xml);
        $this->assertStringContainsString('CreditNote-2', $xml);
        $this->assertStringContainsString('<cbc:CreditNoteTypeCode>91</cbc:CreditNoteTypeCode>', $xml);
        $this->assertStringContainsString('cac:CreditNoteLine', $xml);
    }

    public function test_debit_note_uses_correct_root_and_type(): void
    {
        $doc = $this->crearDocumento([
            'document_type' => ElectronicDocument::TYPE_DEBIT_NOTE,
            'prefix'        => 'ND',
        ]);
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<DebitNote', $xml);
        $this->assertStringContainsString('<cbc:DebitNoteTypeCode>92</cbc:DebitNoteTypeCode>', $xml);
        $this->assertStringContainsString('cac:DebitNoteLine', $xml);
    }

    public function test_throws_exception_when_no_items(): void
    {
        $doc = $this->crearDocumento();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no tiene ítems');

        $this->generator->generate($doc);
    }

    public function test_xml_line_count_matches_items(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:LineCountNumeric>2</cbc:LineCountNumeric>', $xml);
    }

    public function test_xml_environment_is_test_by_default(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('<cbc:ProfileExecutionID>2</cbc:ProfileExecutionID>', $xml);
    }

    public function test_xml_amounts_have_currency_attribute(): void
    {
        $doc = $this->crearDocumento();
        $this->addItems($doc);

        $xml = $this->generator->generate($doc);

        $this->assertStringContainsString('currencyID="COP"', $xml);
    }
}
