<!-- Documento generado para consumo de IA/Claude -->
<!-- Fuente: docs/UNIVERSAL_COMERCIOPLUS.md -->
<!-- Regla: contiene solo estado vigente del proyecto; sin dumps historicos -->
# UNIVERSAL_COMERCIOPLUS

Estado: ACTIVO (fuente unica de verdad)
Ultima actualizacion: 2026-03-04

## Uso para IA (obligatorio)

- Este documento contiene solo estado vigente del proyecto (sin dumps historicos).
- Puede compartirse directamente con Claude/Codex para ejecucion tecnica sin contexto legacy.
- `docs/UNIVERSAL_COMERCIOPLUS_AI.md` se mantiene como copia limpia sincronizada para IA/reportes.

## 1) Vision y alcance del producto

ComercioPlus es una plataforma ecommerce enfocada en comercios en Colombia. El producto cubre dos experiencias principales:

- Cliente: descubrir tiendas y productos, agregar al carrito, iniciar checkout y pago.
- Comerciante: operar tienda, catalogo, pedidos, inventario, picking y reportes.

Este documento consolida estado real del codigo actual. Si hay conflicto con documentos antiguos, gana el codigo del repositorio.

## 2) Stack tecnico real (implementado)

- Backend: Laravel (PHP), Sanctum, MySQL.
- Frontend activo: React + Vite + TypeScript + Tailwind.
- Frontend legacy: Vue (resources/js) mantenido por compatibilidad historica.
- Pagos: Wompi (API + webhook).
- Media: Cloudinary (con fallback a disco local segun configuracion).
- Importacion de inventario: CSV/XLSX con PhpSpreadsheet y template por tienda.
- Deploy frontend: Vercel (rewrites hacia backend).
- Deploy backend: Railway (referenciado en `vercel.json` y docs legacy).

Evidencia:

- `vite.config.js` usa `root: 'comercio-plus-frontend'`.
- `package.json` en raiz usa scripts `npm --prefix comercio-plus-frontend ...`.
- `routes/api.php` incluye auth API, catalogo, merchant y Wompi.
- `config/services.php` incluye bloques `wompi` y `cloudinary`.

## 3) Estructura del repositorio (arbol resumido)

```text
comercioPlusOficial/
|-- app/
|   |-- Http/
|   |   |-- Controllers/
|   |   |-- Middleware/
|   |   `-- Requests/
|   |-- Models/
|   |-- Services/
|   `-- Providers/
|-- comercio-plus-frontend/
|   |-- src/
|   |   |-- app/
|   |   |-- components/
|   |   |-- services/
|   |   |-- lib/
|   |   `-- pages/
|   |-- docs/
|   |   `-- repo-md/
|   `-- vercel.json
|-- config/
|-- database/
|-- public/
|-- resources/
|   `-- js/ (Vue legacy)
|-- routes/
|   |-- api.php
|   |-- auth.php
|   `-- web.php
|-- scripts/
|-- tests/
`-- tests-e2e/
```

## 4) Inventario documental consolidado

Ubicacion auditada inicial: `comercio-plus-frontend/docs/repo-md/*.md`
Ubicacion actual archivada: `docs/_archive/repo-md/*.md`

- Total archivos `.md`: 46
- analisis/auditoria: 4
- contrato/arquitectura: 2
- deploy/config: 2
- integrado/sintesis: 5
- qa/testing: 5
- todo/backlog: 9
- legacy_docs__: 15
- otros: 4

Duplicado detectado:

- `legacy_docs__TODO_COMPREHENSIVE_ANALYSIS.md`
- `TODO_COMPREHENSIVE_ANALYSIS.md`

## 5) Configuracion y variables de entorno (DEV/PROD)

### Backend (.env)

Variables relevantes confirmadas en `.env.example`:

- `APP_URL`
- `DB_*` (MySQL)
- `FRONTEND_URL`
- `VERCEL_PROD_ORIGIN`
- `SANCTUM_STATEFUL_DOMAINS`
- `CORS_ALLOWED_ORIGINS`
- `CORS_ALLOWED_ORIGIN_PATTERNS`
- `WOMPI_*`
- `CLOUDINARY_*`

### Frontend React (comercio-plus-frontend/.env.example)

- `VITE_API_BASE_URL`
- `VITE_API_URL` (alias)
- `VITE_DEV_PROXY_TARGET`
- `VITE_APP_NAME`
- `VITE_CLOUDINARY_CLOUD_NAME`
- `VITE_CLOUDINARY_UPLOAD_PRESET`

### Regla actual de runtime para API base URL

Implementado en `comercio-plus-frontend/src/lib/runtime.ts`:

- DEV: si falta `VITE_API_BASE_URL`, usa `/api`.
- PROD: si falta `VITE_API_BASE_URL`, NO usa fallback implicito a current origin; deja config invalida y expone error (`API_CONFIG_OK=false`).

## 6) Backend: rutas, controllers, middleware y auth

### 6.1 Auth web (sesion Laravel)

Definido en `routes/auth.php` con `Auth\AuthenticatedSessionController`:

- `GET /login`
- `POST /login`
- `POST /logout`
- `GET|POST /register`
- `GET|POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password`

Definido en `routes/web.php`:

- `GET /dashboard` protegido por `auth` y `verified`.
- `GET/PATCH/DELETE /profile` protegido por `auth`.

### 6.2 Auth API (token Sanctum)

Definido en `routes/api.php` y `App\Http\Controllers\Api\AuthController`:

- Publico:
- `POST /api/login`
- `POST /api/register`
- `GET /api/login` -> 405 JSON (mensaje explicito)
- `GET /api/register` -> 405 JSON (mensaje explicito)
- Protegido `auth:sanctum`:
- `GET /api/me`
- `POST /api/logout`

Contrato real de login (`AuthController@login`):

- credenciales invalidas: `401` + `{ "message": "Credenciales invalidas" }`
- exito: `200` + `{ "user": {...}, "token": "..." }`

Contrato real de `/api/me`:

- sin token o token invalido: `401` + `{"message":"Unauthenticated."}`
- con token valido: `200` + `{id,name,email,phone,role,has_store,store_id}`
- rutas API no autenticadas no redirigen a `/login` (middleware `Authenticate` retorna `null` para `api/*`).

### 6.3 API endpoints (mapa real)

Publicos (sin `auth:sanctum`):

- Health: `/api/health`, `/api/health/integrations`
- Auth: `/api/login`, `/api/register`
- Catalogo publico: `/api/public-stores*`, `/api/public/products`, `/api/public/categories`
- Catalogo base: `/api/products*`, `/api/categories*`
- Alertas precio (lectura): `/api/products/{product}/alerts/mine`
- Orden checkout: `POST /api/orders/create`
- Wompi: `/api/payments/wompi/create`, `/webhook`, `/status/{id}`, `/pse-banks`
- Debug runtime (operacion): `GET /api/_debug/env`

Protegidos (`auth:sanctum`):

- Auth user: `/api/me`, `/api/logout`
- Store owner: `/api/my/store`, `/api/stores*`
- Tax settings/reportes/inventario/reorder por tienda
- Merchant dashboard:
- `/api/merchant/orders*`
- `/api/merchant/orders/{order}/picking*`
- `/api/merchant/picking/events`
- `/api/merchant/customers`
- `/api/merchant/credit*`
- `/api/merchant/store`
- `/api/merchant/store/verification`
- `/api/merchant/dashboard` (alias de resumen)
- `/api/merchant/stats`
- `/api/reports/*`
- `/api/inventory/*`
- `/api/merchant/inventory/*`
- `/api/merchant/products/lookup-code`
- Gestion de catalogo:
- `POST|PUT|DELETE /api/products*`
- `POST|PUT|DELETE /api/categories*`
- Uploads:
- `/api/uploads/products`
- `/api/uploads/stores/logo`
- `/api/uploads/stores/cover`
- `/api/uploads/profiles/photo`
- Alertas precio (escritura):
- `POST /api/products/{product}/alerts`
- `DELETE /api/products/{product}/alerts`
- Recursos:
- `/api/users`
- `/api/cart`, `/api/cart/count`, `/api/cart/clear`
- `/api/cart-products`
- `/api/orders`
- `/api/subscriptions`

## 7) Frontend: rutas, servicios y auth flow

### 7.1 Source of truth frontend

Implementado:

- Frontend activo: React (`comercio-plus-frontend/src`).
- Frontend legacy: Vue (`resources/js`) sigue en repo y no se elimina en esta fase.

Evidencia:

- `vite.config.js` define root React.
- scripts raiz `dev/build/lint` usan `--prefix comercio-plus-frontend`.
- Vue solo por comandos `dev:legacy/build:legacy`.

### 7.2 Mapa real de rutas frontend React

Definidas en `comercio-plus-frontend/src/app/App.tsx`:

- Publicas:
- `/`, `/stores`, `/store/create`, `/store/:id`, `/stores/:storeSlug/products`
- `/how-it-works`, `/products`, `/products/:id`, `/product/:id`
- `/cart`, `/checkout`, `/checkout/success`, `/payment/success`, `/orders/:id`
- `/category/:id`, `/privacy`, `/terms`, y paginas informativas
- Auth layout:
- `/login`, `/register`, `/registro` (redirect), `/forgot-password`
- Protegidas (RequireAuth + RequireRole merchant):
- `/dashboard`
- `/dashboard/customers`
- `/dashboard/credit`
- `/dashboard/store`
- `/dashboard/settings`
- `/dashboard/orders`
- `/dashboard/orders/:id/picking`
- `/dashboard/inventory`
- `/dashboard/inventory/import`
- `/dashboard/inventory/receive`
- `/dashboard/reports`
- `/dashboard/categories`
- `/dashboard/products`
- `/dashboard/products/create`
- `/dashboard/products/:id/edit`

### 7.3 Mapa real de rutas Vue legacy

Definidas en `resources/js/router/index.js`:

- `/`, `/stores`, `/stores/create`, `/products`, `/product/:slug`
- `/cart`, `/checkout`, `/orders`
- `/login`, `/register`
- `/profile`, `/settings`

### 7.4 Flujo de autenticacion frontend React

Implementado:

- Login page usa `API.post('/login')`.
- Si hay token, ejecuta `hydrateSession(token, remember, data.user)` para intentar `/me`.
- `RequireAuth` valida token/user y recupera sesion con `/me` cuando aplica.
- `RequireRole` valida rol merchant/client.
- Interceptor API en 401:
- limpia sesion
- redirige a `/login` solo si ruta protegida y no esta ya en `/login`

Variables clave:

- `API_BASE_URL`
- `API_CONFIG_OK`
- `AUTH_API_CONFIG_MISSING` (error controlado en PROD sin env)

## 8) Integraciones: Wompi, Cloudinary, CORS y Deploy

### 8.1 Wompi

Implementado:

- Controller: `app/Http/Controllers/Api/WompiController.php`
- Metodos: `createOrder`, `createPayment`, `webhook`, `getTransactionStatus`, `getPseBanks`
- Config: `config/services.php` (`wompi.*`)

### 8.2 Cloudinary

Implementado:

- Upload API: `app/Http/Controllers/Api/UploadController.php`
- Servicio: `app/Services/CloudinaryService.php`
- Comportamiento: upload a Cloudinary cuando hay config valida; fallback a storage local cuando no.

### 8.3 CORS y Sanctum

Implementado:

- `config/cors.php` incluye `api/*`, `sanctum/csrf-cookie`, `login`, `logout`
- origanes permitidos dinamicos con `FRONTEND_URL`, `VERCEL_PROD_ORIGIN`, `CORS_ALLOWED_ORIGINS`
- `supports_credentials` habilitado
- `config/sanctum.php` esta orientado a auth por Bearer token

### 8.4 Deploy frontend/backend

Implementado:

- `comercio-plus-frontend/vercel.json` reescribe:
- `/api/*` -> Railway backend `/api/*`
- `/sanctum/*` -> Railway
- `/storage/*` -> Railway
- `Dockerfile` de Railway instala extension `gd` para soporte XLSX (PhpSpreadsheet).
- Startup backend ejecuta `php artisan optimize:clear` antes de levantar servidor PHP embebido.

## 9) Flujos del producto (estado actual)

### 9.1 Cliente (implementado en rutas y API)

- Navega tiendas y productos publicos.
- Agrega al carrito.
- Inicia checkout.
- Crea orden (`POST /api/orders/create`).
- Flujo de pago Wompi disponible por endpoints API.
- Puede usar canal de compra `web` o `whatsapp` (registrado en orden).
- Puede crear/eliminar alertas de precio por producto autenticado.
- Puede contactar la tienda desde boton directo de WhatsApp en vistas publicas.

### 9.2 Comerciante (implementado)

- Login por token.
- Gestiona tienda (`/api/my/store`, `/api/stores*`).
- Gestiona tienda merchant por endpoint dedicado (`/api/merchant/store`).
- Gestiona configuracion comercial: categoria, horario, moneda, metodos de pago, impuestos.
- Gestiona productos y categorias.
- Opera pedidos y estados de pedidos.
- Usa picking (scan/manual/fallback/complete/reset).
- Consulta reportes e inventario.
- Opera modulo de fiado digital (`/api/merchant/credit*`) con cargos y abonos.
- Gestiona solicitud de verificacion documental de tienda (`/api/merchant/store/verification`).
- Cuenta con QR descargable para compartir URL publica de su tienda.

### 9.3 Inventario y pedidos (implementado)

- Ajustes inventario por endpoints de inventario.
- Recepcion inventario con scan-in y create-from-scan.
- Picking events para trazabilidad operativa.
- Importacion masiva inventario con preview/import/template por tienda (`CSV/XLSX`, `upsert` opcional).
- Normalizacion de columnas flexibles por alias (sku, marca, unidad, costos, categoria, metadata).
- KPIs profesionales de inventario (valor total, bajo stock, agotados, estado de stock).

## 10) QA: pruebas manuales y automatizadas

### 10.1 Automatizadas (evidencia real)

- Laravel Feature tests:
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- suites API y flujos en `tests/Feature/*`
- Playwright:
- `tests-e2e/*`
- config en `playwright.config.ts`

Comandos:

- `php artisan test`
- `npm run test:e2e`
- `npm run test:e2e:headed`

### 10.2 Smoke checklist manual

- Auth API:
- `POST /api/login` con credenciales invalidas -> 401
- `POST /api/login` con credenciales validas -> token + user
- `GET /api/me` con token -> 200
- `GET /api/me` sin token -> 401
- Frontend:
- login react
- persistencia remember on/off
- acceso rutas protegidas con/ sin token
- logout limpia storage y sesion
- Inventario import:
- descarga de template `/api/inventory/template`
- preview archivo `/api/inventory/preview`
- import archivo `/api/inventory/import` con `upsert` on/off
- Fiado y verificacion:
- listar/crear cuenta fiado `/api/merchant/credit`
- registrar cargo/abono en cuenta
- enviar documento de verificacion `/api/merchant/store/verification`

## 11) Troubleshooting (errores frecuentes)

- Error: falta `VITE_API_BASE_URL` en produccion.
- Sintoma: login no debe seguir haciendo requests; UI muestra error de configuracion.
- Solucion: definir `VITE_API_BASE_URL` en Vercel.

- Error: `Unauthenticated.` en `/api/me`.
- Causa: token ausente/corrupto/expirado.
- Solucion: limpiar storage y relogin.

- Error 503 relacionado a `personal_access_tokens` o DB.
- Causa: migraciones/db incompletas.
- Solucion: ejecutar migraciones y verificar conexion DB.

- Error CORS.
- Causa: origen frontend no incluido en backend.
- Solucion: revisar `FRONTEND_URL`, `VERCEL_PROD_ORIGIN`, `CORS_ALLOWED_ORIGINS`.

- Error importacion XLSX en Railway (PhpSpreadsheet).
- Causa: imagen de PHP sin extension `gd`.
- Solucion: usar Dockerfile actual con `docker-php-ext-install gd`.

- Error API devuelve redirect a `/login` en vez de JSON.
- Causa: middleware auth web interceptando requests API.
- Solucion: `Authenticate::redirectTo` retorna `null` para `api/*` y mantiene 401 JSON.

## 12) Estado de features: implementado vs plan

### Implementado

- Auth web por sesion (Laravel).
- Auth API por token (Sanctum).
- Frontend React activo con rutas publicas, auth y dashboard merchant.
- Frontend Vue legacy aun presente.
- Catalogo, carrito, checkout y endpoints de pagos Wompi.
- Uploads con Cloudinary/local.
- Inventario, picking y reportes en API.
- Importador de inventario con template por tienda, preview e import CSV/XLSX con `upsert`.
- Modulo de fiado digital (credit accounts + credit transactions) y UI merchant `/dashboard/credit`.
- Verificacion de tienda con soporte documental y bandera `is_verified`.
- Alertas de precio por producto + comando de chequeo `alerts:check-prices`.
- Canal de pedidos (`web`, `whatsapp`, `local`) persistido en orden.
- Ruta merchant dedicada para configuracion de tienda (`/api/merchant/store`) y alias `/api/merchant/dashboard`.

### PLAN / PROPUESTA (NO IMPLEMENTADO)

Items identificados solo en documentos historicos `repo-md`:

- traduccion completa de toda la app (`TODO_TRADUCCION_ESPANOL.md`)
- pruebas exhaustivas de todas las vistas (`TODO_TESTING_VIEWS.md`, `TODO_PRUEBAS_EXHAUSTIVAS.md`)
- ajustes de panel comerciante legacy (`TODO_PANEL_COMERCIANTE.md`)
- analisis/diseno adicionales (`TODO_DESIGN_FRONTEND_ANALYSIS.md`, `TODO_COMPREHENSIVE_ANALYSIS.md`)

Estos items no se consideran implementados sin evidencia adicional en codigo.

## 13) Roadmap y backlog vigente (priorizado)

### P0 (operacion/estabilidad)

- consolidar documentacion oficial y gobernanza (este documento + politica)
- mantener validacion de auth/config de API en prod
- endurecer o limitar `GET /api/_debug/env` para ambientes no DEV
- monitorear importacion de inventario en produccion (errores por fila y volumen)

### P1 (calidad)

- ampliar cobertura E2E de login, checkout, dashboard merchant y fallos de red
- agregar smoke automatizado para `/api/login` y `/api/me` en CI
- agregar pruebas API para `/api/inventory/preview`, `/api/inventory/import` y `/api/inventory/template`
- agregar pruebas API para flujos de fiado y verificacion de tienda

### P2 (producto)

- cerrar deuda de TODO historicos que sigan vigentes tras revision funcional
- evolucionar alertas de precio a notificacion multicanal (mail/whatsapp/push)
- depurar/retirar rutas o vistas legacy Vue cuando exista plan de deprecacion aprobado

## 14) Migracion documental a `docs/_archive/` (completada)

Objetivo: dejar solo 1 documento oficial activo.

### 14.1 Estructura objetivo

```text
docs/
|-- UNIVERSAL_COMERCIOPLUS.md
|-- DOC_GOVERNANCE.md
|-- README.md
`-- _archive/
    `-- repo-md/
```

### 14.2 Ejecucion realizada

Fase A - Preparacion (ejecutada):

- crear `docs/_archive/repo-md/`
- congelar creacion de nuevos `.md` en `comercio-plus-frontend/docs/repo-md`

Fase B - Migracion (ejecutada):

- se movieron 46 `.md` de `comercio-plus-frontend/docs/repo-md/` a `docs/_archive/repo-md/`
- conservar nombres originales para trazabilidad

Fase C - Post migracion (ejecutada):

- actualizar links en README (raiz y frontend) para apuntar al universal
- marcar cualquier doc historico con encabezado de archivo legacy

### 14.3 Lista de archivos movidos

- `.env_example.md`
- `ANALISIS_COMPLETO.md`
- `API.md`
- `ComercioPlus_Frontend_Auditoria_Estructural.md`
- `ComercioPlus_Frontend_Contrato_Tecnico.md`
- `DOCS_SIMILITUD_E_INTEGRACION.md`
- `EXECUTION_PLAN_SENIOR.md`
- `GUIA_LARAVEL_WOMPI.md`
- `INFORME_COMPLETO_APLICACION.md`
- `INFORME_GENERAL_PROYECTO.md`
- `INTEGRADO_ANALISIS_PLAN_QA.md`
- `INTEGRADO_DEPLOY_MEDIA.md`
- `INTEGRADO_PICKING.md`
- `INTEGRADO_TESTING_QA.md`
- `INTEGRADO_WOMPI_ECOMMERCE.md`
- `legacy_docs__blueprint.md`
- `legacy_docs__cloudinary-production-checklist.md`
- `legacy_docs__cloudinary-uploads.md`
- `legacy_docs__DatabaseInsertInstructions.md`
- `legacy_docs__deploy-railway-vercel-cloudinary.md`
- `legacy_docs__e2e-playwright.md`
- `legacy_docs__inventory-scan-in-audit.md`
- `legacy_docs__picking-api-contract.md`
- `legacy_docs__picking-claude-design-handoff.md`
- `legacy_docs__picking-phase-plan-prompt.md`
- `legacy_docs__picking-runbook.md`
- `legacy_docs__picking-state-machine.md`
- `legacy_docs__postman_user_api_examples.md`
- `legacy_docs__stock-policy-options.md`
- `legacy_docs__TODO_COMPREHENSIVE_ANALYSIS.md`
- `MIGRATIONS_NOTES.md`
- `PLAN_PRUEBAS_EXHAUSTIVAS_ESPAÃƒâ€˜OL.md`
- `PLAN_PRUEBAS_VISTAS_VUE.md`
- `QA_E2E_REPORT.md`
- `QA_REPORT_AUTOMATICO_FULLFLOW.md`
- `QA_REPORT_COMERCIOPLUS.md`
- `README.md`
- `TODO.md`
- `TODO_API_FIXES.md`
- `TODO_COMPREHENSIVE_ANALYSIS.md`
- `TODO_DESIGN_FRONTEND_ANALYSIS.md`
- `TODO_IMPLEMENTATION.md`
- `TODO_PANEL_COMERCIANTE.md`
- `TODO_PRUEBAS_EXHAUSTIVAS.md`
- `TODO_TESTING_VIEWS.md`
- `TODO_TRADUCCION_ESPANOL.md`

## 15) Anexos (referencias de evidencia)

Rutas y auth:

- `routes/api.php`
- `routes/web.php`
- `routes/auth.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Middleware/Authenticate.php`
- `app/Http/Controllers/Api/CreditController.php`
- `app/Http/Controllers/Api/ProductAlertController.php`
- `app/Http/Controllers/Api/StoreVerificationController.php`
- `app/Http/Controllers/Api/Merchant/MerchantStoreController.php`
- `app/Console/Commands/CheckPriceAlertsCommand.php`

Frontend auth/runtime:

- `comercio-plus-frontend/src/app/App.tsx`
- `comercio-plus-frontend/src/app/login/page.tsx`
- `comercio-plus-frontend/src/services/api.ts`
- `comercio-plus-frontend/src/services/auth-session.ts`
- `comercio-plus-frontend/src/components/auth/RequireAuth.tsx`
- `comercio-plus-frontend/src/components/auth/RequireRole.tsx`
- `comercio-plus-frontend/src/lib/runtime.ts`

Integraciones:

- `app/Http/Controllers/Api/WompiController.php`
- `app/Http/Controllers/Api/UploadController.php`
- `app/Http/Controllers/Api/InventoryController.php`
- `app/Services/CloudinaryService.php`
- `app/Services/InventoryImportService.php`
- `config/services.php`
- `config/cors.php`
- `comercio-plus-frontend/vercel.json`
- `Dockerfile`

Migraciones recientes:

- `database/migrations/2026_02_27_000001_add_channel_to_orders_table.php`
- `database/migrations/2026_02_27_100001_create_credit_accounts_table.php`
- `database/migrations/2026_02_27_100002_create_credit_transactions_table.php`
- `database/migrations/2026_02_27_100003_add_is_verified_to_stores_table.php`
- `database/migrations/2026_02_27_100004_create_store_verifications_table.php`
- `database/migrations/2026_02_27_100005_create_product_alerts_table.php`
- `database/migrations/2026_03_01_100001_add_merchant_settings_to_stores_table.php`
- `database/migrations/2026_03_03_120000_add_sku_brand_metadata_to_products_table.php`
- `database/migrations/2026_03_03_230000_add_professional_inventory_columns_to_products_table.php`

Testing:

- `tests/Feature/Auth/*`
- `tests/Feature/*`
- `tests-e2e/*`
- `playwright.config.ts`

## 16) Historial de cambios

- 2026-03-04 - Actualizacion integral del UNIVERSAL con avances hasta 2026-03-03.
- 2026-03-03 - Inventario: template flexible por tienda, preview/import robusto, soporte duplicados SKU con upsert y detalle 422 en UI.
- 2026-03-03 - Inventario: nuevas columnas profesionales en `products` (`unit`, `ref_adicional`, `sale_price`, `sku`, `brand`, `metadata`).
- 2026-03-03 - Backend API: fix middleware auth para evitar redirects a `/login` en rutas `api/*` no autenticadas.
- 2026-03-03 - Dashboard merchant: ruta alias `/api/merchant/dashboard`, ruta `/dashboard/inventory/import` y mejoras UX en productos/inventario.
- 2026-03-03 - Deploy Railway: habilitada extension PHP `gd` y clear de cache en boot para runtime consistente.
- 2026-03-02 - Operacion: endpoint `GET /api/_debug/env` para diagnostico de variables y estado de config.
- 2026-03-01 - Merchant store: endpoints dedicados `/api/merchant/store` (show/update) y nuevos campos comerciales de tienda.
- 2026-02-27 - Q2: fiado digital, verificacion de tienda, alertas de precio, mejoras de tienda publica.
- 2026-02-27 - Q1: canal de pedidos (`web|whatsapp|local`), boton WhatsApp y QR descargable de tienda.
- 2026-02-27 - Primera consolidacion universal basada en codigo real + plan de migracion documental.

