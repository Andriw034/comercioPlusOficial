<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->unique()->after('slug');
        });

        // Seed existing stores with subdomain = slug
        DB::table('stores')->whereNull('subdomain')->whereNotNull('slug')
            ->update(['subdomain' => DB::raw('slug')]);
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('subdomain');
        });
    }
};
