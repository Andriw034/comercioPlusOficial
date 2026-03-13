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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Items de la orden (JSON)
            $table->json('items');
            
            // Datos del cliente
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            
            // Información de pago
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['PSE', 'NEQUI', 'BANCOLOMBIA', 'CARD']);
            
            // Estado de la orden
            $table->enum('status', [
                'pending', 
                'paid', 
                'payment_failed', 
                'shipped', 
                'delivered', 
                'cancelled'
            ])->default('pending');
            
            // Datos de Wompi
            $table->string('payment_reference')->nullable()->unique();
            $table->string('wompi_transaction_id')->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'approved', 'failed'])->default('pending');
            $table->timestamp('payment_approved_at')->nullable();
            $table->timestamp('payment_failed_at')->nullable();
            $table->json('wompi_data')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('payment_reference');
            $table->index('wompi_transaction_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
