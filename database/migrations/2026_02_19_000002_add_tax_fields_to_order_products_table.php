<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            if (! Schema::hasColumn('order_products', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable()->after('unit_price');
            }
            if (! Schema::hasColumn('order_products', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->nullable()->after('base_price');
            }
            if (! Schema::hasColumn('order_products', 'total_line')) {
                $table->decimal('total_line', 12, 2)->nullable()->after('tax_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('order_products', 'base_price')) {
                $drop[] = 'base_price';
            }
            if (Schema::hasColumn('order_products', 'tax_amount')) {
                $drop[] = 'tax_amount';
            }
            if (Schema::hasColumn('order_products', 'total_line')) {
                $drop[] = 'total_line';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
