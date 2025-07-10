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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->string('primary_color')->nullable();
            $table->text('description')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->string('horario_atencion')->nullable();
            $table->string('categoria_principal')->nullable();
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
