<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'facebook')) {
                $table->string('facebook')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('stores', 'instagram')) {
                $table->string('instagram')->nullable()->after('facebook');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'facebook')) {
                $table->dropColumn('facebook');
            }
            if (Schema::hasColumn('stores', 'instagram')) {
                $table->dropColumn('instagram');
            }
        });
    }
};
