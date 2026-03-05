# UNIVERSAL_COMERCIOPLUS

DOC_STATUS: CANONICO_ACTIVO  
DOC_DATE: 2026-03-05  
DOC_SCOPE: Estado real del repositorio (codigo + comandos ejecutados en FASE 0)

## 1) Vision del producto

ComercioPlus es una plataforma para comercios de repuestos de motos en Colombia con dos experiencias principales:

- Merchant (comerciante): operacion de tienda, catalogo, inventario, pedidos, picking, fiado y configuracion comercial.
- Client (cliente): descubrimiento de tiendas, catalogo, carrito, checkout y seguimiento de compra inmediata.

Regla de verdad: si hay diferencia entre docs y codigo, manda el codigo.

## 2) Arquitectura real

### 2.1 Stack implementado

- Backend API: Laravel 11.47.0 + Sanctum token bearer + MySQL.
- Frontend activo: React + Vite + TypeScript + Tailwind (`comercio-plus-frontend/`).
- Frontend legacy: Vue + Laravel Vite (`resources/js`, `vite.legacy.config.js`).
- E2E: Playwright (`playwright.config.ts`, `tests-e2e/`).
- Testing backend: PHPUnit/Laravel test runner (`php artisan test`).
- Media: Cloudinary + fallback storage local.
- Pagos: Wompi endpoints y webhook.

### 2.2 Deploy confirmado

- Frontend Vercel (detectado en config): `https://comercio-plus-oficial.vercel.app`
  - Evidencia: `config/cors.php` (origen fijo) y `comercio-plus-frontend/vercel-check.ps1` (BaseUrl default).
- Backend Railway (detectado en rewrites): `https://comercioplusoficial-production-d61e.up.railway.app`
  - Evidencia: `comercio-plus-frontend/vercel.json` (`/api`, `/sanctum`, `/storage`).

### 2.3 Auth y CORS real

- Auth API: token bearer (`POST /api/login`, `POST /api/register`, `GET /api/me` protegido).
- `config/sanctum.php`: flujo API token (stateful/cookies desactivado).
- `config/cors.php`: permite `api/*`, `sanctum/csrf-cookie`, `login`, `logout`.
- Origenes CORS incluyen:
  - `https://comercio-plus-oficial.vercel.app`
  - `http://localhost:5173`
  - `http://127.0.0.1:5173`
  - mas origenes via variables `FRONTEND_URL`, `VERCEL_PROD_ORIGIN`, `CORS_ALLOWED_ORIGINS`.

## 3) Mapa real del sistema (FASE 0)

### 3.1 Evidencia de inventario ejecutada

Comandos ejecutados:

- `git status --short`
- `php artisan --version`
- `php artisan route:list`
- `php artisan route:list --path=api`
- `php artisan route:list --path=api --json`
- lectura de `composer.json`
- lectura de `package.json` (raiz y frontend)
- lectura de `playwright.config.ts`
- inventario de `docs/`
- revision de rutas React/Vue y controladores API

Resultado clave:

- Worktree sucio: SI (archivos modificados y nuevos, principalmente API, frontend y docs).
- Version Laravel: `11.47.0`.
- Rutas totales Laravel: `160`.
- Rutas API: `130` (`23` publicas, `107` protegidas).

### 3.2 Estructura de carpetas (resumen)

```text
comercioPlusOficial/
|- app/                      # Backend Laravel (controllers, models, services)
|- routes/                   # api.php, web.php, auth.php
|- config/                   # cors.php, sanctum.php, services.php
|- database/                 # migrations
|- tests/                    # Feature, Unit, e2e legacy
|- tests-e2e/                # Playwright active smoke
|- comercio-plus-frontend/   # Frontend React/Vite/Tailwind
|  |- src/
|  |- vercel.json
|  |- vite.config.ts
|- resources/js/             # Frontend Vue legacy
|- docs/                     # Documentacion
|- playwright.config.ts      # Config E2E raiz
|- vite.config.js            # Root Vite apuntando a frontend React
|- vite.legacy.config.js     # Build legacy Vue/Laravel
```

### 3.3 Rutas frontend activas (React)

Fuente: `comercio-plus-frontend/src/app/App.tsx`.

Publicas:

- `/`
- `/about`
- `/accessibility`
- `/blog`
- `/careers`
- `/cart`
- `/category/:id`
- `/checkout`
- `/checkout/success`
- `/contact`
- `/cookies`
- `/crear-tienda` (redirect)
- `/faq`
- `/forgot-password`
- `/help`
- `/how-it-works`
- `/login`
- `/orders/:id`
- `/payment/success`
- `/press`
- `/privacy`
- `/product/:id`
- `/products`
- `/products/:id`
- `/register`
- `/registro` (redirect)
- `/report`
- `/returns`
- `/sitemap`
- `/status`
- `/store/:id`
- `/store/create`
- `/stores`
- `/stores/:storeSlug/products`
- `/team`
- `/terms`
- `/warranty`

Protegidas merchant (`RequireAuth` + `RequireRole('merchant')`):

- `/dashboard`
- `/dashboard/categories`
- `/dashboard/credit`
- `/dashboard/customers`
- `/dashboard/inventory`
- `/dashboard/inventory/import`
- `/dashboard/inventory/receive`
- `/dashboard/orders`
- `/dashboard/orders/:id/picking`
- `/dashboard/products`
- `/dashboard/products/:id/edit`
- `/dashboard/products/create`
- `/dashboard/reports`
- `/dashboard/settings`
- `/dashboard/store`

### 3.4 Rutas frontend legacy (Vue)

Fuente: `resources/js/router/index.js`.

- `/`
- `/stores`
- `/stores/create`
- `/products`
- `/product/:slug`
- `/cart`
- `/checkout`
- `/orders`
- `/login`
- `/register`
- `/profile`
- `/settings`
- `/:pathMatch(.*)*`

### 3.5 Endpoints API completos

Fuente: `php artisan route:list --path=api --json` (130 endpoints).

Resumen:

- Total: `130`
- Publicos: `23`
- Protegidos (`auth:sanctum`): `107`

Listado completo PUBLICO:

```text
GET|HEAD api/_debug/env
GET|HEAD api/categories
GET|HEAD api/categories/{category}
GET|HEAD api/health
GET|HEAD api/health/integrations
GET|HEAD api/hero-images
POST api/login
GET|HEAD api/login
POST api/orders/create
POST api/payments/wompi/create
GET|HEAD api/payments/wompi/pse-banks
GET|HEAD api/payments/wompi/status/{transactionId}
POST api/payments/wompi/webhook
GET|HEAD api/products
GET|HEAD api/products/{product}
GET|HEAD api/products/{product}/alerts/mine
GET|HEAD api/public/barcode/search
GET|HEAD api/public/categories
GET|HEAD api/public/products
GET|HEAD api/public/stores
GET|HEAD api/public/stores/{store}
POST api/register
GET|HEAD api/register
```

Listado completo PROTEGIDO:

```text
POST api/barcode/generate-batch
GET|HEAD api/barcode/search
GET|HEAD api/cart
POST api/cart
DELETE api/cart
GET|HEAD api/cart-products
POST api/cart-products
GET|HEAD api/cart-products/{cart_product}
PUT|PATCH api/cart-products/{cart_product}
DELETE api/cart-products/{cart_product}
POST api/cart/clear
GET|HEAD api/cart/count
GET|HEAD api/cart/{cart}
PUT|PATCH api/cart/{cart}
DELETE api/cart/{cart}
POST api/categories
PUT api/categories/{category}
DELETE api/categories/{category}
POST api/inventory/adjust
POST api/inventory/bulk-delete
POST api/inventory/import
GET|HEAD api/inventory/invoices
GET|HEAD api/inventory/movements
POST api/inventory/preview
GET|HEAD api/inventory/stats
GET|HEAD api/inventory/summary
GET|HEAD api/inventory/template
POST api/logout
GET|HEAD api/me
GET|HEAD api/merchant/credit
POST api/merchant/credit
GET|HEAD api/merchant/credit/{creditAccount}
POST api/merchant/credit/{creditAccount}/charge
POST api/merchant/credit/{creditAccount}/payment
GET|HEAD api/merchant/customers
GET|HEAD api/merchant/dashboard
POST api/merchant/inventory/create-from-scan
GET|HEAD api/merchant/inventory/movements
POST api/merchant/inventory/scan-in
GET|HEAD api/merchant/orders
PUT api/merchant/orders/{id}/status
GET|HEAD api/merchant/orders/{order}/picking
POST api/merchant/orders/{order}/picking/complete
POST api/merchant/orders/{order}/picking/fallback
POST api/merchant/orders/{order}/picking/manual
POST api/merchant/orders/{order}/picking/reset
POST api/merchant/orders/{order}/picking/scan
GET|HEAD api/merchant/picking/events
POST api/merchant/products/lookup-code
GET|HEAD api/merchant/stats
GET|HEAD api/merchant/store
PUT api/merchant/store
GET|HEAD api/merchant/store/verification
POST api/merchant/store/verification
GET|HEAD api/my/store
GET|HEAD api/orders
POST api/orders
GET|HEAD api/orders/{order}
PUT|PATCH api/orders/{order}
DELETE api/orders/{order}
POST api/products
PUT api/products/{product}
DELETE api/products/{product}
POST api/products/{product}/alerts
DELETE api/products/{product}/alerts
GET|HEAD api/products/{product}/barcode
GET|HEAD api/reports/export/sales.csv
GET|HEAD api/reports/export/tax.csv
GET|HEAD api/reports/inventory
GET|HEAD api/reports/sales
GET|HEAD api/reports/summary
GET|HEAD api/reports/tax
GET|HEAD api/reports/top-products
POST api/stores
POST api/stores/register-customer
PUT api/stores/{store}
DELETE api/stores/{store}
POST api/stores/{store}/follow
DELETE api/stores/{store}/follow
POST api/stores/{store}/inventory/adjust
GET|HEAD api/stores/{store}/inventory/low-stock
GET|HEAD api/stores/{store}/inventory/movements
GET|HEAD api/stores/{store}/reorder/requests
POST api/stores/{store}/reorder/requests
GET|HEAD api/stores/{store}/reorder/requests/{purchaseRequest}
PUT api/stores/{store}/reorder/requests/{purchaseRequest}
GET|HEAD api/stores/{store}/reorder/suggestions
GET|HEAD api/stores/{store}/reports
POST api/stores/{store}/reports/generate
GET|HEAD api/stores/{store}/reports/latest
GET|HEAD api/stores/{store}/tax-settings
PUT api/stores/{store}/tax-settings
POST api/stores/{store}/visit
GET|HEAD api/subscriptions
POST api/subscriptions
GET|HEAD api/subscriptions/{subscription}
PUT|PATCH api/subscriptions/{subscription}
DELETE api/subscriptions/{subscription}
POST api/uploads/products
POST api/uploads/profiles/photo
POST api/uploads/stores/cover
POST api/uploads/stores/logo
GET|HEAD api/users
POST api/users
GET|HEAD api/users/{user}
PUT|PATCH api/users/{user}
DELETE api/users/{user}
```

### 3.6 Dependencias QA detectadas

Backend:

- `phpunit/phpunit`
- `laravel/framework`
- `laravel/sanctum`

Frontend/QA:

- `@playwright/test` (raiz, script `npm run test:e2e`)
- `eslint` (frontend script `lint`)
- `vite` + `typescript`

Notas:

- `vitest.config.ts` existe en raiz pero esta vacio (0 bytes).
- E2E activo: `tests-e2e/smoke.spec.ts`.
- E2E legacy adicional: `tests/e2e/auth.spec.js`.

## 4) Flujos por rol (estado real)

## 4.1 Merchant

- Auth merchant (registro/login/redireccion): implementado.
- Store management: implementado con `GET/PUT /api/merchant/store`.
- Productos: CRUD implementado + carga de imagen + lookup por codigo.
- Categorias: CRUD implementado.
- Inventario:
  - resumen/stats/movimientos: implementado.
  - ajustes: implementado.
  - importacion CSV/XLSX preview/import/template: implementado.
  - recepcion por scanner + create-from-scan: implementado.
- Pedidos:
  - lista y detalle: implementado.
  - cambio de estado: implementado.
  - picking (scan/manual/fallback/complete/reset): implementado.
- Reportes:
  - backend de reportes (sales/tax/top/inventory/export csv): implementado.
  - UI activa `/dashboard/reports`: conectada a `/api/reports/summary|sales|tax|top-products|inventory` + export CSV.
- IVA:
  - backend dedicado `GET/PUT /api/stores/{store}/tax-settings`: implementado.
  - UI activa solo toggle general `taxes_enabled` en `/dashboard/store`.
- Fiado digital (`/merchant/credit*`): implementado backend + UI.
- Verificacion de tienda (`/merchant/store/verification`): endpoint implementado (sin pantalla dedicada activa en router).

### 4.2 Client

- Home landing: implementado (`/`, APIs publicas).
- Listado de tiendas: implementado (`/stores`, `/api/public/stores`).
- Detalle de tienda + catalogo por tienda: implementado (`/store/:id`, `/stores/:storeSlug/products`).
- Carrito (agregar, editar cantidad, eliminar): implementado.
- Checkout (datos comprador + metodo de pago + redireccion Wompi): implementado.
- Confirmacion/factura de compra: implementado (`/checkout/success` + `GET /api/orders/{id}`).
- Registro/Login client: implementado.
- Historial de pedidos client (vista dedicada): no implementado en UI activa.
- Catalogo global `/products` y `/products/:id`: usan mocks locales en frontend activo (no API real).

## 5) Variables de entorno relevantes (sin secretos)

Backend (raiz):

- `APP_ENV`
- `APP_URL`
- `FRONTEND_URL`
- `VERCEL_PROD_ORIGIN`
- `SANCTUM_STATEFUL_DOMAINS`
- `CORS_ALLOWED_ORIGINS`
- `CORS_ALLOWED_ORIGIN_PATTERNS`
- `WOMPI_*`
- `CLOUDINARY_*`

Frontend (`comercio-plus-frontend`):

- `VITE_API_BASE_URL`
- `VITE_API_URL`
- `VITE_DEV_PROXY_TARGET`
- `VITE_APP_NAME`
- `VITE_CLOUDINARY_CLOUD_NAME`
- `VITE_CLOUDINARY_UPLOAD_PRESET`

Regla runtime:

- Dev: si falta `VITE_API_BASE_URL`, usa fallback `/api` (proxy Vite).
- Prod: si falta `VITE_API_BASE_URL`, bloquea requests de auth (`API_CONFIG_OK=false`).

## 6) Estandares UI reales

Direccion visual actual detectada:

- Paleta principal naranja: `#FF6A00` / `comercioplus-600`.
- Estilo: minimalista profesional, cards blancas con bordes suaves, CTA naranja.
- Componentes frecuentes:
  - botones ERP (`ErpBtn`)
  - badges de estado (`ErpBadge`)
  - cards KPI (`ErpKpiCard`)
  - tablas operativas dashboard.
- Layouts:
  - `PublicLayout`
  - `AuthLayout`
  - `DashboardLayout`
- Accesibilidad basica:
  - labels en formularios de auth/checkout
  - estados de carga/error visibles
  - navegacion responsive en navbar.

## 7) Estado real de features (EXISTE / PARCIAL / NO EXISTE)

### 7.1 Tabla Merchant

| Feature Merchant | Estado | Evidencia |
|---|---|---|
| Login/registro merchant + seleccion de rol | EXISTE | `src/app/register/page.tsx`, `AuthController` |
| Redireccion segun rol y tienda | EXISTE | `resolvePostAuthRoute` |
| Crear tienda (onboarding UI completo) | PARCIAL | `/store/create` solo CTA; creacion real via `POST /api/stores` |
| Editar tienda (info, contacto, logo, portada) | EXISTE | `/dashboard/store`, `PUT /api/merchant/store` |
| Colores/branding avanzado de tienda | NO EXISTE | no campos de color en UI activa |
| CRUD productos manual | EXISTE | `/dashboard/products`, `POST/PUT/DELETE /api/products` |
| Crear producto con codigo (lookup/scanner) | EXISTE | `lookup-code`, scanner en products |
| Imagen de producto | EXISTE | `POST /api/uploads/products` |
| CRUD categorias | EXISTE | `/dashboard/categories`, `/api/categories*` |
| Inventario resumen/stock bajo | EXISTE | `/dashboard/inventory`, `/api/inventory/summary` |
| Ajustes inventario | EXISTE | `/api/inventory/adjust` + drawer UI |
| Importacion excel/csv preview/import | EXISTE | `/dashboard/inventory/import`, `/api/inventory/preview|import` |
| Scanner recepcion + create-from-scan | EXISTE | `/dashboard/inventory/receive`, `/api/merchant/inventory/*` |
| Pedidos listar/detalle | EXISTE | `/dashboard/orders`, `/api/merchant/orders` |
| Cambiar estado de pedido | EXISTE | `PUT /api/merchant/orders/{id}/status` |
| Picking/alistamiento | EXISTE | `/dashboard/orders/:id/picking`, `/api/merchant/orders/{id}/picking*` |
| Reportes ventas/impuestos/top/inventario en UI activa | EXISTE | dashboard consume `/api/reports/*` y exporta CSV |
| Configuracion IVA detallada (tasa, redondeo, include tax) en UI activa | PARCIAL | backend `tax-settings` existe, UI activa solo toggle `taxes_enabled` |
| Fiado digital | EXISTE | `/dashboard/credit`, `/api/merchant/credit*` |
| Verificacion de tienda (flujo UI activo completo) | PARCIAL | endpoints existen, ruta UI dedicada no expuesta en router activo |

### 7.2 Tabla Client

| Feature Client | Estado | Evidencia |
|---|---|---|
| Home/landing | EXISTE | `/`, `Home.tsx` |
| Listado de tiendas | EXISTE | `/stores`, `/api/public/stores` |
| Detalle de tienda + productos | EXISTE | `/store/:id`, `/stores/:slug/products` |
| Producto detail API real | PARCIAL | `/products/:id` usa `mockProducts` |
| Catalogo global API real | PARCIAL | `/products` usa `mockProducts` |
| Carrito (add/edit/remove) | EXISTE | `CartContext`, `/cart` |
| Checkout (datos comprador + metodo pago) | EXISTE | `/checkout`, `POST /api/orders/create` |
| Confirmacion success/factura | EXISTE | `/checkout/success`, `GET /api/orders/{id}` |
| Registro/Login client | EXISTE | `/register`, `/login` |
| Historial de pedidos client (vista dedicada) | NO EXISTE | no ruta activa tipo `/orders` para client |
| Busqueda publica de barcode en UI | NO EXISTE | endpoint existe, servicio UI removido |

## 8) Documentacion canonicidad y duplicados

Estado documental detectado en `docs/`:

- Canonicidad objetivo: **este archivo** (`docs/UNIVERSAL_COMERCIOPLUS.md`).
- Duplicado funcional: `docs/UNIVERSAL_COMERCIOPLUS_AI.md` (derivado para consumo IA).
- Artefactos de distribucion: `UNIVERSAL_COMERCIOPLUS_FULL.part*.zip` + `UNIVERSAL_FILE_INDEX.txt`.
- Otros docs activos: `DOC_GOVERNANCE.md`, `README.md`, `README_VALIDADOR.md`.

Decision actual:

- Fuente unica de verdad tecnica: `docs/UNIVERSAL_COMERCIOPLUS.md`.
- `UNIVERSAL_COMERCIOPLUS_AI.md` se mantiene como alias derivado, no como segunda verdad.
