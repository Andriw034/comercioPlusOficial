<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('store_counters')) {
            return;
        }

        Schema::create('store_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->unsignedBigInteger('next_product_barcode')->default(1);
            $table->timestamps();

            $table->unique('store_id', 'store_counters_store_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_counters');
    }
};
