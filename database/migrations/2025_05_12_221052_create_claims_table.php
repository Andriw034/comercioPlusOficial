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
        if (!Schema::hasColumn('stores', 'logo_path')) {
            $table->string('logo_path')->nullable()->after('logo');
        }
        if (!Schema::hasColumn('stores', 'cover_path')) {
            $table->string('cover_path')->nullable()->after('logo_path');
        }
        if (!Schema::hasColumn('stores', 'description')) {
            $table->text('description')->nullable()->after('cover_path');
        }
    });

    Schema::table('products', function (Blueprint $table) {
        if (!Schema::hasColumn('products', 'image_path')) {
            $table->string('image_path')->nullable()->after('description');
        }
    });
}

public function down(): void
{
    Schema::table('stores', function (Blueprint $table) {
        $table->dropColumn(['logo_path','cover_path','description']);
    });

    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['image_path']);
    });
}
}; ?>