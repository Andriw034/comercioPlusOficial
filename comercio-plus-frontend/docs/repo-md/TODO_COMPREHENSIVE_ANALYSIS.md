<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# TODO_COMPREHENSIVE_ANALYSIS

Fecha: 2026-02-25  
Alcance: Auditoria real de ComercioPlus (Laravel API + React/Vite/Tailwind) basada en codigo del repo, sin suposiciones.

---

## 1) Inventario real del proyecto (FASE 0 + FASE 1)

### 1.1 Estructura real del repo
Resultado real de exploracion:
- Backend Laravel en raiz: `artisan`, `composer.json`, `app/`, `routes/`.
- Frontend React separado: `comercio-plus-frontend/package.json`, `comercio-plus-frontend/vite.config.ts`.
- Tambien existe codigo legacy (Blade/Vue) en `resources/`.

Evidencia:
- `package.json:5` y `package.json:6` delegan scripts al frontend React con `--prefix comercio-plus-frontend`.
- `vite.config.js:7` fija `root: 'comercio-plus-frontend'`.
- `resources/js/app.js` y multiples `resources/js/*.vue` siguen presentes.

Conclusion:
- Es monorepo mixto (Laravel + React moderno + legado Vue/Blade).
- Riesgo operativo: coexistencia de dos frontends si no se documenta el camino oficial.

### 1.2 Backend - rutas API reales
Fuente: `routes/api.php:35-198`.

Publicas principales:
- `POST /api/login`, `POST /api/register` (`routes/api.php:75-76`)
- `GET /api/public-stores`, `GET /api/public-stores/{store}` (`routes/api.php:85-86`)
- `GET /api/products`, `GET /api/products/{product}` (`routes/api.php:93-94`)
- `GET /api/categories`, `GET /api/categories/{category}` (`routes/api.php:95-96`)
- `POST /api/orders/create` (`routes/api.php:99`)
- Wompi: `/api/payments/wompi/*` (`routes/api.php:103-106`)

Protegidas con Sanctum:
- Grupo `Route::middleware('auth:sanctum')` (`routes/api.php:114`)
- Sesion: `/api/me`, `/api/logout` (`routes/api.php:115-116`)
- Tienda: `/api/my/store`, `/api/stores`, `/api/stores/{store}` (`routes/api.php:119-122`)
- Merchant pedidos: `/api/merchant/orders`, update status (`routes/api.php:149-150`)
- Picking: `/api/merchant/orders/{order}/picking/*` (`routes/api.php:151-157`)
- Inventario: `/api/inventory/*` + scan IN (`routes/api.php:167-173`)
- Uploads: `/api/uploads/*` (`routes/api.php:182-185`)

### 1.3 Backend - rutas web legacy
Fuentes:
- `routes/web.php:6-20`
- `routes/auth.php:14-59`
- `routes/admin.php:12-27`

Existe autenticacion web de Laravel (session/cookies) en paralelo al flujo API Bearer.

### 1.4 Frontend - rutas React reales
Fuente: `comercio-plus-frontend/src/app/App.tsx:42-155`.

#### Rutas publicas
- `/`, `/stores`, `/store/:id`, `/stores/:storeSlug/products`
- `/products`, `/products/:id`, `/product/:id`
- `/cart`, `/checkout`, `/checkout/success`, `/payment/success`
- `/privacy`, `/terms`, `/how-it-works`, etc.

#### Rutas auth
- `/login`, `/register`, `/registro` (alias a `/register`), `/forgot-password`.

#### Rutas merchant dashboard
Protegidas por:
- `RequireAuth` (`components/auth/RequireAuth.tsx:7-21`)
- `RequireRole role="merchant"` (`components/auth/RequireRole.tsx:28-37`)

Rutas:
- `/dashboard`
- `/dashboard/store`
- `/dashboard/products`, `/dashboard/products/create`, `/dashboard/products/:id/edit`
- `/dashboard/orders`, `/dashboard/orders/:id/picking`
- `/dashboard/inventory`, `/dashboard/inventory/receive`
- `/dashboard/customers`, `/dashboard/categories`, `/dashboard/reports`, `/dashboard/settings`

### 1.5 Tabla ruta -> vista -> layout -> auth

| Ruta | Vista | Layout | Auth/Rol |
|---|---|---|---|
| `/stores` | `src/app/stores/page.tsx` | `PublicLayout` | Publica |
| `/stores/:storeSlug/products` | `src/pages/StoreProducts.tsx` | `PublicLayout` | Publica |
| `/cart` | `src/pages/Cart.tsx` | `PublicLayout` | Publica |
| `/checkout` | `src/pages/Checkout.tsx` | `PublicLayout` | Publica |
| `/login` | `src/app/login/page.tsx` | `AuthLayout` | Guest |
| `/register` | `src/app/register/page.tsx` | `AuthLayout` | Guest |
| `/dashboard` | `src/app/dashboard/page.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/store` | `src/pages/DashboardStore.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/products` | `src/app/dashboard/products/page.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/orders` | `src/app/dashboard/orders/page.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/orders/:id/picking` | `src/app/dashboard/orders/picking/page.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/inventory` | `src/app/dashboard/inventory/page.tsx` | `DashboardLayout` | Merchant |
| `/dashboard/inventory/receive` | `src/app/dashboard/inventory/receive/page.tsx` | `DashboardLayout` | Merchant |

---

## 2) Auditoria auth/sesion/logout/guards (FASE 2)

### 2.1 Emision y uso de token
Backend:
- Token Sanctum se crea en registro/login: `app/Http/Controllers/Api/AuthController.php:36-43`, `app/Http/Controllers/Api/AuthController.php:80-83`, `app/Http/Controllers/Api/AuthController.php:161-166`.
- `login` revoca tokens previos: `app/Http/Controllers/Api/AuthController.php:81`.
- `/api/me` devuelve `role`, `has_store`, `store_id`: `app/Http/Controllers/Api/AuthController.php:120-128`.

Frontend:
- Interceptor agrega `Authorization: Bearer ...`: `comercio-plus-frontend/src/services/api.ts:71-79`.
- Manejo global 401 limpia sesion y redirige `/login`: `comercio-plus-frontend/src/services/api.ts:102-107`.
- Persistencia remember/session: `comercio-plus-frontend/src/services/auth-session.ts:54-76`.

### 2.2 Guards de UI
- `RequireAuth`: exige token + user serializado (`RequireAuth.tsx:10-21`).
- `RequireRole`: exige `merchant` para dashboard (`RequireRole.tsx:35-37`).

### 2.3 Logout real (hallazgo importante)
- Dashboard Home hace logout backend + limpieza local (`src/app/dashboard/page.tsx:404-413`).
- Navbar publica solo limpia local, no llama `/api/logout` (`src/components/Navbar.tsx:88-92`).

Impacto:
- Logout inconsistente segun desde donde cierre sesion.

### 2.4 Roles/permisos
- Hay soporte mixto: columna `users.role`, Spatie roles, middlewares propios.
- Evidencia: `app/Http/Kernel.php:45-50`, `app/Http/Middleware/EnsureRole.php`, `app/Http/Controllers/Api/AuthController.php:146-157`.

---

## 3) Flujo AS-IS real end-to-end (FASE 3)

## 3.1 Flujo Cliente comprador (actual)
1. Home `/` -> entra a `/stores`.
2. `/stores` carga `GET /api/public-stores` (`src/app/stores/page.tsx:40-42`).
3. Al elegir tienda se abre modal de registro (`src/app/stores/page.tsx:64-67`).
4. Si acepta registro intenta `POST /api/stores/register-customer` (`src/app/stores/page.tsx:78-81`), luego navega de todas formas a catalogo.
5. `/stores/:slug/products` resuelve tienda y carga productos por `store_id` (`src/pages/StoreProducts.tsx:41-60`).
6. Agrega al carrito con `CartContext` local (`src/pages/StoreProducts.tsx:119-127`).
7. `/cart` usa localStorage (`src/context/CartContext.tsx:31-44`).
8. `/checkout` crea orden (`POST /api/orders/create`) y pago Wompi (`POST /api/payments/wompi/create`) (`src/pages/Checkout.tsx:53-64`, `74-85`).
9. Redirige a `checkoutUrl` y vuelve a `/checkout/success`.

Estados reales encontrados:
- Carrito vacio redirige a `/cart` (`src/pages/Checkout.tsx:106-109`).
- Errores/validaciones usan `alert()` (`src/pages/Checkout.tsx:41`, `46`, `95`).

## 3.2 Flujo Comerciante (actual)
1. Registro/Login (`/register`, `/login`) contra API (`src/app/register/page.tsx:34`, `src/app/login/page.tsx:25`).
2. Post-auth route por estado de tienda:
   - merchant con tienda -> `/dashboard/products`
   - merchant sin tienda -> `/dashboard/store`
   Evidencia: `src/services/auth-session.ts:86-89`.
3. `DashboardLayout` consulta `/api/my/store` y cachea tienda (`src/components/layouts/DashboardLayout.tsx:46-51`).
4. En productos (`/dashboard/products`) primero valida tienda y luego lista (`src/app/dashboard/products/page.tsx:308-318`, `340-345`).
5. En pedidos (`/dashboard/orders`) consume `GET /api/merchant/orders` (`src/app/dashboard/orders/page.tsx:316-319`).
6. Picking (`/dashboard/orders/:id/picking`) usa endpoints scan/manual/fallback/complete.

Regla de stock actual (critica):
- Stock se descuenta por transicion de orden pagada/aprobada/completada, no por completar picking.
- Evidencia:
  - `app/Observers/OrderObserver.php:39-47`
  - `app/Services/OrderBillingService.php:134-137`
  - `app/Services/InventoryService.php:24-30`, `66-83`
- `complete picking` solo cambia fulfillment y valida lineas (`app/Http/Controllers/Api/Merchant/OrderPickingController.php:488-504`).

---

## 4) Auditoria UX/UI (FASE 4)

## 4.1 Fricciones severas (con evidencia)
1. Navbar/Footer publica no es consistente en todas las rutas.
- `PublicLayout` solo los pinta en home (`src/components/layouts/PublicLayout.tsx:12-13`).

2. Logout no esta visible globalmente en dashboard.
- Sidebar no tiene accion logout (`src/components/dashboard/Sidebar.tsx:36-49`, `156-190`).
- Logout solo aparece en `/dashboard` home (`src/app/dashboard/page.tsx:440-442`).

3. Inconsistencia de logout (server vs local).
- Dashboard: llama `/api/logout`.
- Navbar: no llama backend (`src/components/Navbar.tsx:88-92`).

4. Catalogo publico `/products` y `/products/:id` sigue en mock/demo.
- `src/pages/Products.tsx:9-18`
- `src/pages/ProductDetail.tsx:9-30`

5. Feedback bloqueante con `alert/confirm` en flujos operativos.
- Checkout alerts: `src/pages/Checkout.tsx:41`, `46`, `95`.
- Confirm en pedidos/picking: `src/app/dashboard/orders/page.tsx:175`, `src/app/dashboard/orders/picking/page.tsx:634`.

6. Responsividad dashboard limitada por sidebar fija.
- Sidebar ancho fijo (`src/components/dashboard/Sidebar.tsx:132`).
- Layout sin colapso/toggle responsive (`src/components/layouts/DashboardLayout.tsx:107-113`).

## 4.2 Fricciones medias
7. Existe `/forgot-password`, pero login no lo enlaza como link interactivo.
- Ruta definida: `src/app/App.tsx:130`.
- Login muestra texto no navegable: `src/app/login/page.tsx:118`.

8. Carrito solo localStorage (sin sincronia cross-device).
- `src/context/CartContext.tsx:31-44`.

9. Mezcla fuerte de estilos inline y Tailwind en navbar.
- `src/components/Navbar.tsx` (bloques extensos style inline).

10. Hay alta carga de codigo legacy (Vue/Blade) no usado por el React principal.
- `resources/js/**`, `resources/views/**` coexisten con `comercio-plus-frontend/src/**`.

---

## 5) Propuesta TO-BE: flujo tienda profesional (FASE 5)

## 5.1 Cliente (publico)
Flujo recomendado:
1. Home -> 2. Tiendas/Categorias -> 3. Catalogo tienda -> 4. Detalle producto -> 5. Carrito -> 6. Checkout -> 7. Success.

Mejoras drop-in:
- Navbar/Footer consistente en TODAS las rutas publicas.
- Breadcrumbs + boton volver en detalle producto, carrito, checkout.
- Reemplazar `alert/confirm` por toast/dialog.
- Conectar `/products` y `/products/:id` a API real para eliminar demo.

## 5.2 Comerciante
Flujo recomendado:
1. Login/Register -> 2. Gate onboarding tienda -> 3. Dashboard KPI -> 4. Productos/Inventario -> 5. Pedidos -> 6. Picking -> 7. Reportes.

Reglas de redireccion:
1. Sin token -> `/login?redirect=...`
2. Token con rol no permitido -> `/`
3. Merchant sin tienda -> forzar `/dashboard/store`
4. 401 global -> limpiar sesion + `/login` (ya existe)

Componentes concretos a crear/mejorar:
- `src/components/navigation/Breadcrumbs.tsx`
- `src/components/navigation/BackButton.tsx`
- `src/components/navigation/LogoutButton.tsx` (unificado)
- `src/components/layouts/DashboardTopbar.tsx`
- `src/components/feedback/AppToast.tsx`
- `src/components/feedback/ConfirmDialog.tsx`

## 5.3 Decision funcional critica de negocio
Definir explicitamente una sola politica de OUT:
- Opcion A: descontar en `paid` (modelo actual)
- Opcion B: descontar en `complete picking`
- Opcion C: reservar al crear pedido y confirmar al pagar/alistar

Hoy el codigo implementa A.

---

## 6) Plan priorizado impacto/esfuerzo (FASE 6)

| Cambio | Impacto | Esfuerzo | Riesgo | Archivos principales |
|---|---|---|---|---|
| Logout visible global (dashboard) | Alto | 1-2h | Bajo | `src/components/layouts/DashboardLayout.tsx`, `src/components/dashboard/Sidebar.tsx` |
| Unificar logout (siempre backend + clear local) | Alto | 2-3h | Bajo | `src/components/Navbar.tsx`, `src/services/auth-session.ts` |
| Link real a forgot password | Medio | <1h | Bajo | `src/app/login/page.tsx` |
| Reemplazar `alert/confirm` por UI propia | Alto | 4-8h | Medio | `src/pages/Checkout.tsx`, `src/app/dashboard/orders/page.tsx`, `src/app/dashboard/orders/picking/page.tsx` |
| Navbar/Footer consistente en publicas | Alto | 4-6h | Bajo | `src/components/layouts/PublicLayout.tsx` |
| Sidebar colapsable en mobile | Alto | 1-2 dias | Medio | `src/components/dashboard/Sidebar.tsx`, `src/components/layouts/DashboardLayout.tsx` |
| Quitar mock en `/products` y `/products/:id` | Alto | 1-2 dias | Medio | `src/pages/Products.tsx`, `src/pages/ProductDetail.tsx` |
| Documentar regla oficial de stock OUT | Alto | 2-4h | Bajo | `docs/*`, `dashboard/orders/*` |
| Higiene de legado (doc de frontend oficial) | Medio | 1 dia | Medio | `README.md`, `docs/*` |

Quick wins (1-2h):
- Logout global, link forgot-password, primer reemplazo de alerts.

Medios (1-2 dias):
- Public layout consistente, sidebar responsive, eliminar mock products.

Grandes (1-2 semanas):
- Sistema completo de navegacion (breadcrumbs/topbar), feedback unificado, hardening E2E.

---

## 7) Casos E2E recomendados (FASE 7)

Total: 36 casos (dentro del rango pedido 25-40).

### A. Cliente publico (1-12)
1. Home carga y navega a tiendas.
2. `/stores` consume `GET /api/public-stores`.
3. Buscar tienda filtra correctamente.
4. Modal al seleccionar tienda abre/cierra bien.
5. Ir sin registro a catalogo tienda funciona.
6. Registro cliente desde modal (si autenticado) no rompe flujo.
7. Catalogo tienda muestra productos por `store_id`.
8. Agregar producto al carrito.
9. Actualizar cantidad en carrito.
10. Eliminar item carrito.
11. Checkout valida campos obligatorios.
12. Checkout redirige a Wompi con orden creada.

### B. Auth/sesion (13-20)
13. Register merchant guarda token y user.
14. Register client guarda token y user.
15. Login valido redirige segun rol.
16. Login invalido muestra mensaje 401.
17. Remember=true persiste en localStorage.
18. Remember=false solo sessionStorage.
19. 401 forzado limpia sesion y manda a `/login`.
20. Logout desde dashboard invalida backend + limpia frontend.

### C. Comerciante tienda/producto (21-28)
21. Merchant sin tienda termina en `/dashboard/store`.
22. Crear tienda guarda nombre, logo, portada.
23. Sidebar refleja nombre/logo/portada de tienda.
24. Crear producto manual aparece en listado.
25. Crear producto por scanner keyboard aplica codigo al form.
26. Scanner con 3 fallos habilita fallback manual (`products/page.tsx:382-390`).
27. Inventory receive scan-in suma stock y crea movement.
28. Create-from-scan crea producto + code + movement IN.

### D. Pedidos/picking/stock (29-36)
29. Cliente crea pedido con items correctos.
30. Merchant ve pedido en `/dashboard/orders`.
31. Cambio estado de pedido via API merchant.
32. Picking scan/manual actualiza `qty_picked`.
33. Fallback picking tras 3 errores de scan (`OrderPickingController.php:759-767`).
34. Complete picking solo permite lineas resueltas (`qty_picked + qty_missing == qty_ordered`).
35. Al pasar orden a `paid`, stock decrementa y crea movement sale.
36. Evitar doble descuento en reintentos de estado (validar observer + service).

Casos negativos criticos:
- Token invalido/expirado.
- Merchant sin tienda intentando rutas profundas de dashboard.
- DB caida: login/register deben devolver error controlado (503 en auth).
- CORS/origin mal configurado.
- Red lenta (loaders visibles, sin bloqueos).

---

## Resumen ejecutivo

- El flujo base funciona, pero hay 4 brechas de producto para verse como tienda profesional: navegacion publica inconsistente, logout inconsistente, presencia de vistas mock en catalogo publico, y UX operativa con `alert/confirm`.
- En negocio, la regla real de stock hoy es "descuento por estado paid/approved/completed", no por "complete picking".
- La mejora propuesta es compatible (drop-in) sin romper contratos backend: se puede ejecutar por PRs pequenos y medibles.

