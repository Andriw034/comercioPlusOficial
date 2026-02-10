# ComercioPlus

ComercioPlus es una plataforma e-commerce para repuestos de moto con:
- Backend API: Laravel 11 + Sanctum (Bearer tokens)
- Frontend oficial: React + Vite en `comercio-plus-frontend/`

## Frontend oficial

Toda nueva funcionalidad de UI se implementa en `comercio-plus-frontend/`.

Comandos desde la raiz:
- `npm run dev` inicia el frontend React oficial
- `npm run build` construye el frontend React oficial
- `npm run lint` ejecuta lint del frontend React oficial

## Stack legacy (archivado)

El stack Laravel + Vue/Blade queda en modo legacy y no es fuente de verdad para nuevas features.

- Config legacy Vite: `vite.legacy.config.js`
- Comando legacy dev: `npm run dev:legacy`
- Comando legacy build: `npm run build:legacy`

## Backend API

### Requisitos
- PHP 8.2+
- Composer
- MySQL (o motor configurado en `.env`)

### Setup
1. `composer install`
2. `cp .env.example .env`
3. Configurar DB en `.env`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan serve`

### Deploy Vercel (frontend) + Railway (backend)

Checklist rapido para evitar `HTTP 503` en `/api/login` y `/api/register`:

1. En Vercel define `VITE_API_BASE_URL` con URL absoluta al backend (`https://<tu-backend>.up.railway.app/api`).
2. En Railway valida variables de DB (`DATABASE_URL` o `MYSQL*`/`PG*`).
3. Verifica extensiones PDO del contenedor:
   - MySQL: `pdo_mysql`
   - PostgreSQL: `pdo_pgsql`
4. Confirma migraciones aplicadas (`php artisan migrate --force`).
5. Revisa `GET /api/health`:
   - `db_ok: true`
   - `env_hints.pdo_mysql_loaded` / `env_hints.pdo_pgsql_loaded` segun el motor.

Notas para Railway:
- Si en logs solo ves arranque de MySQL (`Entrypoint script for MySQL Server ...`) y luego `Stopping Container`, estas viendo el servicio de base de datos, no necesariamente el servicio backend.
- El servicio backend debe arrancar con el script `./docker/railway-start.sh` (incluye espera de DB + reintento de migraciones).
- Variables opcionales de arranque backend:
  - `DB_WAIT_MAX_ATTEMPTS` (default `20`)
  - `DB_WAIT_SLEEP_SECONDS` (default `3`)
  - `MIGRATE_MAX_ATTEMPTS` (default `5`)
  - `MIGRATE_SLEEP_SECONDS` (default `4`)


Solucion rapida cuando `/api/health` muestra `db_ok=false` con host `127.0.0.1` y DB `forge`:
- En el servicio backend de Railway agrega (minimo):
  - `MYSQLHOST`
  - `MYSQLPORT`
  - `MYSQLDATABASE`
  - `MYSQLUSER`
  - `MYSQLPASSWORD`
- Alternativamente usa una sola `DATABASE_URL`.
- Redeploy del backend luego de guardar variables.


### Cloudinary (imagenes de productos, tienda y avatar)

Variables del backend (Railway):
- `CLOUDINARY_CLOUD_NAME`
- `CLOUDINARY_API_KEY`
- `CLOUDINARY_API_SECRET`
- Opcional: `CLOUDINARY_FOLDER_BASE=comercioplus`

> Seguridad: no expongas `CLOUDINARY_API_SECRET` en frontend/Vercel. El frontend solo envia archivos al backend y renderiza `secure_url`.

Comandos:
- `composer install`
- `php artisan migrate --force`
- `npm run build` (frontend)

Endpoints nuevos:
- `POST /api/products/{product}/image` (multipart: `image`)
- `POST /api/stores/{store}/logo` (multipart: `logo`)
- `POST /api/stores/{store}/cover` (multipart: `cover`)
- `POST /api/users/{user}/avatar` (multipart: `avatar`)

`POST /api/products` y `PUT /api/products/{id}` aceptan:
- `image` (principal)
- `images[]` (galeria)

Rutas Cloudinary usadas:
- `comercioplus/stores/{store_id}/logo`
- `comercioplus/stores/{store_id}/cover`
- `comercioplus/stores/{store_id}/products/{product_id}`
- `comercioplus/users/{user_id}/avatar`

## Notas de arquitectura

- Autenticacion oficial: Sanctum por Bearer token para API.
- Roles/permisos: Spatie Permission es la fuente de verdad.
- `users.role` se conserva como shortcut operativo para frontend.

## Pruebas

- API (Laravel): `php artisan test`
- Lint frontend: `npm run lint`

## Estado actual de build en este entorno

El comando `npm run build` puede fallar en sandbox por `spawn EPERM` de `esbuild`.
En un entorno sin restriccion de sandbox, la build debe ejecutarse normalmente.
