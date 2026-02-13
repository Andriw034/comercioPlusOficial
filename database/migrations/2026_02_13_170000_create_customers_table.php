<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_visited_at');
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
            $table->index('store_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

