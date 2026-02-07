# MIGRATIONS_NOTES.md

## Fase 4B - migrate:fresh + schema limpio

Fecha de ejecución: 2026-02-06
Proyecto: ComercioPlus (`c:\xampp\htdocs\comercioPlusOficial`)

## 1) Verificación de entorno (NO producción)

Comandos ejecutados:
- `php artisan env`
- `rg -n "^APP_ENV=|^DB_CONNECTION=|^DB_HOST=|^DB_PORT=|^DB_DATABASE=" .env`
- Script PHP runtime (`app()->environment()`, `config('database.*')`)

Evidencia:
- `APP_ENV=local`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=comercioplus`
- `artisan env => The application environment is [local]`

Conclusión: ejecución realizada en entorno local, no producción.

## 2) Backup obligatorio previo

Comando ejecutado:
- `C:\xampp\mysql\bin\mysqldump.exe --host=127.0.0.1 --port=3306 --user=root --default-character-set=utf8mb4 --routines --events --triggers --single-transaction --databases comercioplus --result-file=...`

Archivo generado:
- `backups/db/comercioplus_pre_fase4b_20260206_135559.sql`

Estado:
- Backup SQL completo creado correctamente antes de `migrate:fresh`.

## 3) Migraciones duplicadas: canónicas y archivado

### 3.1 Migraciones identificadas como duplicadas/conflictivas
- `2024_01_01_000003_create_categories_table.php`
- `2024_01_01_000004_create_stores_table.php`
- `2024_01_01_000005_create_products_table.php`
- `2025_09_03_030000_fix_orders_table_columns.php`
- `2025_09_28_134038_add_popularity_columns_to_categories_table.php`
- `2025_09_28_135458_add_storeid_to_categories_table.php`
- `2025_09_28_224258_add_storeid_to_products_table.php`

### 3.2 Archivado físico
Ruta de archivo:
- `database/migrations_archive/fase4b_20260206/`

Nota operativa:
- El entorno denegó `Move-Item`/`rename`/`delete` en esos archivos (`Access denied`).
- Se aplicó archivado por copia al directorio anterior.
- Para evitar doble ejecución en `migrate:fresh`, los archivos originales se dejaron como migraciones no-op (stub) con referencia de archivado.

### 3.3 Ajuste técnico adicional para permitir fresh limpio
Archivo modificado:
- `database/migrations/2025_05_12_220900_create_orders_table.php`

Cambio aplicado:
- `store_id` dejó de crear FK temprana contra `stores` (que aún no existía en ese punto del orden).
- `status` default unificado a `pending`.

Motivo:
- Corregir error real de `migrate:fresh`: FK `orders_store_id_foreign` incorrectamente formada por orden de creación de tablas.

## 4) Resultado de `php artisan migrate:fresh`

- Primer intento: falló por FK en `orders.store_id`.
- Segundo intento (tras ajuste de migración): **OK**.
- Estado final: todas las migraciones en batch `[1] Ran`.

Comandos de validación:
- `php artisan migrate:fresh`
- `php artisan migrate:status`

## 5) Seed mínimo productivo ejecutado

### 5.1 Seeder creado
- `database/seeders/ProductionMinimalSeeder.php`

Contenido funcional:
- Roles base (tabla roles / Spatie-compatible): `admin`, `comerciante`, `cliente`
- Permisos base: `manage products`, `manage categories`, `manage stores`, `manage orders`, `manage users`, `view dashboard`
- Asignación permisos por rol (admin completo, comerciante operativo, cliente mínimo)
- Usuario admin base:
  - email: `admin@comercioplus.local`
  - password: `Admin12345!` (cambiar inmediatamente fuera de entorno local)
  - role string: `merchant`
  - rol Spatie asignado: `admin`
- Categorías base de catálogo (6 slugs usados por API pública)

### 5.2 Seeder registrado
- `database/seeders/DatabaseSeeder.php` actualizado para llamar `ProductionMinimalSeeder::class`

### 5.3 Comando ejecutado
- `php artisan db:seed`

Resultado:
- `ProductionMinimalSeeder ... DONE`

## 6) Validación de rutas API

Comando ejecutado:
- `php artisan route:list --path=api --except-vendor`

Resultado:
- Rutas API cargadas correctamente (44 rutas mostradas).

## 7) Smoke tests API críticos

Se levantó servidor temporal (`php artisan serve`) y se probaron:
- `GET /api/health`
- `POST /api/register`
- `POST /api/login`
- `GET /api/public-stores`
- `GET /api/products`
- `GET /api/categories`

Resultados finales (última corrida):
- `/api/health` -> `200`
- `/api/register` -> `201`
- `/api/login` -> `200`
- `/api/public-stores` -> `200`
- `/api/products` -> `200`
- `/api/categories` -> `200`

Chequeo de error de esquema:
- Búsqueda explícita de `Unknown column` en respuestas smoke: **no detectado**.

Nota de validación:
- `register` usa regla `email:rfc,dns`; correos sin DNS válido retornan `422` (comportamiento esperado).

## 8) Riesgos detectados tras Fase 4B

1. Archivos duplicados quedaron como stubs no-op (por restricción del FS para mover/borrar).
   - Riesgo: deuda técnica documental si no se elimina físicamente más adelante.
2. Existen muchas migraciones históricas no esenciales aún presentes.
   - Riesgo: complejidad de mantenimiento.
3. Password admin inicial en seeder es conocida.
   - Riesgo: seguridad si se reutiliza fuera de entorno local.

## 9) Próximos pasos inmediatos recomendados

1. Confirmar funcionalidad E2E de flujos críticos con DB limpia:
   - registro/login
   - merchant: crear tienda + CRUD productos
   - cliente: catálogo + carrito + checkout MVP
2. Reemplazar password admin inicial por secreto de entorno (`env`) o rotación post-seed.
3. En una ventana de mantenimiento de repo, resolver permisos de FS y mover/eliminar físicamente las migraciones duplicadas ya archivadas.
4. Continuar Fase 5 (estabilización contractual de API y documentación `API.md`).

## 10) Revalidación smoke posterior (2026-02-06)

Se ejecutó una segunda validación smoke iniciando servidor temporal en `127.0.0.1:8020`.

Resultados:
- `GET /api/health` -> `200`
- `POST /api/register` -> `200`
- `POST /api/login` -> `200`
- `GET /api/public-stores` -> `200`
- `GET /api/products` -> `200`
- `GET /api/categories` -> `200`

Chequeo de esquema en respuestas:
- búsqueda de `Unknown column` en payload de cada endpoint smoke: **no detectado**.
