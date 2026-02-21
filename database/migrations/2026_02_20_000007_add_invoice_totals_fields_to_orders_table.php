<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('store_id');
            }

            if (! Schema::hasColumn('orders', 'tax_total')) {
                $table->decimal('tax_total', 12, 2)->default(0)->after('subtotal');
            }

            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('COP')->after('total');
            }

            if (! Schema::hasColumn('orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('currency');
            }

            if (! Schema::hasColumn('orders', 'invoice_date')) {
                $table->dateTime('invoice_date')->nullable()->after('invoice_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $drop = [];

            foreach (['subtotal', 'tax_total', 'currency', 'invoice_number', 'invoice_date'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $drop[] = $column;
                }
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
