<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'cover_image') && Schema::hasColumn('stores', 'cover')) {
                $table->renameColumn('cover', 'cover_image');
            }
            if (!Schema::hasColumn('stores', 'background_color') && Schema::hasColumn('stores', 'background')) {
                $table->renameColumn('background', 'background_color');
            }
            if (!Schema::hasColumn('stores', 'text_color')) {
                $table->string('text_color')->nullable();
            }
            if (!Schema::hasColumn('stores', 'button_color')) {
                $table->string('button_color')->nullable();
            }
            if (!Schema::hasColumn('stores', 'primary_color')) {
                $table->string('primary_color')->default('#FF6000');
            }
            if (Schema::hasColumn('stores', 'descripcion') && !Schema::hasColumn('stores', 'description')) {
                $table->string('description')->nullable();
                // Optionally, you can copy data from 'descripcion' to 'description' here using DB::statement
            }
        });
    }

    public function down(): void
    {
        // Optional: revert column renames if needed
    }
};
