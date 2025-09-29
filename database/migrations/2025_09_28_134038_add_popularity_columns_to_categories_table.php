<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Añade campos necesarios para soportar la lógica de categorías populares:
     * - slug (único)
     * - sales_count (contador de ventas)
     * - popularity (score para ordenar)
     * - is_popular (marcado manual)
     * - short_description (descripción corta)
     *
     * Nota: si tu tabla ya tiene 'slug' (u otros), puedes comentar la línea correspondiente.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Añadimos columnas sólo si no existen (protección si vuelves a ejecutar)
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }

            if (!Schema::hasColumn('categories', 'sales_count')) {
                $table->unsignedBigInteger('sales_count')->default(0)->after('slug');
            }

            if (!Schema::hasColumn('categories', 'popularity')) {
                $table->unsignedInteger('popularity')->default(0)->after('sales_count')->comment('Score de popularidad para ordenar categorias');
            }

            if (!Schema::hasColumn('categories', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('popularity')->index();
            }

            if (!Schema::hasColumn('categories', 'short_description')) {
                $table->string('short_description')->nullable()->after('is_popular');
            }

            // Índice compuesto opcional para consultas por popularidad
            // Evitamos crear índice duplicado si ya existe
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('categories'));
            } catch (\Throwable $e) {
                $indexes = [];
            }

            if (!in_array('categories_popularity_sales_count_index', $indexes)) {
                $table->index(['popularity', 'sales_count'], 'categories_popularity_sales_count_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminamos los índices antes de columnas si existen
            if (Schema::hasColumn('categories', 'popularity') && Schema::hasColumn('categories', 'sales_count')) {
                // Intentamos eliminar índice por nombre; si no existe, no falla
                try {
                    $table->dropIndex('categories_popularity_sales_count_index');
                } catch (\Throwable $e) {
                    // índice no existe o DB diferente; ignorar
                }
            }

            if (Schema::hasColumn('categories', 'short_description')) {
                $table->dropColumn('short_description');
            }
            if (Schema::hasColumn('categories', 'is_popular')) {
                $table->dropColumn('is_popular');
            }
            if (Schema::hasColumn('categories', 'popularity')) {
                $table->dropColumn('popularity');
            }
            if (Schema::hasColumn('categories', 'sales_count')) {
                $table->dropColumn('sales_count');
            }
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
