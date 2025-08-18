<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('stores')) {
            if (DB::getDriverName() !== 'sqlite') {
                $primaryKeyExists = DB::select("SHOW KEYS FROM stores WHERE Key_name = 'PRIMARY'");
                if (empty($primaryKeyExists)) {
                    DB::statement('ALTER TABLE stores ADD PRIMARY KEY (id)');
                }
                DB::statement('ALTER TABLE stores MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stores')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE stores MODIFY id BIGINT UNSIGNED');
                $primaryKeyExists = DB::select("SHOW KEYS FROM stores WHERE Key_name = 'PRIMARY'");
                if (!empty($primaryKeyExists)) {
                    DB::statement('ALTER TABLE stores DROP PRIMARY KEY');
                }
            }
        }
    }
};
