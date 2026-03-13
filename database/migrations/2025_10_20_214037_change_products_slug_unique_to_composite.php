<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quita el Ã­ndice Ãºnico actual sobre 'slug'
            // AsegÃºrate que el nombre coincide con el de tu BD: 'products_slug_unique'
            $table->dropUnique('products_slug_unique');

            // Crea Ã­ndice Ãºnico compuesto por tienda + slug
            $table->unique(['store_id', 'slug'], 'products_store_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revierte al Ã­ndice Ãºnico simple en 'slug'
            $table->dropUnique('products_store_id_slug_unique');
            $table->unique('slug', 'products_slug_unique');
        });
    }
};
