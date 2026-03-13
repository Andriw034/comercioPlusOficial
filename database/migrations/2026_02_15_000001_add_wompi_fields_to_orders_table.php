<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'wompi_transaction_id')) {
                $table->string('wompi_transaction_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_data')) {
                $table->json('payment_data')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_error')) {
                $table->text('payment_error')->nullable();
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'payment_reference',
                'wompi_transaction_id',
                'payment_status',
                'payment_data',
                'payment_error',
                'paid_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

