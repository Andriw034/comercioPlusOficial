<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Renombrar columnas antiguas si existen
        Schema::table('stores', function (Blueprint $table) {
            // cover -> cover_image
            if (Schema::hasColumn('stores', 'cover') && ! Schema::hasColumn('stores', 'cover_image')) {
                $table->renameColumn('cover', 'cover_image');
            }

            // background -> background_color
            if (Schema::hasColumn('stores', 'background') && ! Schema::hasColumn('stores', 'background_color')) {
                $table->renameColumn('background', 'background_color');
            }
        });

        // 2) Agregar las que falten (sin duplicar)
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'cover_image')) {
                $table->string('cover_image', 255)->nullable()->after('logo');
            }

            // primary_color YA existe: no lo volvemos a crear.
            // Si quisieras cambiar su default a #FF6000, descomenta lo siguiente (requiere doctrine/dbal):
            /*
            if (Schema::hasColumn('stores', 'primary_color')) {
                $table->string('primary_color', 7)->default('#FF6000')->change();
            }
            */

            if (! Schema::hasColumn('stores', 'background_color')) {
                $table->string('background_color', 7)->default('#f9f9f9')->after('primary_color');
            }

            if (! Schema::hasColumn('stores', 'text_color')) {
                $table->string('text_color', 7)->default('#333333')->after('background_color');
            }

            if (! Schema::hasColumn('stores', 'button_color')) {
                $table->string('button_color', 7)->default('#FF6000')->after('text_color');
            }
        });
    }

    public function down(): void
    {
        // Revertir con seguridad (solo si existen)
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'button_color')) {
                $table->dropColumn('button_color');
            }
            if (Schema::hasColumn('stores', 'text_color')) {
                $table->dropColumn('text_color');
            }
            if (Schema::hasColumn('stores', 'background_color')) {
                // Si originalmente tenías 'background', lo renombramos de vuelta
                // OJO: primero creamos 'background' si no existe, para poder renombrar.
                if (! Schema::hasColumn('stores', 'background')) {
                    $table->string('background', 7)->nullable();
                }
            }
            if (Schema::hasColumn('stores', 'cover_image')) {
                // Si originalmente tenías 'cover', lo renombramos de vuelta
                if (! Schema::hasColumn('stores', 'cover')) {
                    $table->string('cover', 255)->nullable();
                }
            }
        });

        // Renombres inversos (separado para evitar choques)
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'background_color') && Schema::hasColumn('stores', 'background')) {
                $table->renameColumn('background_color', 'background');
            }
            if (Schema::hasColumn('stores', 'cover_image') && Schema::hasColumn('stores', 'cover')) {
                $table->renameColumn('cover_image', 'cover');
            }
        });
    }
};
