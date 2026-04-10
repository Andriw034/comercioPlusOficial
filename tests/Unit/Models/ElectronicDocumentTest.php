<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentItem;
use App\Models\ElectronicDocumentTax;
use App\Models\ElectronicDocumentLog;
use App\Models\Store;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ElectronicDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function crearDocumento(array $overrides = []): ElectronicDocument
    {
        $store = Store::factory()->create();

        return ElectronicDocument::create(array_merge([
            'store_id'                     => $store->id,
            'document_type'                => ElectronicDocument::TYPE_INVOICE,
            'prefix'                       => 'FE',
            'number'                       => 1001,
            'dian_status'                  => ElectronicDocument::STATUS_DRAFT,
            'issuer_nit'                   => '901234567-8',
            'issuer_name'                  => 'Tienda Test',
            'customer_identification_type' => 'CC',
            'customer_identification'      => '1098765432',
            'customer_name'                => 'Cliente Test',
            'subtotal'                     => 100000,
            'tax_total'                    => 19000,
            'discount_total'               => 0,
            'total'                        => 119000,
            'currency'                     => 'COP',
        ], $overrides));
    }

    // ─── Fillable y estructura ───

    /** @test */
    public function tiene_campos_fillable_correctos()
    {
        $doc = new ElectronicDocument();
        $fillable = $doc->getFillable();

        $this->assertContains('store_id', $fillable);
        $this->assertContains('document_type', $fillable);
        $this->assertContains('cufe', $fillable);
        $this->assertContains('dian_status', $fillable);
        $this->assertContains('issuer_nit', $fillable);
        $this->assertContains('customer_identification', $fillable);
        $this->assertContains('subtotal', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('xml_content', $fillable);
        $this->assertContains('metadata', $fillable);
    }

    /** @test */
    public function tiene_casts_correctos()
    {
        $doc = new ElectronicDocument();
        $casts = $doc->getCasts();

        $this->assertEquals('array', $casts['metadata']);
        $this->assertEquals('datetime', $casts['dian_approved_at']);
        $this->assertEquals('date', $casts['payment_due_date']);
        $this->assertEquals('integer', $casts['number']);
    }

    /** @test */
    public function tiene_constantes_de_tipo()
    {
        $this->assertEquals('invoice', ElectronicDocument::TYPE_INVOICE);
        $this->assertEquals('credit_note', ElectronicDocument::TYPE_CREDIT_NOTE);
        $this->assertEquals('debit_note', ElectronicDocument::TYPE_DEBIT_NOTE);
    }

    /** @test */
    public function tiene_constantes_de_estado()
    {
        $this->assertEquals('draft', ElectronicDocument::STATUS_DRAFT);
        $this->assertEquals('pending', ElectronicDocument::STATUS_PENDING);
        $this->assertEquals('approved', ElectronicDocument::STATUS_APPROVED);
        $this->assertEquals('rejected', ElectronicDocument::STATUS_REJECTED);
        $this->assertEquals('cancelled', ElectronicDocument::STATUS_CANCELLED);
    }

    /** @test */
    public function usa_soft_deletes()
    {
        $doc = $this->crearDocumento();
        $doc->delete();

        $this->assertSoftDeleted('electronic_documents', ['id' => $doc->id]);
        $this->assertNotNull(ElectronicDocument::withTrashed()->find($doc->id));
    }

    // ─── Relaciones ───

    /** @test */
    public function pertenece_a_store()
    {
        $doc = $this->crearDocumento();

        $this->assertInstanceOf(Store::class, $doc->store);
        $this->assertEquals($doc->store_id, $doc->store->id);
    }

    /** @test */
    public function pertenece_a_order_nullable()
    {
        $doc = $this->crearDocumento(['order_id' => null]);
        $this->assertNull($doc->order);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $docConOrder = $this->crearDocumento(['order_id' => $order->id, 'number' => 1002]);

        $this->assertInstanceOf(Order::class, $docConOrder->order);
    }

    /** @test */
    public function tiene_muchos_items()
    {
        $doc = $this->crearDocumento();

        ElectronicDocumentItem::create([
            'electronic_document_id' => $doc->id,
            'description'            => 'Pastillas de freno',
            'quantity'               => 2,
            'unit_price'             => 25000,
            'line_total'             => 50000,
        ]);

        ElectronicDocumentItem::create([
            'electronic_document_id' => $doc->id,
            'description'            => 'Aceite motor',
            'quantity'               => 1,
            'unit_price'             => 50000,
            'line_total'             => 50000,
        ]);

        $doc->refresh();
        $this->assertCount(2, $doc->items);
        $this->assertInstanceOf(ElectronicDocumentItem::class, $doc->items->first());
    }

    /** @test */
    public function tiene_muchos_taxes()
    {
        $doc = $this->crearDocumento();

        ElectronicDocumentTax::create([
            'electronic_document_id' => $doc->id,
            'tax_type'               => 'IVA',
            'tax_rate'               => 19,
            'taxable_amount'         => 100000,
            'tax_amount'             => 19000,
        ]);

        $doc->refresh();
        $this->assertCount(1, $doc->taxes);
        $this->assertInstanceOf(ElectronicDocumentTax::class, $doc->taxes->first());
    }

    /** @test */
    public function tiene_muchos_logs()
    {
        $doc = $this->crearDocumento();

        ElectronicDocumentLog::create([
            'electronic_document_id' => $doc->id,
            'action'                 => 'created',
            'status_to'              => 'draft',
            'message'                => 'Documento creado.',
        ]);

        $doc->refresh();
        $this->assertCount(1, $doc->logs);
        $this->assertInstanceOf(ElectronicDocumentLog::class, $doc->logs->first());
    }

    /** @test */
    public function referencia_a_documento_origen()
    {
        $factura = $this->crearDocumento([
            'dian_status' => ElectronicDocument::STATUS_APPROVED,
            'cufe'        => 'CUFE-TEST-001',
        ]);

        $notaCredito = $this->crearDocumento([
            'document_type'         => ElectronicDocument::TYPE_CREDIT_NOTE,
            'prefix'                => 'NC',
            'number'                => 1,
            'reference_document_id' => $factura->id,
        ]);

        $this->assertInstanceOf(ElectronicDocument::class, $notaCredito->referenceDocument);
        $this->assertEquals($factura->id, $notaCredito->referenceDocument->id);
        $this->assertTrue($factura->referencedBy->contains($notaCredito));
    }

    // ─── Accessor ───

    /** @test */
    public function full_number_formatea_correctamente()
    {
        $doc = $this->crearDocumento(['prefix' => 'FE', 'number' => 42]);

        $this->assertEquals('FE-0000000042', $doc->full_number);
    }

    /** @test */
    public function full_number_con_numero_largo()
    {
        $doc = $this->crearDocumento(['prefix' => 'NC', 'number' => 9999999999]);

        $this->assertEquals('NC-9999999999', $doc->full_number);
    }

    // ─── Scopes ───

    /** @test */
    public function scope_approved_filtra_correctamente()
    {
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_DRAFT, 'number' => 1]);
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => 'CUFE-A1', 'number' => 2]);
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => 'CUFE-A2', 'number' => 3]);

        $this->assertEquals(2, ElectronicDocument::approved()->count());
    }

    /** @test */
    public function scope_pending_filtra_correctamente()
    {
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_PENDING, 'number' => 1]);
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_DRAFT, 'number' => 2]);

        $this->assertEquals(1, ElectronicDocument::pending()->count());
    }

    /** @test */
    public function scope_rejected_filtra_correctamente()
    {
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_REJECTED, 'number' => 1]);
        $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => 'CUFE-R1', 'number' => 2]);

        $this->assertEquals(1, ElectronicDocument::rejected()->count());
    }

    /** @test */
    public function scope_invoices_filtra_por_tipo()
    {
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_INVOICE, 'number' => 1]);
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_CREDIT_NOTE, 'number' => 2]);

        $this->assertEquals(1, ElectronicDocument::invoices()->count());
    }

    /** @test */
    public function scope_credit_notes_filtra_por_tipo()
    {
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_INVOICE, 'number' => 1]);
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_CREDIT_NOTE, 'number' => 2]);

        $this->assertEquals(1, ElectronicDocument::creditNotes()->count());
    }

    /** @test */
    public function scope_debit_notes_filtra_por_tipo()
    {
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_DEBIT_NOTE, 'number' => 1]);
        $this->crearDocumento(['document_type' => ElectronicDocument::TYPE_INVOICE, 'number' => 2]);

        $this->assertEquals(1, ElectronicDocument::debitNotes()->count());
    }

    // ─── Métodos helper ───

    /** @test */
    public function is_approved_retorna_correctamente()
    {
        $draft = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_DRAFT, 'number' => 1]);
        $approved = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => 'CUFE-IA1', 'number' => 2]);

        $this->assertFalse($draft->isApproved());
        $this->assertTrue($approved->isApproved());
    }

    /** @test */
    public function is_rejected_retorna_correctamente()
    {
        $rejected = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_REJECTED]);

        $this->assertTrue($rejected->isRejected());
    }

    /** @test */
    public function is_draft_retorna_correctamente()
    {
        $draft = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_DRAFT]);

        $this->assertTrue($draft->isDraft());
    }

    /** @test */
    public function can_be_cancelled_solo_si_aprobada_y_menor_a_5_dias()
    {
        // Aprobada hace 2 días → sí se puede cancelar
        $reciente = $this->crearDocumento([
            'dian_status' => ElectronicDocument::STATUS_APPROVED,
            'cufe'        => 'CUFE-CBC1',
            'number'      => 1,
        ]);
        $this->assertTrue($reciente->canBeCancelled());

        // Aprobada hace 10 días → no se puede cancelar
        $vieja = $this->crearDocumento([
            'dian_status' => ElectronicDocument::STATUS_APPROVED,
            'cufe'        => 'CUFE-CBC2',
            'number'      => 2,
        ]);
        ElectronicDocument::where('id', $vieja->id)
            ->update(['created_at' => now()->subDays(10)]);
        $vieja->refresh();
        $this->assertFalse($vieja->canBeCancelled());

        // Draft → no se puede cancelar
        $draft = $this->crearDocumento([
            'dian_status' => ElectronicDocument::STATUS_DRAFT,
            'number'      => 3,
        ]);
        $this->assertFalse($draft->canBeCancelled());
    }

    /** @test */
    public function can_be_edited_solo_si_draft_o_pending()
    {
        $draft = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_DRAFT, 'number' => 1]);
        $pending = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_PENDING, 'number' => 2]);
        $approved = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => 'CUFE-CBE1', 'number' => 3]);
        $rejected = $this->crearDocumento(['dian_status' => ElectronicDocument::STATUS_REJECTED, 'number' => 4]);

        $this->assertTrue($draft->canBeEdited());
        $this->assertTrue($pending->canBeEdited());
        $this->assertFalse($approved->canBeEdited());
        $this->assertFalse($rejected->canBeEdited());
    }

    // ─── Metadata JSON ───

    /** @test */
    public function metadata_se_castea_como_array()
    {
        $doc = $this->crearDocumento([
            'metadata' => ['provider' => 'matias', 'version' => '1.0'],
        ]);

        $doc->refresh();
        $this->assertIsArray($doc->metadata);
        $this->assertEquals('matias', $doc->metadata['provider']);
    }

    // ─── CUFE único ───

    /** @test */
    public function cufe_es_unico()
    {
        $this->crearDocumento(['cufe' => 'CUFE-UNIQUE-TEST', 'number' => 1]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->crearDocumento(['cufe' => 'CUFE-UNIQUE-TEST', 'number' => 2]);
    }
}
