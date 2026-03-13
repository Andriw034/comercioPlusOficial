<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auto_restock_settings')) {
            Schema::create('auto_restock_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete()->unique();
                $table->boolean('enabled')->default(false);
                $table->unsignedInteger('min_stock_threshold')->default(5);
                $table->unsignedInteger('days_of_stock_target')->default(30);
                $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly'])->default('weekly');
                $table->boolean('auto_approve')->default(false);
                $table->json('excluded_product_ids')->nullable();
                $table->string('supplier_email')->nullable();
                $table->string('supplier_whatsapp', 50)->nullable();
                $table->timestamp('last_generated_at')->nullable();
                $table->timestamps();
                $table->index(['enabled', 'frequency']);
            });
        }

        if (! Schema::hasTable('stock_predictions')) {
            Schema::create('stock_predictions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->integer('current_stock');
                $table->decimal('avg_daily_sales', 8, 4);
                $table->integer('predicted_days_until_depletion')->nullable();
                $table->date('predicted_depletion_date')->nullable();
                $table->integer('recommended_restock_quantity');
                $table->json('calculation_details')->nullable();
                $table->timestamps();
                $table->index(['product_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('ai_metrics_cache')) {
            Schema::create('ai_metrics_cache', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
                $table->string('metric_type', 50);
                $table->json('metric_data');
                $table->timestamp('calculated_at');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->index(['store_id', 'metric_type']);
                $table->index('expires_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_metrics_cache');
        Schema::dropIfExists('stock_predictions');
        Schema::dropIfExists('auto_restock_settings');
    }
};
