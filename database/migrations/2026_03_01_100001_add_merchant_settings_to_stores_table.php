<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'category')) {
                $table->string('category', 120)->nullable()->after('description');
            }
            if (! Schema::hasColumn('stores', 'schedule')) {
                $table->text('schedule')->nullable();
            }
            if (! Schema::hasColumn('stores', 'currency')) {
                $table->string('currency', 10)->default('COP');
            }
            if (! Schema::hasColumn('stores', 'taxes_enabled')) {
                $table->boolean('taxes_enabled')->default(false);
            }
            if (! Schema::hasColumn('stores', 'payment_methods')) {
                $table->json('payment_methods')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            foreach (['category', 'schedule', 'currency', 'taxes_enabled', 'payment_methods'] as $col) {
                if (Schema::hasColumn('stores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
