<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * AÃ±ade store_id (foreign key) a la tabla categories y crea un Ã­ndice Ãºnico compuesto (store_id, slug)
     * para permitir slugs iguales en diferentes tiendas. Es seguro si la tabla ya tiene datos.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // AÃ±adir store_id si no existe
            if (!Schema::hasColumn('categories', 'store_id')) {
                // nullable() permite categorÃ­as globales (null = global)
                $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete()->after('id');
            }

            // AÃ±adir slug si no existe (no obligamos si ya existe)
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->after('name')->nullable();
            }

            // Crear Ã­ndice Ãºnico compuesto store_id + slug (si no existe)
            // Usamos try/catch para evitar fallos en distintos motores/estado de BD
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('categories'));
            } catch (\Throwable $e) {
                $indexes = [];
            }

            if (!in_array('categories_store_slug_unique', $indexes)) {
                // Si slug es nullable, MySQL permite mÃºltiples NULL; el Ã­ndice Ãºnico funcionarÃ¡ por store_id+slug
                $table->unique(['store_id', 'slug'], 'categories_store_slug_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar Ã­ndice compuesto si existe
            try {
                $table->dropUnique('categories_store_slug_unique');
            } catch (\Throwable $e) {
                // ignorar si no existe
            }

            // Eliminar columna slug si fue creada por esta migration
            if (Schema::hasColumn('categories', 'slug')) {
                try {
                    $table->dropColumn('slug');
                } catch (\Throwable $e) {
                    // ignorar si no se puede
                }
            }

            // Eliminar foreign key y columna store_id si existen
            if (Schema::hasColumn('categories', 'store_id')) {
                try {
                    $table->dropForeign(['store_id']);
                } catch (\Throwable $e) {
                    // ignorar si no existe FK
                }

                try {
                    $table->dropColumn('store_id');
                } catch (\Throwable $e) {
                    // ignorar si no se puede
                }
            }
        });
    }
};
