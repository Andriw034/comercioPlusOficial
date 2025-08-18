<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('store_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('primary_color')->default('#3b82f6');
            $table->string('secondary_color')->default('#64748b');
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#1e293b');
            $table->string('font_family')->default('Inter, sans-serif');
            $table->text('custom_css')->nullable();
            $table->string('background_image')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['store_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_themes');
    }
};
