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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // login, logout, create, update, delete, etc.
            $table->string('model_type')->nullable(); // App\Models\User, App\Models\Product, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID del modelo afectado
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que realizó la acción
            $table->string('user_name')->nullable(); // Nombre del usuario para logs históricos
            $table->json('old_values')->nullable(); // Valores anteriores (para updates)
            $table->json('new_values')->nullable(); // Valores nuevos (para updates/creates)
            $table->string('ip_address')->nullable(); // IP del usuario
            $table->text('user_agent')->nullable(); // User agent del navegador
            $table->string('url')->nullable(); // URL donde ocurrió la acción
            $table->string('method')->nullable(); // GET, POST, PUT, DELETE
            $table->text('description')->nullable(); // Descripción legible de la actividad
            $table->json('metadata')->nullable(); // Información adicional
            $table->timestamps();

            // Índices para mejor performance
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
