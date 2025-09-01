<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionAndCoverImageToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('stores', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('logo');
            }
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('stores', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });
    }
}