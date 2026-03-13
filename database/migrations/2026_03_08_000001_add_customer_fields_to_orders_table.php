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
            $columns = [
                'customer_email'      => fn () => $table->string('customer_email')->nullable()->after('currency'),
                'customer_name'       => fn () => $table->string('customer_name')->nullable()->after('customer_email'),
                'customer_phone'      => fn () => $table->string('customer_phone')->nullable()->after('customer_name'),
                'customer_address'    => fn () => $table->string('customer_address')->nullable()->after('customer_phone'),
                'customer_city'       => fn () => $table->string('customer_city')->nullable()->after('customer_address'),
                'items'               => fn () => $table->json('items')->nullable()->after('customer_city'),
                'customer'            => fn () => $table->json('customer')->nullable()->after('items'),
                'total_amount'        => fn () => $table->decimal('total_amount', 12, 2)->nullable()->after('total'),
                'wompi_data'          => fn () => $table->json('wompi_data')->nullable()->after('payment_data'),
                'payment_approved_at' => fn () => $table->timestamp('payment_approved_at')->nullable()->after('paid_at'),
                'payment_failed_at'   => fn () => $table->timestamp('payment_failed_at')->nullable()->after('payment_approved_at'),
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('orders', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $cols = [
                'customer_email', 'customer_name', 'customer_phone',
                'customer_address', 'customer_city', 'items', 'customer',
                'total_amount', 'wompi_data', 'payment_approved_at', 'payment_failed_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
