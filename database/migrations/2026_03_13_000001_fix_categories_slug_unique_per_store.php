<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia el índice único de `categories.slug` de global a compuesto (slug, store_id).
 *
 * El constraint anterior era global, lo que impedía que dos tiendas distintas
 * tuvieran una categoría con el mismo slug (ej. "general" o "sin-categoria").
 * Con este índice compuesto cada comerciante puede tener sus propias categorías
 * sin colisionar con las de otras tiendas.
 *
 * También agrega el índice compuesto (store_id, slug) para acelerar los lookups
 * de findOrCreateCategory durante importaciones masivas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar el unique global en slug.
            $table->dropUnique('categories_slug_unique');

            // Agregar unique compuesto (slug, store_id) — aísla slugs por tienda.
            $table->unique(['slug', 'store_id'], 'categories_slug_store_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_store_id_unique');
            $table->unique('slug', 'categories_slug_unique');
        });
    }
};
