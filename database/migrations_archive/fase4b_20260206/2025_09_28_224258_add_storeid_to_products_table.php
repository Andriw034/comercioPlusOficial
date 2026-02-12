<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // AÃ±adir store_id si no existe
            if (! Schema::hasColumn('products', 'store_id')) {
                // Nullable para no romper creaciones previas; null = producto sin tienda (si las hay)
                $table->foreignId('store_id')->nullable()->after('user_id')
                      ->constrained('stores')->nullOnDelete();
            }

            // Si no existe Ã­ndice sobre store_id, crearlo
            if (! Schema::hasColumn('products', 'store_id')) {
                // la condiciÃ³n anterior ya aÃ±adiÃ³ la columna; aquÃ­ por seguridad intentamos index
                try {
                    $table->index('store_id', 'products_store_id_index');
                } catch (\Throwable $e) {
                    // ignorar si el Ã­ndice ya existe o DB no lo soporta de la misma forma
                }
            } else {
                // Si la columna ya existÃ­a (raro), aseguramos el Ã­ndice por nombre si no existe
                try {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('products'));
                } catch (\Throwable $e) {
                    $indexes = [];
                }
                if (! in_array('products_store_id_index', $indexes)) {
                    try {
                        $table->index('store_id', 'products_store_id_index');
                    } catch (\Throwable $e) {
                        // ignorar
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Eliminar Ã­ndice si existe
            try {
                $table->dropIndex('products_store_id_index');
            } catch (\Throwable $e) {
                // ignorar si no existe
            }

            // Intentar eliminar FK y columna store_id si existe
            if (Schema::hasColumn('products', 'store_id')) {
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
