<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_requests')) {
            Schema::create('purchase_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
                $table->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');
                $table->string('period_tag', 20)->nullable();
                $table->text('notes')->nullable();
                $table->date('expected_date')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->index(['store_id', 'status']);
                $table->index(['store_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('purchase_request_items')) {
            Schema::create('purchase_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->integer('current_stock')->default(0);
                $table->integer('suggested_qty')->default(0);
                $table->integer('ordered_qty')->default(0);
                $table->decimal('last_cost', 12, 2)->nullable();
                $table->timestamps();
                $table->unique(['purchase_request_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
