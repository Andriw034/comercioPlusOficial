<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Deploy final: Railway + Vercel + Cloudinary

## 1) Variables en Railway (backend Laravel)

Configura estas variables en el servicio backend de Railway:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://comercioplusoficial-production.up.railway.app`
- `FRONTEND_URL=https://comercio-plus-oficial.vercel.app`
- `VERCEL_PROD_ORIGIN=https://comercio-plus-oficial.vercel.app`
- `CORS_ALLOWED_ORIGINS=https://comercio-plus-oficial.vercel.app`
- `DB_CONNECTION=mysql`
- `DB_HOST=...` (Railway MySQL host)
- `DB_PORT=...`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `FILESYSTEM_DISK=public`
- `CLOUDINARY_CLOUD_NAME=...`
- `CLOUDINARY_API_KEY=...`
- `CLOUDINARY_API_SECRET=...`
- `CLOUDINARY_UPLOAD_PRESET=...` (opcional para frontend signed/unsigned)

Luego ejecuta en Railway:

```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan storage:link
```

## 2) Variables en Vercel (frontend React/Vite)

Configura estas variables en el proyecto frontend:

- `VITE_API_BASE_URL=/api`
- `VITE_FORCE_THEME=light`
- `VITE_APP_NAME=ComercioPlus`
- `VITE_APP_VERSION=1.0.0`
- `VITE_ENABLE_DARK_MODE=true`
- `VITE_ENABLE_SOCIAL_LOGIN=false`
- `VITE_CLOUDINARY_CLOUD_NAME=...` (si se usa upload directo frontend)
- `VITE_CLOUDINARY_UPLOAD_PRESET=...` (si se usa upload directo frontend)

Notas:

- `vercel.json` ya reescribe `/api/*` y `/sanctum/*` hacia Railway.
- `vercel.json` ya reescribe `/storage/*` hacia Railway para media local legacy.

## 3) Cloudinary (obligatorio producciÃ³n)

En Cloudinary:

1. Crea un Upload Preset.
2. Habilita formato imagen y tamaÃ±o permitido.
3. Copia `cloud_name`, `api_key`, `api_secret`.

En backend, `CloudinaryService` usa:

- `services.cloudinary.cloud_name`
- `services.cloudinary.api_key`
- `services.cloudinary.api_secret`

Si estas 3 faltan, hace fallback a disco local.

## 4) ValidaciÃ³n rÃ¡pida despuÃ©s de deploy

### Backend health

```bash
GET /api/health
GET /api/health/integrations
```

`/api/health/integrations` debe devolver:

- `database.ok=true`
- `cloudinary.configured=true`

### Flujo funcional mÃ­nimo

1. Login merchant.
2. Crear/editar tienda con logo y portada.
3. Crear producto con imagen.
4. Ver dashboard y pedidos.
5. Confirmar que imagen carga en Vercel y local.

## 5) Evitar desalineaciÃ³n local vs producciÃ³n

Para cambiar entorno local rÃ¡pido:

```powershell
.\switch-env.ps1 local
.\switch-env.ps1 railway
```

El script actualiza:

- Backend: `.env`
- Frontend: `comercio-plus-frontend/.env.local`

Y limpia config de Laravel.

Despues de cambiar entorno, reinicia procesos:

```bash
# Backend
php artisan serve --host=127.0.0.1 --port=8000

# Frontend
cd comercio-plus-frontend
npm run dev
```

