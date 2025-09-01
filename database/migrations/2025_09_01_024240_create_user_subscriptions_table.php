<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subscription_type'); // 'free', 'premium', 'enterprise'
            $table->string('status')->default('active'); // 'active', 'inactive', 'cancelled'
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->json('features')->nullable(); // JSON array of features included
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('COP');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['subscription_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
