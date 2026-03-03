<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 191)->nullable()->after('name');
            }

            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand', 191)->nullable()->after('sku');
            }

            if (!Schema::hasColumn('products', 'metadata')) {
                $after = Schema::hasColumn('products', 'image_url') ? 'image_url' : 'image';
                $table->json('metadata')->nullable()->after($after);
            }
        });

        if (Schema::hasColumn('products', 'sku') && Schema::hasColumn('products', 'store_id')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->index(['store_id', 'sku'], 'products_store_sku_idx');
                } catch (\Throwable $e) {
                    // Ignore duplicated index creation on already patched environments.
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropIndex('products_store_sku_idx');
            } catch (\Throwable $e) {
                // Ignore missing index on rollback.
            }

            $drop = [];
            foreach (['sku', 'brand', 'metadata'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $drop[] = $column;
                }
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
