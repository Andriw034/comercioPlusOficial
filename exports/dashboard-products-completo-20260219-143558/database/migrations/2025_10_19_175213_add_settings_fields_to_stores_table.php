<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'phone')) {
                $table->string('phone')->nullable()->after('description');
            }
            if (!Schema::hasColumn('stores', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('stores', 'support_email')) {
                $table->string('support_email')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('stores', 'address')) {
                $table->text('address')->nullable()->after('support_email');
            }
            if (!Schema::hasColumn('stores', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('stores', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('city');
            }
            if (!Schema::hasColumn('stores', 'payment_instructions')) {
                $table->text('payment_instructions')->nullable()->after('is_visible');
            }
            if (!Schema::hasColumn('stores', 'shipping_radius_km')) {
                $table->decimal('shipping_radius_km', 8, 2)->nullable()->after('payment_instructions');
            }
            if (!Schema::hasColumn('stores', 'shipping_base_cost')) {
                $table->decimal('shipping_base_cost', 8, 2)->nullable()->after('shipping_radius_km');
            }
            if (!Schema::hasColumn('stores', 'tax_percent')) {
                $table->decimal('tax_percent', 5, 2)->nullable()->after('shipping_base_cost');
            }
            if (!Schema::hasColumn('stores', 'price_includes_tax')) {
                $table->boolean('price_includes_tax')->default(false)->after('tax_percent');
            }
            if (!Schema::hasColumn('stores', 'notify_email')) {
                $table->boolean('notify_email')->default(true)->after('price_includes_tax');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $columns = [
                'phone', 'whatsapp', 'support_email', 'address', 'city', 'is_visible',
                'payment_instructions', 'shipping_radius_km', 'shipping_base_cost',
                'tax_percent', 'price_includes_tax', 'notify_email'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('stores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
