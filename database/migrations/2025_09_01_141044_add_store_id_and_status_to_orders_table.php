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
        // 1) Asegúrate de no petar si aún no existe 'stores'
        if (!Schema::hasTable('stores') || !Schema::hasTable('orders')) {
            return; // o lanza excepción si prefieres
        }

        Schema::table('orders', function (Blueprint $table) {
            // Agregar store_id solo si no existe
            if (!Schema::hasColumn('orders', 'store_id')) {
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            } else {
                // Si existe store_id pero sin FK, intentamos añadir FK
                // Laravel no tiene helper "if FK exists", así que si llega a fallar, se maneja en down()
                try {
                    $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
                } catch (\Throwable $e) { /* ignora si ya existe */ }
            }

            // Agregar status solo si no existe
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending','processing','completed','cancelled'])
                    ->default('pending')
                    ->after('total');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) return;

        Schema::table('orders', function (Blueprint $table) {
            // Quitar FK si existe y columna
            try {
                $table->dropForeign(['store_id']);
            } catch (\Throwable $e) { /* puede no existir */ }

            if (Schema::hasColumn('orders', 'store_id')) {
                $table->dropColumn('store_id');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
