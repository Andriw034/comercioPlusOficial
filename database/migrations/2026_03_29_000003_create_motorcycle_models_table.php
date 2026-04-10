<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorcycle_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->integer('year_from');
            $table->integer('year_to')->nullable();
            $table->string('engine_cc', 10)->nullable();
            $table->enum('type', ['sport', 'touring', 'cruiser', 'scooter', 'enduro', 'naked', 'other'])->nullable();
            $table->timestamps();

            $table->index(['brand', 'model']);
            $table->index('year_from');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorcycle_models');
    }
};
