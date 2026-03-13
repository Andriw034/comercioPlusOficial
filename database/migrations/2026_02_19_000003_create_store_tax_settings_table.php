<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('store_tax_settings')) {
            return;
        }

        Schema::create('store_tax_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->boolean('enable_tax')->default(false);
            $table->string('tax_name', 20)->default('IVA');
            $table->decimal('tax_rate', 5, 4)->default(0.1900);
            $table->boolean('prices_include_tax')->default(false);
            $table->enum('tax_rounding_mode', ['round', 'ceil', 'floor'])->default('round');
            $table->timestamps();
            $table->unique('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_tax_settings');
    }
};
