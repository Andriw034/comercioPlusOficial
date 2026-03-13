<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('products', 'reorder_point')) {
                $table->unsignedInteger('reorder_point')->default(5)->after('stock');
            }
            if (! Schema::hasColumn('products', 'allow_backorder')) {
                $table->boolean('allow_backorder')->default(false)->after('reorder_point');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('products', 'cost_price')) {
                $drop[] = 'cost_price';
            }
            if (Schema::hasColumn('products', 'reorder_point')) {
                $drop[] = 'reorder_point';
            }
            if (Schema::hasColumn('products', 'allow_backorder')) {
                $drop[] = 'allow_backorder';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
