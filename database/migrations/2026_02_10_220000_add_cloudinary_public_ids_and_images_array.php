<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'image_public_id')) {
                $table->string('image_public_id')->nullable()->after('image_url');
            }

            if (!Schema::hasColumn('products', 'image_urls')) {
                $table->json('image_urls')->nullable()->after('image_public_id');
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'logo_public_id')) {
                $table->string('logo_public_id')->nullable()->after('logo_url');
            }

            if (!Schema::hasColumn('stores', 'cover_public_id')) {
                $table->string('cover_public_id')->nullable()->after('cover_url');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('users', 'avatar_public_id')) {
                $table->string('avatar_public_id')->nullable()->after('avatar_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'image_urls')) {
                $table->dropColumn('image_urls');
            }

            if (Schema::hasColumn('products', 'image_public_id')) {
                $table->dropColumn('image_public_id');
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'cover_public_id')) {
                $table->dropColumn('cover_public_id');
            }

            if (Schema::hasColumn('stores', 'logo_public_id')) {
                $table->dropColumn('logo_public_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_public_id')) {
                $table->dropColumn('avatar_public_id');
            }

            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->dropColumn('avatar_url');
            }
        });
    }
};
