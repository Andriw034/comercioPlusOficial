<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Agregar columnas si no existen (evita choques si ya estaban)
            if (! Schema::hasColumn('stores', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('primary_color');
            }
            if (! Schema::hasColumn('stores', 'cover_path')) {
                $table->string('cover_path')->nullable()->after('logo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
            if (Schema::hasColumn('stores', 'cover_path')) {
                $table->dropColumn('cover_path');
            }
        });
    }
};
