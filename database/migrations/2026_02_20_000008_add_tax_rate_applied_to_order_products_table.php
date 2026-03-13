<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_products')) {
            return;
        }

        Schema::table('order_products', function (Blueprint $table) {
            if (! Schema::hasColumn('order_products', 'tax_rate_applied')) {
                $table->decimal('tax_rate_applied', 6, 4)->default(0)->after('tax_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_products')) {
            return;
        }

        Schema::table('order_products', function (Blueprint $table) {
            if (Schema::hasColumn('order_products', 'tax_rate_applied')) {
                $table->dropColumn('tax_rate_applied');
            }
        });
    }
};
