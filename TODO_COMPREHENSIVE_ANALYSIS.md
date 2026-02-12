# ComercioPlus - TODO Comprehensive Analysis

## Executive Summary
- Se auditó backend Laravel (CORS, rutas API, middleware, arranque Docker) y frontend React (tema, guards, consumo API).
- CORS tenía un riesgo principal: uso de wildcard en `allowed_origins` (`https://*.vercel.app`), que no es la forma correcta para previews Vercel.
- Se confirmó `HandleCors` en middleware global y `supports_credentials=true`.
- Se confirmó que en código existen `POST /api/categories` y `GET /api/my/store` con contrato coherente (404 JSON cuando no hay tienda).
- El 405 observado en producción para `POST /api/categories` no proviene del repo actual; es señal de deploy desactualizado o cache en Railway.
- Se corrigió tema login/register para garantizar contraste en light/dark en campos y placeholders.
- Se redujo ruido de errores 404 en dashboard header cuando no existe tienda (estado esperado para merchant nuevo).
- Guards de rol ya evitan dashboard para cliente (`RequireRole role="merchant"`).

## FASE 1 - Auditoria con evidencia

### A) CORS / API

| Severidad | Hallazgo | Evidencia | Impacto |
|---|---|---|---|
| Critico | `allowed_origins` usaba wildcard de Vercel | `config/cors.php:15` (antes de fix) | Previews pueden no matchear correctamente y causar bloqueo CORS intermitente. |
| Alto | CORS global sí está activo | `app/Http/Kernel.php:14` | Correcto; descarta fallo por ausencia de middleware. |
| Medio | Configuración CORS con credentials y paths API | `config/cors.php:39`, `config/cors.php:58` | Correcto, pero dependía de matching de origin. |
| Alto | En repo existen rutas API requeridas | `routes/api.php:88`, `routes/api.php:97`, `routes/api.php:137`, `routes/api.php:143` | Si en producción aparece 405 para `POST /api/categories`, el problema es despliegue/caché, no código fuente. |
| Medio | Dockerfile no ejecuta `config:cache` ni `optimize:clear` al boot | `Dockerfile:40` | Correcto (arranque mínimo). Aún se requiere limpiar cache en Railway tras cambios. |

### B) Tema (dark/light)

| Severidad | Hallazgo | Evidencia | Impacto |
|---|---|---|---|
| Alto | Tema global está correctamente centralizado con clase `dark` | `comercio-plus-frontend/tailwind.config.js:3`, `comercio-plus-frontend/src/providers/theme-provider.tsx:28`, `comercio-plus-frontend/src/app/main.tsx:9` | Base sólida para contrastes. |
| Alto | Login/Register tenían combinaciones que podían degradar contraste en dark | `comercio-plus-frontend/src/app/login/page.tsx:97`, `comercio-plus-frontend/src/app/register/page.tsx:101` | Campos podían verse inconsistentes dependiendo de sobreescrituras de clase y fondo. |
| Medio | Tokens base sí existen (`input-dark`, `select-dark`, `textarea-dark`) | `comercio-plus-frontend/src/app/globals.css:110`, `comercio-plus-frontend/src/app/globals.css:114`, `comercio-plus-frontend/src/app/globals.css:118` | Correcto, pero las overrides en login/register debían alinearse. |

### C) Roles y rutas

| Severidad | Hallazgo | Evidencia | Impacto |
|---|---|---|---|
| Alto | Dashboard protegido solo para merchant | `comercio-plus-frontend/src/app/App.tsx:52` | Cliente no entra al dashboard de comerciante. |
| Medio | `/api/my/store` se consulta en login/register para merchant | `comercio-plus-frontend/src/app/login/page.tsx:18`, `comercio-plus-frontend/src/app/register/page.tsx:24` | Esperable 404 si merchant no tiene tienda aún; debe manejarse como flujo, no error crítico. |
| Alto | Contrato backend de `/api/my/store` está bien definido | `app/Http/Controllers/Api/StoreController.php:33`, `app/Http/Controllers/Api/StoreController.php:38` | 404 con JSON claro cuando no hay tienda. |

## FASE 2 - Plan aplicado (cambios mínimos)

### 1) CORS robusto para Vercel prod/preview/local
- Ajustado `config/cors.php`:
  - `allowed_origins` explícitos (prod + local) sin wildcard string.
  - Preview wildcard mantenido en `allowed_origins_patterns` con regex.
- Confirmado `HandleCors` global en `Kernel`.

### 2) Tema y contraste
- Ajustados formularios de login/register para garantizar:
  - light: texto/placeholder oscuros en superficies claras.
  - dark: texto/placeholder claros en superficies oscuras.
- Se mantuvo `ThemeProvider` único como fuente de verdad.

### 3) Roles y UX de merchant sin tienda
- Se redujo ruido de logs en header dashboard cuando `/api/my/store` responde 404 (caso funcional esperado).
- Se mantiene bloqueo de dashboard para clientes vía `RequireRole`.

### 4) Endpoints coherentes
- Confirmado en rutas locales: `GET /api/categories`, `POST /api/categories`, `GET /api/products`, `POST /api/login`, `GET /api/my/store`.

## Archivos modificados en esta iteración
- `config/cors.php`
- `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx`
- `comercio-plus-frontend/src/app/dashboard/page.tsx`
- `comercio-plus-frontend/src/app/login/page.tsx`
- `comercio-plus-frontend/src/app/register/page.tsx`

## FASE 3 - Validacion real

### Backend local
- `php artisan optimize:clear` ✅
- `php artisan route:list | rg "api"` ✅
  - Incluye `POST api/categories`.

### CORS preflight contra Railway
- Comandos ejecutados (`curl OPTIONS`) ❌ en este entorno local de ejecución.
- Resultado técnico: fallo de red saliente por proxy local (`Failed to connect to 127.0.0.1 port 9`), no por respuesta del backend.
- Conclusión: no se pudo medir headers remotos desde este runner; debe verificarse en tu terminal/CI con conectividad normal.

### Frontend
- `npm run lint` ✅
- `npm run build` ✅

## Checklist de verificación en producción (Vercel + Railway)
1. Railway desplegado en commit backend más reciente.
2. En Railway shell:
   - `php artisan optimize:clear`
   - `php artisan route:list --path=api/categories` (debe mostrar `POST`).
3. Probar preflight desde tu equipo:
   - `OPTIONS /api/categories` con origin prod y preview.
4. Cliente:
   - no entra a `/dashboard/*` (redirige a `/`).
   - ve storefront público (`/stores`, `/store/:id`, productos).
5. Merchant:
   - login -> sin tienda: flujo a crear tienda.
   - con tienda: dashboard store/products funcional.
   - crear categoría desde dashboard y verla en select de productos.
6. Tema:
   - light: textos oscuros por defecto.
   - dark: textos claros por defecto.
   - login/register inputs y placeholders legibles en ambos modos.

## Nota operativa importante
El 405 actual en producción para `POST /api/categories` indica instancia backend desalineada (deploy o cache), no falta de ruta en código fuente actual.
