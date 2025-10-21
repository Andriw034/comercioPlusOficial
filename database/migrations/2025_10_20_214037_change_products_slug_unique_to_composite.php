<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quita el índice único actual sobre 'slug'
            // Asegúrate que el nombre coincide con el de tu BD: 'products_slug_unique'
            $table->dropUnique('products_slug_unique');

            // Crea índice único compuesto por tienda + slug
            $table->unique(['store_id', 'slug'], 'products_store_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revierte al índice único simple en 'slug'
            $table->dropUnique('products_store_id_slug_unique');
            $table->unique('slug', 'products_slug_unique');
        });
    }
};
