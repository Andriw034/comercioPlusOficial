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
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('name'); // Store name
            $table->string('slug')->unique(); // Public URL slug
            $table->string('logo')->nullable(); // Path to logo image
            $table->string('cover')->nullable(); // Path to cover image
            $table->string('background')->nullable(); // Path to background image
            $table->string('primary_color')->default('#FFA14F'); // Main color theme
            $table->text('description')->nullable(); // Store description
            $table->string('direccion'); // Store address
            $table->string('telefono', 20)->nullable(); // Store phone number
            $table->enum('estado', ['activa', 'inactiva'])->default('activa'); // Store status
            $table->string('horario_atencion')->nullable(); // Store opening hours
            $table->string('categoria_principal'); // Main category
            $table->decimal('calificacion_promedio', 3, 2)->default(0); // Average rating (4.80, 4.60, etc.)
            
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
