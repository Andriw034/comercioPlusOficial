<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# INTEGRADO_DEPLOY_MEDIA

Fecha de consolidacion: 2026-02-25
Base: deploy railway/vercel + cloudinary uploads + checklist produccion.

## 1) Objetivo
Asegurar despliegue estable backend/frontend y flujo de media (logo, cover, producto) en produccion.

## 2) Variables criticas

### Backend (Railway)
- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `FRONTEND_URL` / `VERCEL_PROD_ORIGIN` / CORS
- Variables DB (`DB_*`)
- Cloudinary (`CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, opcional `CLOUDINARY_URL`)

### Frontend (Vercel)
- `VITE_API_BASE_URL`
- `VITE_CLOUDINARY_CLOUD_NAME` (si aplica en UI)
- `VITE_CLOUDINARY_UPLOAD_PRESET` (si aplica en UI)

## 3) Endpoints media
- `POST /api/uploads/products`
- `POST /api/uploads/stores/logo`
- `POST /api/uploads/stores/cover`

## 4) Criterios E2E de aceptacion
1. Uploads responden 200 con token valido.
2. DB persiste URLs de media validas.
3. UI renderiza media en dashboard y publico.
4. Integraciones de salud reportan DB y cloudinary en estado correcto.

## 5) Checklist release
1. `php artisan migrate --force`
2. `php artisan optimize:clear`
3. Verificar `VITE_API_BASE_URL` efectivo en frontend desplegado.
4. Prueba manual de logo, cover e imagen de producto.
5. Validacion SQL de campos `logo_url/cover_url/image_url`.

## 6) Fuentes consolidadas
- `docs/cloudinary-uploads.md`
- `docs/cloudinary-production-checklist.md`
- `docs/deploy-railway-vercel-cloudinary.md`

