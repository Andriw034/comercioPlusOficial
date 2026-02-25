<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# QA E2E Report - ComercioPlus

Fecha: 2026-02-23
Rol de ejecucion: QA Lead + Full-Stack Senior

## 1) Resumen ejecutivo
- Estado general: **estable para avanzar a produccion controlada** en flujo core API (auth, tienda, productos, inventario, pedido, merchant orders).
- Backend: `php artisan test` ejecutado completo -> **113 PASS / 0 FAIL**.
- Frontend build: `npm run build` -> **PASS**.
- Persistencia real Railway validada con flujo API end-to-end y consultas DB.
- Limitante actual: pruebas browser E2E con Playwright no se pudieron correr en este entorno por cache offline de npm.
- Hallazgo funcional relevante de negocio: el descuento de stock OUT **sigue ocurriendo al pasar pedido a `paid`**, no en `complete picking` (coincide con implementacion actual y tests).

## 2) Fase 0 - Inventario tecnico (PASS)

### 2.1 Rutas UI clave (React Router)
Archivo: `comercio-plus-frontend/src/app/App.tsx`
- Publicas: `/`, `/stores`, `/store/:id`, `/stores/:storeSlug/products`, `/products`, `/products/:id`, `/cart`, `/checkout`, `/checkout/success`, `/category/:id`, `/privacy`, `/terms`, etc.
- Auth: `/login`, `/register`, `/registro` (alias), `/forgot-password`.
- Dashboard merchant (protegido):
  - `/dashboard`
  - `/dashboard/store`
  - `/dashboard/products`, `/dashboard/products/create`, `/dashboard/products/:id/edit`
  - `/dashboard/orders`, `/dashboard/orders/:id/picking`
  - `/dashboard/inventory`, `/dashboard/inventory/receive`
  - `/dashboard/categories`, `/dashboard/customers`, `/dashboard/reports`, `/dashboard/settings`

### 2.2 Endpoints API clave
Fuente: `routes/api.php` + `php artisan route:list --path=api`
- Auth: `POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/me`
- Store: `GET /api/my/store`, `POST /api/stores`, `PUT /api/stores/{store}`
- Productos: `GET /api/products`, `POST /api/products`, `PUT /api/products/{product}`
- Product codes: `POST /api/merchant/products/lookup-code`
- Inventario IN: `POST /api/merchant/inventory/scan-in`, `POST /api/merchant/inventory/create-from-scan`, `GET /api/merchant/inventory/movements`
- Inventario general: `GET /api/inventory/summary`, `GET /api/inventory/movements`, `POST /api/inventory/adjust`
- Carrito: `/api/cart*`, `/api/cart-products*`
- Pedidos: `/api/orders*`, `/api/merchant/orders*`, picking `/api/merchant/orders/{order}/picking/*`
- Checkout Wompi: `/api/orders/create`, `/api/payments/wompi/*`

### 2.3 Modelos/relaciones principales
- `User` -> `store()`, `orders()`, `carts()`
- `Store` -> `products()`, `categories()`, `inventoryMovements()`
- `Product` -> `store()`, `category()`, `productCodes()`, `inventoryMovements()`
- `ProductCode` -> `belongsTo product/store`
- `Order` -> `ordenproducts()` (`OrderProduct`), `store()`, `user()`
- `InventoryMovement` -> ledger de entradas/salidas/ajustes

### 2.4 Tablas principales detectadas
- `users`, `stores`, `products`, `product_codes`
- `inventory_movements`
- `carts`, `cart_products`
- `orders`, `order_products`
- `order_picking_events`, `order_picking_sessions`

## 3) Fase 1 - Auth merchant/client (PASS)
Evidencia:
- Tests: `AuthSanctumTest`, `AuthenticationTest`, `RegistrationTest` -> PASS.
- Flujo API real (Railway) ejecutado:
  - `merchant_register_ok status=201`
  - `client_register_ok status=201`

Validacion DB Railway:
- Conteos `users` incrementaron (de 5 a 7) tras flujo real.

## 4) Fase 2 - Crear tienda + logo/portada (PASS con nota)
Evidencia:
- Test `StoresApiTest` -> PASS.
- Flujo API real:
  - `store_create_ok id=6`

Validacion DB Railway:
- `stores` incremento (de 1 a 2).

Nota Cloudinary:
- Variables Wompi/Cloudinary en `.env` estan vacias en este entorno.
- Store/media funciona por URL directa y fallback local; integracion Cloudinary productiva no fue validada end-to-end aqui.

## 5) Fase 3 - Crear producto manual (PASS)
Evidencia:
- `ProductApiTest` PASS (incluye create/update/delete y codigos).
- Flujo API real:
  - `category_create_ok id=7`
  - `product_create_ok id=2`

Validacion DB Railway:
- `products` incremento (1 -> 2)
- `product_codes` incremento (1 -> 2)

## 6) Fase 4 - Crear producto por escaneo (PASS parcial UI)
Backend/API:
- `InventoryReceiveApiTest` PASS (scan-in existente, idempotencia request_id, not found, create-from-scan).
- `ProductCodeLookupApiTest` PASS.

Frontend:
- Vista implementada en `/dashboard/inventory/receive` con:
  - input scanner dominante
  - qty stepper/presets
  - quick create modal en `PRODUCT_NOT_FOUND`
  - historial de ingresos

Limitacion de prueba UI automatica:
- `npx playwright test` no ejecutable por restriccion de cache/npm offline en este entorno.

## 7) Fase 5 - Inventario/stock/contador (PASS)
Evidencia:
- Test integrado: `FullCommerceFlowTest` PASS.
- Flujo API real:
  - Producto creado con stock 7
  - Pedido por 2 unidades
  - Cambio estado pedido a `paid`
  - `product_stock_after=5`

Validacion DB Railway:
- `inventory_movements` incremento (0 -> 1)
- Registro `sale` con `quantity=-2`, `stock_after=5`, `reference_type=order`, `reference_id=4`.

## 8) Fase 6 - Vistas publicas cliente (PASS tecnico)
Evidencia:
- Tests `HomePageTest`, `WebRoutesTest`, `BasicApiTest` -> PASS.
- Endpoints publicos de tiendas/productos responden OK.

Nota:
- Verificacion visual browser automatizada pendiente por limitacion Playwright en este entorno.

## 9) Fase 7 - Carrito (PASS backend / frontend localStorage)
Evidencia:
- `CartApiTest` PASS.
- `CheckoutFlowTest` PASS (add to cart via API).

Hallazgo de arquitectura:
- Frontend usa `CartContext` con persistencia en `localStorage` (no usa backend cart como fuente unica).
- Esto funciona, pero para multi-dispositivo no hay sincronizacion server-side real del carrito UI.

## 10) Fase 8 - Checkout / pedido (PASS)
Evidencia:
- `OrderStatusFlowTest` PASS.
- Flujo API real:
  - `order_create_ok id=4`
  - pedido ligado a `store_id=6`, `user_id=34`, `order_products` con cantidades correctas.

Validacion DB Railway:
- `orders` incremento (0 -> 1)
- `order_products` incremento (0 -> 1)

## 11) Fase 9 - Dashboard merchant pedidos + picking (PASS)
Evidencia:
- `OrderPickingApiTest` PASS (show, scan, fallback 3 intentos, manual, complete, events).
- Flujo API real:
  - `merchant_orders_ok count=1`
  - update status a paid OK.

Nota de negocio importante:
- Implementacion actual: stock descuenta en transicion de estado a pagado (`OrderObserver` + `InventoryService`), **no en `complete picking`**.
- Esto esta probado y consistente con `FullCommerceFlowTest`.

## 12) Incidencias encontradas y correcciones aplicadas

### 12.1 Sidebar no mostraba nombre/logo/portada de mini tienda
- Problema: header lateral fijo en “ComercioPlus”.
- Causa: `Sidebar` no consumia `store.name`/`cover` y nombre estaba hardcodeado.
- Correccion:
  - `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`
  - `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx`
- Resultado: tras crear/editar tienda, el sidebar refleja nombre/logo/portada reales y se actualiza por evento `store:updated`.

### 12.2 Lint error introducido por `return` en `finally`
- Archivo: `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx`
- Error: `no-unsafe-finally`
- Fix: remover `return` dentro de `finally` y condicionar `setIsLoading(false)` con `if (isMounted)`.

## 13) Comandos ejecutados (evidencia)
- `php artisan test` -> **113 passed**
- `npm run build` (root/frontend) -> **PASS**
- `npm run test:e2e` -> **FAIL en este entorno** por `ENOTCACHED` (npm offline/cached-only, sin paquete Playwright disponible).
- `php artisan migrate:status` -> todas las migraciones en estado `Ran`
- `php artisan db:show --counts` -> conteos en Railway (antes/despues)
- Flujo API real con script PowerShell:
  - registro merchant/client
  - create store/category/product
  - create order
  - merchant list orders
  - update status paid
  - verificacion stock final e inventario

## 14) Variables de entorno relevantes
Revisadas en `.env.example` y `config/services.php`:
- Backend:
  - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - `WOMPI_PUBLIC_KEY`, `WOMPI_PRIVATE_KEY`, `WOMPI_EVENTS_SECRET`, `WOMPI_API_URL`
  - `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, `CLOUDINARY_UPLOAD_PRESET`, `CLOUDINARY_URL`
- Frontend:
  - `VITE_API_BASE_URL` / `VITE_API_URL`
  - `VITE_CLOUDINARY_*`

## 14.1 Artefactos agregados para PR#2 (E2E + produccion)
- `playwright.config.ts` ajustado a stack actual (Laravel API + React Vite), con soporte de `E2E_FRONTEND_URL` y `E2E_API_BASE_URL`.
- `tests-e2e/smoke.spec.ts` reescrito con flujo smoke real:
  - register/login merchant
  - create store/category/product (API autenticada)
  - register/login client
  - add cart + checkout (mock solo del endpoint externo de Wompi)
  - validacion de pedido visible para merchant
- Scripts root agregados:
  - `npm run test:e2e`
  - `npm run test:e2e:headed`
- Documentacion nueva:
  - `docs/e2e-playwright.md`
  - `docs/cloudinary-production-checklist.md`
  - `docs/stock-policy-options.md`

## 15) Riesgos residuales para produccion
1. Playwright E2E browser no ejecutado en este entorno (restriccion npm offline).
2. Descuento de stock sigue en `paid` (no en `complete picking`), revisar si coincide con politica final.
3. Carrito frontend persiste en localStorage (no backend first); posible divergencia multi-dispositivo.
4. `npm run lint` global del frontend tiene errores/warnings heredados en modulos fuera del fix puntual.

## 16) Estado final por fase
- Fase 0: PASS
- Fase 1: PASS
- Fase 2: PASS (nota Cloudinary no validado productivo)
- Fase 3: PASS
- Fase 4: PASS backend / PARTIAL UI automation
- Fase 5: PASS
- Fase 6: PASS tecnico / PARTIAL visual automation
- Fase 7: PASS
- Fase 8: PASS
- Fase 9: PASS

---

## Apendice A - Query SQL de validacion usadas
- `SELECT COUNT(*) FROM users;`
- `SELECT COUNT(*) FROM stores;`
- `SELECT COUNT(*) FROM products;`
- `SELECT COUNT(*) FROM product_codes;`
- `SELECT COUNT(*) FROM orders;`
- `SELECT COUNT(*) FROM order_products;`
- `SELECT COUNT(*) FROM inventory_movements;`
- `SELECT id, status, store_id, user_id, total FROM orders ORDER BY id DESC LIMIT 3;`
- `SELECT id, type, quantity, stock_after, reference_type, reference_id FROM inventory_movements ORDER BY id DESC LIMIT 5;`

