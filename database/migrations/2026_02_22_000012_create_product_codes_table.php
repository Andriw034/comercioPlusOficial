<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_codes')) {
            return;
        }

        Schema::create('product_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('type', ['barcode', 'qr', 'sku'])->default('barcode');
            $table->string('value', 191);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['store_id', 'type', 'value'], 'product_codes_store_type_value_unique');
            $table->index(['product_id', 'is_primary'], 'product_codes_product_primary_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_codes');
    }
};

