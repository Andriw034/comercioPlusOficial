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
