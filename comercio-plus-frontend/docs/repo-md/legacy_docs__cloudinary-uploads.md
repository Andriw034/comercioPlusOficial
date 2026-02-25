<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Cloudinary Uploads (Laravel + React)

## Variables de entorno

### Railway (backend Laravel)

Completa estas variables en Railway:

```env
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
VERCEL_PROD_ORIGIN=https://comercio-plus-oficial.vercel.app
```

Tambien puedes usar `FRONTEND_URL` con tu dominio de Vercel.

### Vercel (frontend React + Vite)

```env
VITE_API_BASE_URL=https://TU_BACKEND_RAILWAY_URL/api
```

## Endpoints de upload

Todos requieren `Authorization: Bearer <token>`:

- `POST /api/uploads/products`
- `POST /api/uploads/stores/logo`
- `POST /api/uploads/stores/cover`
- `POST /api/uploads/profiles/photo`

Request: `multipart/form-data` con campo `image`.

Validaciones:

- tipos: `image/jpeg`, `image/png`, `image/webp`, `image/avif`
- maximo: `5MB`

Respuesta:

```json
{
  "data": {
    "url": "https://res.cloudinary.com/...",
    "public_id": "comercio-plus/products/...",
    "width": 1200,
    "height": 800
  },
  "message": "Uploaded"
}
```

## Comandos utiles

```bash
php artisan optimize:clear
php artisan route:list --path=api/uploads
php artisan test
```

Frontend:

```bash
cd comercio-plus-frontend
npm run build
```

## Prueba rapida con curl

```bash
curl -i -X POST "https://TU_BACKEND_RAILWAY_URL/api/uploads/products" \
  -H "Authorization: Bearer TU_TOKEN" \
  -F "image=@/ruta/imagen.jpg"
```


