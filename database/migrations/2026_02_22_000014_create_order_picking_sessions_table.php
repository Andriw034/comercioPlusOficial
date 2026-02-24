<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_picking_sessions')) {
            return;
        }

        Schema::create('order_picking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('scan_consecutive_failures')->default(0);
            $table->boolean('fallback_required')->default(false);
            $table->string('last_error_code', 64)->nullable();
            $table->string('last_code', 191)->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'user_id'], 'order_picking_sessions_order_user_unique');
            $table->index(['order_id', 'fallback_required'], 'order_picking_sessions_order_fallback_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_picking_sessions');
    }
};

