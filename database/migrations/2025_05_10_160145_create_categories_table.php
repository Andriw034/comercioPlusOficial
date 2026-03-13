<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta migration extiende la tabla categories para soportar:
     * - slug Ãºnico para URLs/consistencia
     * - popularity (score) y sales_count para identificar categorÃ­as populares
     * - is_popular boolean para marcar manualmente una categorÃ­a como destacada
     * - Ã­ndices Ãºtiles para bÃºsquedas y ordenamiento
     *
     * Con estos campos podrÃ¡s, desde el controlador de creaciÃ³n de productos,
     * hacer queries como: Category::orderByDesc('popularity')->limit(8)->get()
     * o Category::where('is_popular', true)->get() para llenar el <select>
     * con las categorÃ­as mÃ¡s populares.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Nombre visible de la categorÃ­a (ej: Lubricantes, Frenos...)
            $table->string('name')->index();

            // Slug para url amigables y bÃºsquedas rÃ¡pidas (Ãºnico)
            $table->string('slug')->unique();

            // Contador de ventas relacionadas (puedes actualizarlo desde lÃ³gica de Ã³rdenes)
            $table->unsignedBigInteger('sales_count')->default(0);

            // PuntuaciÃ³n de popularidad (puede combinar ventas, views, conversiones...)
            $table->unsignedInteger('popularity')->default(0)->comment('Score de popularidad para ordenar categorieas');

            // Marca manual para destacar categorÃ­as en UI
            $table->boolean('is_popular')->default(false)->index();

            // Texto opcional con descripciÃ³n corta (Ãºtil para tooltips)
            $table->string('short_description')->nullable();

            // Orden manual para listados si se requiere prioridad personalizada
            $table->unsignedSmallInteger('sort_order')->nullable()->default(null);

            // Timestamps
            $table->timestamps();

            // Ãndices compuestos si buscas por popularidad y orden
            $table->index(['popularity', 'sales_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
