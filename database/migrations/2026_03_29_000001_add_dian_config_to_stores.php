<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('dian_enabled')->default(false)->after('taxes_enabled');
            $table->string('dian_nit', 20)->nullable()->after('dian_enabled');
            $table->string('dian_business_name')->nullable()->after('dian_nit');
            $table->enum('dian_provider', ['saphety', 'carvajal', 'factory', 'alegra', 'other'])->nullable()->after('dian_business_name');
            $table->text('dian_api_credentials')->nullable()->after('dian_provider');
            $table->timestamp('dian_enabled_at')->nullable()->after('dian_api_credentials');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'dian_enabled',
                'dian_nit',
                'dian_business_name',
                'dian_provider',
                'dian_api_credentials',
                'dian_enabled_at',
            ]);
        });
    }
};
