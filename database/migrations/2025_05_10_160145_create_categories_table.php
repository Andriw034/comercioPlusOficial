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
     * - slug único para URLs/consistencia
     * - popularity (score) y sales_count para identificar categorías populares
     * - is_popular boolean para marcar manualmente una categoría como destacada
     * - índices útiles para búsquedas y ordenamiento
     *
     * Con estos campos podrás, desde el controlador de creación de productos,
     * hacer queries como: Category::orderByDesc('popularity')->limit(8)->get()
     * o Category::where('is_popular', true)->get() para llenar el <select>
     * con las categorías más populares.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Nombre visible de la categoría (ej: Lubricantes, Frenos...)
            $table->string('name')->index();

            // Slug para url amigables y búsquedas rápidas (único)
            $table->string('slug')->unique();

            // Contador de ventas relacionadas (puedes actualizarlo desde lógica de órdenes)
            $table->unsignedBigInteger('sales_count')->default(0);

            // Puntuación de popularidad (puede combinar ventas, views, conversiones...)
            $table->unsignedInteger('popularity')->default(0)->comment('Score de popularidad para ordenar categorieas');

            // Marca manual para destacar categorías en UI
            $table->boolean('is_popular')->default(false)->index();

            // Texto opcional con descripción corta (útil para tooltips)
            $table->string('short_description')->nullable();

            // Orden manual para listados si se requiere prioridad personalizada
            $table->unsignedSmallInteger('sort_order')->nullable()->default(null);

            // Timestamps
            $table->timestamps();

            // Índices compuestos si buscas por popularidad y orden
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
