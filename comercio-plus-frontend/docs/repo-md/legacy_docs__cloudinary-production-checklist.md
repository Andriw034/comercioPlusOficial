<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Cloudinary Produccion - Checklist E2E

Fecha: 2026-02-23

## Objetivo
Validar que las subidas de media (logo, portada, imagen de producto) funcionen end-to-end en produccion y queden persistidas en DB con URL publica renderizable.

## 1) Variables obligatorias

### Backend (Railway)
Configurar en servicio Laravel/API:
- `CLOUDINARY_CLOUD_NAME`
- `CLOUDINARY_API_KEY`
- `CLOUDINARY_API_SECRET`
- `CLOUDINARY_UPLOAD_PRESET` (opcional si tu flujo no lo exige)
- `CLOUDINARY_URL` (alternativa al bloque de 3 credenciales)

Variables adicionales recomendadas:
- `APP_URL` (URL publica del backend)
- `FILESYSTEM_DISK=public`

Fuente de lectura de credenciales:
- `config/services.php`
- `app/Services/CloudinaryService.php`

### Frontend (Vercel)
- `VITE_API_BASE_URL` (apuntando al backend Railway, terminado en `/api`)
- `VITE_CLOUDINARY_CLOUD_NAME` (si UI lo consume)
- `VITE_CLOUDINARY_UPLOAD_PRESET` (si UI lo consume)

## 2) Endpoints involucrados
- `POST /api/uploads/stores/logo`
- `POST /api/uploads/stores/cover`
- `POST /api/uploads/products`

Controlador:
- `app/Http/Controllers/Api/UploadController.php`

## 3) Prueba E2E manual (produccion)

### Caso A - Logo tienda
1. Login merchant.
2. Ir a `/dashboard/store`.
3. Subir logo.
4. Guardar.
5. Verificar:
- Sidebar muestra logo actualizado.
- En DB `stores.logo_url` no es null y apunta a Cloudinary (`https://res.cloudinary.com/...`).

### Caso B - Portada tienda
1. En `/dashboard/store`, subir portada.
2. Guardar.
3. Verificar:
- Sidebar/header usa portada.
- En DB `stores.cover_url` y/o `stores.background_url` guardados con URL Cloudinary.

### Caso C - Imagen producto
1. Ir a `/dashboard/products/create`.
2. Subir imagen y guardar producto.
3. Verificar:
- Imagen renderiza en lista y vista publica.
- En DB `products.image_url` (o campo equivalente) con URL Cloudinary.

## 4) SQL de verificacion recomendada
```sql
SELECT id, name, logo_url, cover_url, background_url
FROM stores
ORDER BY id DESC
LIMIT 5;

SELECT id, name, image_url, image_path
FROM products
ORDER BY id DESC
LIMIT 10;
```

## 5) Criterios de aceptacion
- Los 3 uploads responden 200.
- DB persiste URLs validas Cloudinary.
- UI dashboard y publico renderizan correctamente.
- No hay fallback a rutas locales (`/storage/...`) en produccion Cloudinary.

## 6) Fallas comunes
- Variables Cloudinary incompletas en Railway.
- `VITE_API_BASE_URL` apuntando mal desde Vercel.
- CORS backend bloqueando uploads.
- Limites/tipos de archivo invalidos (max 5MB, mime no permitido).

