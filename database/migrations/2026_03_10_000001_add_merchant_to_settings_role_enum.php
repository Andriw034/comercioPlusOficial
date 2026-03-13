<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE settings MODIFY COLUMN role ENUM('admin','user','all','merchant') NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("UPDATE settings SET role = 'all' WHERE role = 'merchant'");
        DB::statement("ALTER TABLE settings MODIFY COLUMN role ENUM('admin','user','all') NOT NULL DEFAULT 'all'");
    }
};
