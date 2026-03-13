# EXPO_DOSSIER

DOC_STATUS: EXPO_READY_FINAL  
DOC_DATE: 2026-03-05  
BASE_DOC: `docs/UNIVERSAL_COMERCIOPLUS.md`

## 1) Que es ComercioPlus

Problema:

- Comercios de repuestos de motos operan ventas, inventario y pedidos con procesos manuales o dispersos.
- Se pierde trazabilidad en stock, pedidos y cobro.

Solucion:

- Plataforma web con dos frentes:
  - Merchant: gestion operativa de tienda (catalogo, inventario, pedidos, picking, fiado).
  - Client: descubrimiento de tiendas, carrito y checkout.

Usuarios:

- Comerciante (dueno/operador de tienda).
- Cliente final (comprador).

## 2) Arquitectura real (diagrama simple)

```text
                +---------------------------+
                |   Vercel (Frontend React) |
                | comercio-plus-oficial     |
                +------------+--------------+
                             |
                 rewrites /api, /sanctum, /storage
                             |
                             v
      +-----------------------------------------------+
      | Railway (Laravel API + Sanctum + MySQL)       |
      | comercioplusoficial-production-d61e.up...     |
      +---------------------+-------------------------+
                            |
                            v
                    +---------------+
                    |   Database    |
                    |   (MySQL)     |
                    +---------------+
```

Complementos:

- Cloudinary para media.
- Wompi para pagos.
- Playwright para E2E.
- Frontend legacy Vue conservado para compatibilidad.

## 3) Funcionalidades por rol

### Merchant

- Auth merchant (registro/login con rol).
- Gestion de tienda (`/dashboard/store`).
- CRUD productos (`/dashboard/products`).
- CRUD categorias (`/dashboard/categories`).
- Inventario (resumen, ajustes, importacion, recepcion scanner).
- Pedidos y cambio de estado (`/dashboard/orders`).
- Picking/alistamiento (`/dashboard/orders/:id/picking`).
- Fiado digital (`/dashboard/credit`).

### Client

- Home y listado de tiendas.
- Vista de tienda y productos publicados.
- Carrito.
- Checkout + redireccion de pago.
- Confirmacion/factura.
- Login/registro.

## 4) Evidencia de calidad (estado actual)

Evidencia confirmada en repo:

- Tests backend listados en `tests/Feature/*`.
- E2E activo en `tests-e2e/smoke.spec.ts`.
- Config E2E en `playwright.config.ts`.
- Scripts de build/lint/test definidos en `package.json` raiz y frontend.

Resultados reales ejecutados (2026-03-05):

- Backend tests: `121 passed (402 assertions)`.
- Frontend lint: PASS.
- Frontend build (React/Vite): PASS.
- Legacy build: PASS.
- Smoke preview local (`/`, `/stores`, `/products`, `/cart`): todas 200.
- Playwright E2E: 2 PASS (chromium + mobile-chrome).

Referencia de plan de ejecucion:

- `docs/QA_RELEASE_REPORT.md` (secciones A-G).

## 5) Evidencia de despliegue

Deteccion en codigo:

- Frontend Vercel: `https://comercio-plus-oficial.vercel.app`
- API Railway: `https://comercioplusoficial-production-d61e.up.railway.app`

Resultados HTTP de produccion:

| Verificacion | Resultado |
|---|---|
| Vercel `/`, `/stores`, `/products`, `/cart`, `/checkout` | 200 en todas |
| Rewrite Vercel `/api/health` | 200 |
| Railway `/api/health` | 200 |
| Railway `/api/public/stores`, `/api/public/products` | 200 |
| Railway `/api/hero-images` | 404 |
| Railway `/api/public/barcode/search?code=TEST` | 404 |
| Vercel-check script (SPA + assets) | OK (sin pantalla en blanco) |

## 6) Seguridad (Sanctum + CORS)

Implementado:

- Auth token bearer (`/api/login`, `/api/register`, `/api/me`).
- Proteccion de endpoints privados con `auth:sanctum`.
- CORS configurado para origenes de Vercel y local.
- Throttle en endpoints publicos sensibles (`hero-images`, `public/barcode/search`).

Validacion ejecutada:

| Control | Resultado |
|---|---|
| `POST /api/register` | 201 |
| `POST /api/login` | 200 |
| `GET /api/me` sin token (`Accept: application/json`) | 401 |
| `GET /api/me` con bearer token | 200 |
| OPTIONS `/api/public/stores` con Origin Vercel | 204 + `Access-Control-Allow-Origin` correcto |
| OPTIONS `/api/me` con Origin Vercel | 204 + `Access-Control-Allow-Origin` correcto |

## 7) Inventario y scanner: como opera

Flujo actual:

1. Merchant escanea codigo en recepcion (`/dashboard/inventory/receive`).
2. API busca producto por barcode/sku/qr.
3. Si existe: incrementa stock y registra movimiento.
4. Si no existe: devuelve `PRODUCT_NOT_FOUND` + `CREATE_PRODUCT`.
5. UI permite crear producto rapido (`create-from-scan`) y registrar stock inicial.
6. Todo queda trazado en movimientos.

Complemento:

- Importacion masiva CSV/XLSX con preview e import final.

## 8) Limitaciones actuales y plan de mejora

Limitaciones detectadas:

- Dashboard settings depende de endpoint no expuesto en API activa.
- Catalogo global y detalle global de producto usan mocks.
- Historial de pedidos client no tiene vista dedicada.

Plan:

- Ver `Anexos de faltantes` en `docs/QA_RELEASE_REPORT.md`.
- Prioridades separadas en P0/P1/P2.

## 9) Checklist "lista para promocion"

| Criterio | Estado actual | Criterio de aceptacion final |
|---|---|---|
| Documentacion canonica unica | LISTO | `UNIVERSAL_COMERCIOPLUS.md` actualizado y coherente |
| Mapa real rutas/API | LISTO | listado completo frontend/API validado por comandos |
| Plan QA exhaustivo | LISTO | casos A-G definidos con evidencia requerida |
| Pruebas locales (FASE 3) | PARCIAL (automatizado + smoke en verde) | completar matriz manual FE/INT/PERF |
| Pruebas produccion (FASE 4) | PARCIAL (HTTP/CORS/Auth OK) | cerrar drift de endpoints 404 y validar matriz completa en prod |
| Riesgos y faltantes priorizados | LISTO | anexo P0/P1/P2 separado del reporte de pruebas |
| Material para exposicion tecnica | LISTO FINAL | relato + arquitectura + evidencia objetiva |

## 10) Flujo operativo oficial (2026-03-06)

- Rama oficial de produccion: master.
- Frontend React/redisenos: http://localhost:5173.
- Backend/API local y legacy: http://127.0.0.1:8000.

Secuencia minima pre-demo o pre-deploy:

1. git status --short (limpio)
2. git rev-list --left-right --count origin/master...HEAD (0 0)
3. php artisan test
4. `npm run lint --prefix comercio-plus-frontend`
5. `npm run build --prefix comercio-plus-frontend`
6. `npm run build:legacy`
7. git push origin master
8. smoke Vercel + endpoints Railway