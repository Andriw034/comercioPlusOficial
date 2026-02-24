<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_products')) {
            return;
        }

        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'qty_picked')) {
                $table->unsignedInteger('qty_picked')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('order_products', 'qty_packed')) {
                $table->unsignedInteger('qty_packed')->default(0)->after('qty_picked');
            }
            if (!Schema::hasColumn('order_products', 'qty_missing')) {
                $table->unsignedInteger('qty_missing')->default(0)->after('qty_packed');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_products')) {
            return;
        }

        Schema::table('order_products', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('order_products', 'qty_picked')) {
                $dropColumns[] = 'qty_picked';
            }
            if (Schema::hasColumn('order_products', 'qty_packed')) {
                $dropColumns[] = 'qty_packed';
            }
            if (Schema::hasColumn('order_products', 'qty_missing')) {
                $dropColumns[] = 'qty_missing';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

