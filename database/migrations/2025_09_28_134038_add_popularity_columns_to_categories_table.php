<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * AÃ±ade campos necesarios para soportar la lÃ³gica de categorÃ­as populares:
     * - slug (Ãºnico)
     * - sales_count (contador de ventas)
     * - popularity (score para ordenar)
     * - is_popular (marcado manual)
     * - short_description (descripciÃ³n corta)
     *
     * Nota: si tu tabla ya tiene 'slug' (u otros), puedes comentar la lÃ­nea correspondiente.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // AÃ±adimos columnas sÃ³lo si no existen (protecciÃ³n si vuelves a ejecutar)
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

            // Ãndice compuesto opcional para consultas por popularidad
            // Evitamos crear Ã­ndice duplicado si ya existe
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('categories'));
            } catch (\Throwable $e) {
                $indexes = [];
            }

            if (!in_array('categories_popularity_sales_count_index', $indexes)) {
                $table->index(['popularity', 'sales_count'], 'categories_popularity_sales_count_index_new');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminamos los Ã­ndices antes de columnas si existen
            if (Schema::hasColumn('categories', 'popularity') && Schema::hasColumn('categories', 'sales_count')) {
                // Intentamos eliminar Ã­ndice por nombre; si no existe, no falla
                try {
                    $table->dropIndex('categories_popularity_sales_count_index');
                } catch (\Throwable $e) {
                    // Ã­ndice no existe o DB diferente; ignorar
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
