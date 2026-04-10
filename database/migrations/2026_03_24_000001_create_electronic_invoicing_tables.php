<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Documentos electrónicos (factura principal) ───
        Schema::create('electronic_documents', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            // Identificación del documento
            $table->string('document_type', 10);         // FEV, NCE, NDE
            $table->string('prefix', 10);                 // SETT, NC, ND
            $table->unsignedBigInteger('number');
            $table->string('cufe', 120)->nullable()->unique();  // Código Único de Factura Electrónica
            $table->string('cude', 120)->nullable();             // Código Único de Documento Electrónico (notas)

            // Estado DIAN
            $table->enum('dian_status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->string('dian_track_id', 100)->nullable();    // UUID de seguimiento DIAN
            $table->timestamp('dian_approved_at')->nullable();
            $table->text('dian_response_message')->nullable();

            // Emisor (merchant / tienda)
            $table->string('issuer_nit', 20);
            $table->string('issuer_name');
            $table->string('issuer_email')->nullable();
            $table->string('issuer_phone', 30)->nullable();
            $table->text('issuer_address')->nullable();
            $table->string('issuer_city', 100)->nullable();
            $table->string('issuer_department', 100)->nullable();

            // Adquiriente (cliente)
            $table->string('customer_identification_type', 10);  // CC, NIT, CE, PP, TI
            $table->string('customer_identification', 30);
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_city', 100)->nullable();
            $table->string('customer_department', 100)->nullable();

            // Totales
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 3)->default('COP');

            // Forma de pago
            $table->string('payment_method', 30)->nullable();     // contado, credito
            $table->string('payment_means', 30)->nullable();      // efectivo, transferencia, tarjeta
            $table->date('payment_due_date')->nullable();

            // Archivos generados
            $table->longText('xml_content')->nullable();
            $table->longText('xml_signed')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('qr_code')->nullable();

            // Referencia a documento origen (para notas crédito/débito)
            $table->foreignId('reference_document_id')->nullable()
                ->constrained('electronic_documents')->onDelete('set null');

            // Metadata flexible
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices de performance
            $table->index(['store_id', 'document_type']);
            $table->index('dian_status');
            $table->index('created_at');
            $table->index(['store_id', 'prefix', 'number']);
            $table->index('customer_identification');
        });

        // ─── Items de documento electrónico ───
        Schema::create('electronic_document_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('electronic_document_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->unsignedSmallInteger('line_number')->default(1);
            $table->string('code', 50)->nullable();          // SKU / código interno
            $table->string('description');
            $table->string('unit_measure', 10)->default('EA');  // EA=unidad, KGM, MTR, etc.

            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            // Desglose impuesto por línea
            $table->string('tax_type', 10)->default('IVA');   // IVA, INC, ICA
            $table->decimal('tax_rate', 5, 2)->default(19.00);

            $table->timestamps();

            $table->index('electronic_document_id');
        });

        // ─── Impuestos consolidados del documento ───
        Schema::create('electronic_document_taxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('electronic_document_id')->constrained()->onDelete('cascade');

            $table->string('tax_type', 10);          // IVA, INC, ICA, RteFte, RteIVA, RteICA
            $table->decimal('tax_rate', 5, 2);        // 0, 5, 8, 19
            $table->decimal('taxable_amount', 15, 2); // base gravable
            $table->decimal('tax_amount', 15, 2);     // valor del impuesto

            $table->timestamps();

            $table->index('electronic_document_id');
        });

        // ─── Log de auditoría ───
        Schema::create('electronic_document_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('electronic_document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->string('action', 50);             // created, sent_to_dian, approved, rejected, cancelled, resent, downloaded
            $table->string('status_from', 20)->nullable();
            $table->string('status_to', 20)->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();       // request/response DIAN completo
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index('electronic_document_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_document_logs');
        Schema::dropIfExists('electronic_document_taxes');
        Schema::dropIfExists('electronic_document_items');
        Schema::dropIfExists('electronic_documents');
    }
};
