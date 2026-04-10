<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_motorcycle_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('motorcycle_model_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'motorcycle_model_id'], 'product_moto_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_motorcycle_compatibility');
    }
};
