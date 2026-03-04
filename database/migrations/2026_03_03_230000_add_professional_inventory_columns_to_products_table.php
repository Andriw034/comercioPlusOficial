<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 50)->nullable()->after('stock');
            }

            if (!Schema::hasColumn('products', 'ref_adicional')) {
                $table->string('ref_adicional', 191)->nullable()->after('unit');
            }

            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->nullable()->after('cost_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $drop = [];
            foreach (['unit', 'ref_adicional', 'sale_price'] as $column) {
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

