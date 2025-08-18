<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePublicStoresTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:public-stores-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the public_stores table manually if it does not exist.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Schema::hasTable('public_stores')) {
            $this->info('❗ La tabla public_stores ya existe.');
            return;
        }

        DB::statement("
            CREATE TABLE public_stores (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                nombre_tienda VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                descripcion TEXT,
                logo VARCHAR(255),
                cover VARCHAR(255),
                direccion VARCHAR(255) NOT NULL,
                telefono VARCHAR(20),
                estado ENUM('activa', 'inactiva') DEFAULT 'activa',
                horario_atencion VARCHAR(255),
                categoria_principal VARCHAR(255) NOT NULL,
                calificacion_promedio DECIMAL(3,2) DEFAULT 0.00,
                store_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                INDEX public_stores_user_id_index (user_id),
                INDEX public_stores_store_id_index (store_id),
                CONSTRAINT fk_public_stores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_public_stores_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->info('✅ Tabla public_stores creada exitosamente.');
    }
}
