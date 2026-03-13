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
        Schema::create('public_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->string('nombre_tienda');
            $table->string('slug')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->string('direccion');
            $table->string('telefono', 20)->nullable();
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->string('horario_atencion')->nullable();
            $table->string('categoria_principal');
            $table->decimal('calificacion_promedio', 3, 2)->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_stores');
    }
};
