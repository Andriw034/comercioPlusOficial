# UNIVERSAL_COMERCIOPLUS

DOC_STATUS: CANONICO_ACTIVO
DOC_DATE: 2026-03-24
DOC_SCOPE: Estado real del repositorio (codigo + comandos ejecutados en FASE 0)

## 1) Vision del producto

ComercioPlus es una plataforma para comercios de repuestos de motos en Colombia con dos experiencias principales:

- Merchant (comerciante): operacion de tienda, catalogo, inventario, pedidos, picking, fiado y configuracion comercial.
- Client (cliente): descubrimiento de tiendas, catalogo, carrito, checkout y seguimiento de compra inmediata.

Regla de verdad: si hay diferencia entre docs y codigo, manda el codigo.

## 2) Arquitectura real

### 2.1 Stack implementado

- Backend API: Laravel 11.47.0 + Sanctum token bearer + MySQL.
- Frontend activo: React 19.2.4 + Vite 7.2.4 + TypeScript 5.9.3 + Tailwind CSS 3.4.17 (`comercio-plus-frontend/`).
- Frontend legacy: Vue + Laravel Vite (`resources/js`, `vite.legacy.config.js`).
- E2E: Playwright (`playwright.config.ts`, `tests-e2e/`).
- Testing backend: PHPUnit/Laravel test runner (`php artisan test`).
- Media: Cloudinary (`cloudinary/cloudinary_php ^3.1`) + fallback storage local.
- Pagos: MercadoPago SDK (`mercadopago/dx-php ^3.8` backend, `@mercadopago/sdk-react` frontend) + webhook.

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

- Worktree sucio: SI (archivos modificados y untracked: package-lock, TODO.md, scripts .bat).
- Version Laravel: `11.47.0`.
- Rutas totales Laravel: `173`.
- Rutas API: `143`.
- Node.js: `v22.22.1`.

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
- `/checkout/result`
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
- `/orders/history`
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
- `/dashboard/inventory/restock`
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

Fuente: `php artisan route:list --path=api` (2026-03-15, 143 endpoints).

Resumen:

- Total: `143`

Listado completo:

```text
GET|HEAD api/_debug/env
POST     api/barcode/generate-batch
GET|HEAD api/barcode/search
GET|HEAD api/cart
POST     api/cart
DELETE   api/cart
GET|HEAD api/cart-products
POST     api/cart-products
GET|HEAD api/cart-products/{cart_product}
PUT|PATCH api/cart-products/{cart_product}
DELETE   api/cart-products/{cart_product}
POST     api/cart/clear
GET|HEAD api/cart/count
GET|HEAD api/cart/{cart}
PUT|PATCH api/cart/{cart}
DELETE   api/cart/{cart}
GET|HEAD api/categories
POST     api/categories
GET|HEAD api/categories/{category}
PUT      api/categories/{category}
DELETE   api/categories/{category}
GET|HEAD api/health
GET|HEAD api/health/integrations
GET|HEAD api/hero-images
POST     api/inventory/adjust
POST     api/inventory/bulk-delete
POST     api/inventory/import
GET|HEAD api/inventory/invoices
GET|HEAD api/inventory/movements
POST     api/inventory/preview
GET|HEAD api/inventory/stats
GET|HEAD api/inventory/summary
GET|HEAD api/inventory/template
POST     api/login
GET|HEAD api/login
POST     api/logout
GET|HEAD api/me
GET|HEAD api/merchant/credit
POST     api/merchant/credit
GET|HEAD api/merchant/credit/{creditAccount}
POST     api/merchant/credit/{creditAccount}/charge
POST     api/merchant/credit/{creditAccount}/payment
GET|HEAD api/merchant/customers
DELETE   api/merchant/customers/{customer}
GET|HEAD api/merchant/dashboard
POST     api/merchant/inventory/create-from-scan
GET|HEAD api/merchant/inventory/movements
POST     api/merchant/inventory/scan-in
GET|HEAD api/merchant/live-metrics
GET|HEAD api/merchant/orders
PUT      api/merchant/orders/{id}/status
GET|HEAD api/merchant/orders/{order}/picking
POST     api/merchant/orders/{order}/picking/complete
POST     api/merchant/orders/{order}/picking/fallback
POST     api/merchant/orders/{order}/picking/manual
POST     api/merchant/orders/{order}/picking/reset
POST     api/merchant/orders/{order}/picking/scan
GET|HEAD api/merchant/picking/events
POST     api/merchant/products/lookup-code
GET|HEAD api/merchant/restock
GET|HEAD api/merchant/restock/{product}
PUT      api/merchant/restock/{product}
POST     api/merchant/restock/{product}/dismiss
POST     api/merchant/restock/{product}/request
GET|HEAD api/merchant/stats
GET|HEAD api/merchant/store
PUT      api/merchant/store
GET|HEAD api/merchant/store/verification
POST     api/merchant/store/verification
GET|HEAD api/my/store
GET|HEAD api/orders
POST     api/orders
GET|HEAD api/orders/{order}
PUT|PATCH api/orders/{order}
DELETE   api/orders/{order}
POST     api/payments/create-preference
GET|HEAD api/payments/result
POST     api/payments/webhook
GET|HEAD api/products
POST     api/products
GET|HEAD api/products/{product}
PUT      api/products/{product}
DELETE   api/products/{product}
POST     api/products/{product}/alerts
DELETE   api/products/{product}/alerts
GET|HEAD api/products/{product}/alerts/mine
GET|HEAD api/products/{product}/barcode
GET|HEAD api/profile
PUT      api/profile
PUT      api/profile/password
GET|HEAD api/public/barcode/search
GET|HEAD api/public/categories
GET|HEAD api/public/products
GET|HEAD api/public/stores
GET|HEAD api/public/stores/{store}
POST     api/register
GET|HEAD api/register
GET|HEAD api/reports/alerts
GET|HEAD api/reports/export/sales.csv
GET|HEAD api/reports/export/tax.csv
GET|HEAD api/reports/inventory
GET|HEAD api/reports/inventory-decisions
GET|HEAD api/reports/sales
GET|HEAD api/reports/summary
GET|HEAD api/reports/tax
GET|HEAD api/reports/top-products
GET|HEAD api/reports/trends
GET|HEAD api/settings
PUT      api/settings
POST     api/stores
POST     api/stores/register-customer
PUT      api/stores/{store}
DELETE   api/stores/{store}
POST     api/stores/{store}/follow
DELETE   api/stores/{store}/follow
POST     api/stores/{store}/inventory/adjust
GET|HEAD api/stores/{store}/inventory/low-stock
GET|HEAD api/stores/{store}/inventory/movements
GET|HEAD api/stores/{store}/reorder/requests
POST     api/stores/{store}/reorder/requests
GET|HEAD api/stores/{store}/reorder/requests/{purchaseRequest}
PUT      api/stores/{store}/reorder/requests/{purchaseRequest}
GET|HEAD api/stores/{store}/reorder/suggestions
GET|HEAD api/stores/{store}/reports
POST     api/stores/{store}/reports/generate
GET|HEAD api/stores/{store}/reports/latest
GET|HEAD api/stores/{store}/tax-settings
PUT      api/stores/{store}/tax-settings
POST     api/stores/{store}/visit
GET|HEAD api/subscriptions
POST     api/subscriptions
GET|HEAD api/subscriptions/{subscription}
PUT|PATCH api/subscriptions/{subscription}
DELETE   api/subscriptions/{subscription}
POST     api/uploads/products
POST     api/uploads/profiles/photo
POST     api/uploads/stores/cover
POST     api/uploads/stores/logo
GET|HEAD api/users
POST     api/users
GET|HEAD api/users/{user}
PUT|PATCH api/users/{user}
DELETE   api/users/{user}
```

### 3.6 Optimizaciones de rendimiento (2026-03-14)

Commit `44bc2197`:

- N+1 corregidos en PublicCategoryController: `with('products/parent/children')` reducido a columnas esenciales (de ~151 queries a 4 por request).
- Indices de rendimiento agregados (migracion `add_performance_indexes_to_core_tables`):
  - `stores`: `is_visible`
  - `products`: `(store_id, stock)` compuesto, `offer`
  - `orders`: `(store_id, status)` compuesto, `(store_id, created_at)` compuesto
  - `inventory_movements`: `(product_id, created_at)` compuesto
- Cache file-driver (TTL 300s) en endpoints publicos:
  - `GET /api/public/stores` → `public_stores_list`
  - `GET /api/public/products` → `public_products_{hash}`
  - `GET /api/public/categories` → `public_categories_list`
  - Con invalidacion automatica en create/update/destroy de cada recurso.
- Fix `image_url` en `PublicProductController` (commit `e450c0b5`).

### 3.7 Metricas del repositorio

| Metrica | Valor |
|---|---|
| Modelos Eloquent | 40 |
| Controladores API | 50 |
| Servicios | 11 |
| Migraciones | 99 |
| Rutas totales Laravel | 173 |
| Rutas API | 143 |
| Componentes React | 45+ |
| Paginas React | 13 |
| Rutas frontend (publicas + protegidas) | 55+ |

### 3.8 Dependencias QA detectadas

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

### 3.9 Modelos en app/Models/ (inventariados 2026-03-24)

```text
ActivityLog, AiMetricCache, AutoRestockSetting, Cart, CartProduct, Category,
Channel, Claim, CreditAccount, CreditTransaction, Customer, InventoryMovement,
Location, Notification, Order, OrderMessage, OrderPickingEvent, OrderPickingSession,
OrderProduct, Product, ProductAlert, ProductCode, ProductSupplier, Profile,
PublicStore, PurchaseRequest, PurchaseRequestItem, Rating, Role, Sale,
SalesReport, Setting, StockPrediction, Store, StoreCounter, StoreTaxSetting,
StoreVerification, Tutorial, User, UserSubscription
```

Total: 40 modelos.

### 3.10 Controladores API en app/Http/Controllers/Api/ (inventariados 2026-03-24)

Raiz:

```text
AuthController, BarcodeController, CartController, CartProductController,
CategoryController, ChannelController, ClaimController, CreditController,
CustomerController, DemoImageController, ExternalProductController,
HeroImageController, InventoryController, InventoryDecisionsController,
LocationController, NotificacionController, OrderController, OrderMessageController,
OrderProductController, ProductAlertController, ProductController, ProfileController,
PruebaController, PublicCategoryController, PublicProductController, PublicStoreController,
PurchaseRequestController, RatingController, ReportController, ReportsAlertsController,
ReportsTrendsController, RoleController, SaleController, SettingController,
SettingsController, StatsController, StoreController, StoreVerificationController,
SubscriptionController, TaxSettingController, TutorialController, UploadController,
UserController
```

Subdirectorio Merchant/:

```text
AutoRestockController, InventoryReceiveController, LiveMetricsController,
MerchantStoreController, OrderPickingController, ProductCodeLookupController
```

Subdirectorio Payment/:

```text
MercadoPagoController
```

Total: 50 controladores API.

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
  - auto-restock (sugerencias, config por producto, solicitudes): implementado (`/dashboard/inventory/restock`, `/api/merchant/restock/*`).
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
- Checkout (datos comprador + metodo de pago + MercadoPago preference): implementado.
- Confirmacion/factura de compra: implementado (`/checkout/success`, `/checkout/result` + `GET /api/orders/{id}`).
- Registro/Login client: implementado.
- Historial de pedidos client (vista dedicada): implementado (`/orders/history`, consume API `/api/orders`).
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
- `MERCADOPAGO_*`
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
| Auto-restock (sugerencias, config, solicitudes) | EXISTE | `/dashboard/inventory/restock`, `/api/merchant/restock/*` |
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
| Checkout (datos comprador + metodo pago) | EXISTE | `/checkout`, `POST /api/payments/create-preference` |
| Confirmacion success/factura | EXISTE | `/checkout/success`, `GET /api/orders/{id}` |
| Registro/Login client | EXISTE | `/register`, `/login` |
| Historial de pedidos client (vista dedicada) | EXISTE | `/orders/history`, consume API `/api/orders` |
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

## 9) Flujo operativo final (release - 2026-03-06)

### 9.1 Rama oficial de produccion

- Rama oficial unica: master.
- Estado objetivo para cierre de release:
  - git status --short vacio.
  - git rev-list --left-right --count origin/master...HEAD = 0 0.

### 9.2 Ejecucion local correcta (sin confusion de instancias)

- Frontend React y redisenos dashboard:
  - http://localhost:5173
  - comando (raiz): `npm run dev --prefix comercio-plus-frontend`
  - comando (en comercio-plus-frontend): `npm run dev`
- Backend/API local + vistas legacy:
  - http://127.0.0.1:8000
  - se usa para API Laravel y legacy, no para validar redisenos React.

### 9.3 Flujo correcto de despliegue

1. git fetch origin --prune
2. git pull --ff-only origin master
3. git push origin master
4. Verificar Vercel: ./comercio-plus-frontend/vercel-check.ps1 -BaseUrl https://comercio-plus-oficial.vercel.app
5. Verificar Railway: /api/health, /api/public/stores, CORS OPTIONS.

### 9.4 Como evitar ver builds viejos

- Confirmar URL correcta antes de probar (:5173 vs :8000).
- Hacer hard refresh (Ctrl+F5) o incognito.
- No ejecutar `npm ci` junto con lint/build/dev en paralelo.
- Validar hashes de assets en Vercel si hay dudas de cache.

### 9.5 Checklist post-deploy

1. master local == origin/master.
2. GET /dashboard/products en Vercel = 200.
3. GET /dashboard/products/create en Vercel = 200.
4. GET /dashboard/reports en Vercel = 200.
5. En bundles de Vercel aparecen:
   - Productos e inventario
   - IA comercial y reportes
   - Centro inteligente de decisiones
6. Railway GET /api/health = 200.
7. CORS preflight (OPTIONS) responde 204 para origen Vercel.

## 10) Historial de actualizaciones

| Fecha | Cambios |
|---|---|
| 2026-03-05 | Creacion inicial. Inventario FASE 0: Laravel 11.47.0, 160 rutas totales, 130 API. Tests 121 passed (402 assertions). |
| 2026-03-06 | FASE 5-7: re-ejecucion tests 123 passed (407 assertions), rutas 165 totales / 135 API. Deploy produccion verificado. |
| 2026-03-13 | Actualizacion completa: rutas 173 totales / 143 API. Pagos migrados de Wompi a MercadoPago. Nuevas rutas: merchant/live-metrics, merchant/restock/*, reports/alerts, reports/inventory-decisions, reports/trends, profile, settings, merchant/picking/events. Inventario modelos (41) y controladores (50). Frontend: React 19, Vite 7, TypeScript 5.9. lint FAIL (2 errores en CheckoutResult.tsx). Build PASS. Node v22.22.1. |
| 2026-03-15 | Re-inventario: rutas 173/143 (sin cambio). Modelos corregido a 40 (conteo anterior erroneo). PruebaController agregado al listado de controladores. 3 nuevas rutas React: `/checkout/result`, `/orders/history`, `/dashboard/inventory/restock`. Feature "Historial pedidos client" cambia de NO EXISTE a EXISTE. Drift produccion: `hero-images` resuelto (200), `barcode/search` sigue 404. Tests 123 passed (407 assertions). Lint PASS. Build PASS (7.82s). |
| 2026-03-24 | Actualizacion completa post-optimizacion. Documentada auditoria de rendimiento: correccion N+1 en PublicCategoryController (~151 queries → 4), indices de BD en stores/products/orders/inventory_movements, cache file-driver TTL 300s en endpoints publicos con invalidacion automatica. Fix image_url en PublicProductController. Fix lint frontend. Migraciones totales: 99. Nuevo componente: LowStockAlert.tsx. Metricas del repositorio agregadas. hero-images drift resuelto confirmado. |