<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si ya existe 'descripcion' no hacemos nada:
        if (Schema::hasColumn('stores', 'descripcion')) {
            return;
        }

        // Si existe 'description', renombramos a 'descripcion'
        if (Schema::hasColumn('stores', 'description')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->renameColumn('description', 'descripcion');
            });
        } else {
            // Si no existe ninguna, creamos 'descripcion'
            Schema::table('stores', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        // Si quieres revertir al nombre original:
        if (Schema::hasColumn('stores', 'descripcion') && !Schema::hasColumn('stores', 'description')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->renameColumn('descripcion', 'description');
            });
        }
    }
};
