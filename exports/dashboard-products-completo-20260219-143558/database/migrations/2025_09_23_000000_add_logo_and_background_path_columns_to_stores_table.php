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
            // Add logo_path column if it doesn't exist
            if (!Schema::hasColumn('stores', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('slug');
            }

            // Add background_path column if it doesn't exist
            if (!Schema::hasColumn('stores', 'background_path')) {
                $table->string('background_path')->nullable()->after('logo_path');
            }

            // Add logo_url column if it doesn't exist
            if (!Schema::hasColumn('stores', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('background_path');
            }

            // Add background_url column if it doesn't exist
            if (!Schema::hasColumn('stores', 'background_url')) {
                $table->string('background_url')->nullable()->after('logo_url');
            }

            // Add theme_primary column if it doesn't exist
            if (!Schema::hasColumn('stores', 'theme_primary')) {
                $table->string('theme_primary')->nullable()->after('background_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'background_path', 'logo_url', 'background_url', 'theme_primary']);
        });
    }
};
