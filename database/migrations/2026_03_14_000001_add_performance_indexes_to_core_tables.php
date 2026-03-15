<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // stores: index on is_visible for public store listing query (WHERE is_visible = 1)
        Schema::table('stores', function (Blueprint $table) {
            $table->index('is_visible', 'stores_is_visible_index');
        });

        // products: composite (store_id, stock) for public product listing (WHERE stock > 0)
        // and simple index on offer for offer-filter queries
        // Note: store_id+created_at already exists (2026_02_21_000009)
        // Note: store_id+slug unique already exists (products_store_id_slug_unique)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['store_id', 'stock'], 'products_store_id_stock_index');
            $table->index('offer', 'products_offer_index');
        });

        // orders: composite (store_id, status) for merchant order listing filtered by store+status
        // and (store_id, created_at) for time-ordered order lists per store
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['store_id', 'status'], 'orders_store_id_status_index');
            $table->index(['store_id', 'created_at'], 'orders_store_id_created_at_index');
        });

        // inventory_movements: (product_id, created_at) for per-product time-range movement queries
        // Note: store_id+created_at and product_id+type already exist (create_inventory_movements_table)
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['product_id', 'created_at'], 'inventory_movements_product_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_is_visible_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_store_id_stock_index');
            $table->dropIndex('products_offer_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_store_id_status_index');
            $table->dropIndex('orders_store_id_created_at_index');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('inventory_movements_product_id_created_at_index');
        });
    }
};
