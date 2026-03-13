<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_reports')) {
            return;
        }

        Schema::create('sales_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('range_type', ['weekly', 'monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period_label', 50)->nullable();
            $table->string('currency', 3)->default('COP');
            $table->json('totals_json');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
            $table->index(['store_id', 'range_type', 'start_date']);
            $table->index(['store_id', 'generated_at']);
            $table->unique(['store_id', 'range_type', 'start_date', 'end_date'], 'unique_report_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_reports');
    }
};
