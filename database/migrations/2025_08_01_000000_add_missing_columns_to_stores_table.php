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
            // Verificar si la columna 'background' existe antes de agregarla
            if (!Schema::hasColumn('stores', 'background')) {
                $table->string('background')->nullable()->after('cover');
            }
            
            // Verificar si la columna 'primary_color' existe antes de agregarla
            if (!Schema::hasColumn('stores', 'primary_color')) {
                $table->string('primary_color')->default('#FFA14F')->after('background');
            }
            
            // Verificar si la columna 'description' existe antes de agregarla
            if (!Schema::hasColumn('stores', 'description')) {
                $table->text('description')->nullable()->after('primary_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['background', 'primary_color', 'description']);
        });
    }
};
