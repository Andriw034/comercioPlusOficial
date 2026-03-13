<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_picking_events')) {
            return;
        }

        Schema::create('order_picking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_product_id')->nullable()->constrained('order_products')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('mode', ['scanner', 'manual', 'system'])->default('scanner');
            $table->enum('action', [
                'scan_ok',
                'scan_error',
                'manual_pick',
                'manual_missing',
                'manual_note',
                'fallback_triggered',
                'picking_completed',
                'picking_reset',
            ])->default('scan_ok');
            $table->string('code', 191)->nullable();
            $table->unsignedInteger('qty')->default(0);
            $table->string('error_code', 64)->nullable();
            $table->string('message', 255)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at'], 'order_picking_events_order_created_at_index');
            $table->index(['order_id', 'mode', 'action'], 'order_picking_events_order_mode_action_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_picking_events');
    }
};

