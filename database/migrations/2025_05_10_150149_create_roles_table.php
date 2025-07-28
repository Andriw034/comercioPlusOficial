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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
           $table->string('name'); // Nombre para mostrar
            $table->string('slug')->unique(); // Clave corta para lógica (admin, user)
            $table->string('description')->nullable(); // Descripción opcional
            $table->boolean('is_active')->default(true); // Por si algún día desactivas roles
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
