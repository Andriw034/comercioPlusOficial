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
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('theme')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();   // Logo de la tienda
            $table->string('cover')->nullable();  // Imagen de portada
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_visible')->default(1);
            $table->timestamps();

            // RelaciÃ³n con usuarios
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
