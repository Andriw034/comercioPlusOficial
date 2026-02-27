<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
            $table->unique(['store_id', 'customer_id']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_accounts');
    }
};
