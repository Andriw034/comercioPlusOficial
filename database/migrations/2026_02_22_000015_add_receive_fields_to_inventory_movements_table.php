<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_movements', 'reason')) {
                $table->string('reason', 80)->nullable()->after('type');
            }

            if (! Schema::hasColumn('inventory_movements', 'reference')) {
                $table->string('reference', 191)->nullable()->after('reference_id');
            }

            if (! Schema::hasColumn('inventory_movements', 'request_id')) {
                $table->string('request_id', 120)->nullable()->after('reference');
                $table->unique('request_id', 'inventory_movements_request_id_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_movements', 'request_id')) {
                $table->dropUnique('inventory_movements_request_id_unique');
                $table->dropColumn('request_id');
            }

            if (Schema::hasColumn('inventory_movements', 'reference')) {
                $table->dropColumn('reference');
            }

            if (Schema::hasColumn('inventory_movements', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};

