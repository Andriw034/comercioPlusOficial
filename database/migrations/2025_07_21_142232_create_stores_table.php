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
            $table->string('primary_color')->default('#FFA14F'); // Main color theme
            $table->text('description')->nullable(); // Store description
 


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
