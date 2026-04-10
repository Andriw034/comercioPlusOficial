<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts_compatibility', function (Blueprint $table) {
            $table->id();

            // Información del Repuesto
            $table->string('part_reference', 100);
            $table->string('part_type', 50);
            $table->string('part_brand', 50);
            $table->text('part_description')->nullable();

            // Compatibilidad con Motos
            $table->string('motorcycle_brand', 50);
            $table->string('motorcycle_model', 100);
            $table->integer('year_from');
            $table->integer('year_to');

            // Notas adicionales
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index('part_reference');
            $table->index('part_type');
            $table->index(['motorcycle_brand', 'motorcycle_model']);
            $table->index(['year_from', 'year_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_compatibility');
    }
};
