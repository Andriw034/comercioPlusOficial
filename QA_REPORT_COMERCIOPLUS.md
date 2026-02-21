# QA Report ComercioPlus

## Fase 0 - Inventario real del sistema

Fecha: 2026-02-18  
Alcance: Solo inventario y mapa del sistema. Sin ejecutar Fase 1 aun.

## 1) Backend (Laravel) - Rutas y acceso

Fuente principal:
- `routes/api.php`
- `routes/web.php`
- `routes/auth.php`
- `php artisan route:list`

### 1.1 Tabla de rutas API

| Metodo | Endpoint | Auth | Rol/permiso real observado |
|---|---|---|---|
| GET | `/api/health` | No | Publico |
| POST | `/api/register` | No | Publico (rol enviado en payload: `merchant/client/comerciante/cliente`) |
| POST | `/api/login` | No | Publico |
| GET | `/api/public-stores` | No | Publico |
| GET | `/api/public-stores/{store}` | No | Publico |
| GET | `/api/public/stores` | No | Publico |
| GET | `/api/public/stores/{store}` | No | Publico |
| GET | `/api/public/products` | No | Publico |
| GET | `/api/public/categories` | No | Publico |
| GET | `/api/products` | No | Publico |
| GET | `/api/products/{product}` | No | Publico |
| GET | `/api/categories` | No | Publico |
| GET | `/api/categories/{category}` | No | Publico |
| POST | `/api/orders/create` | No | Publico (checkout Wompi) |
| POST | `/api/payments/wompi/create` | No | Publico |
| POST | `/api/payments/wompi/webhook` | No | Publico (valida firma en controlador) |
| GET | `/api/payments/wompi/status/{transactionId}` | No | Publico |
| GET | `/api/payments/wompi/pse-banks` | No | Publico |
| GET | `/api/me` | Si (`auth:sanctum`) | Usuario autenticado |
| POST | `/api/logout` | Si (`auth:sanctum`) | Usuario autenticado |
| GET | `/api/my/store` | Si (`auth:sanctum`) | Usuario autenticado (devuelve tienda propia por `user_id`) |
| POST | `/api/stores` | Si (`auth:sanctum`) | Usuario autenticado (sin middleware de rol; crea tienda para usuario actual) |
| PUT | `/api/stores/{store}` | Si (`auth:sanctum`) | Solo dueno de tienda (`store.user_id == auth.id`) |
| DELETE | `/api/stores/{store}` | Si (`auth:sanctum`) | Solo dueno de tienda |
| POST | `/api/stores/register-customer` | Si (`auth:sanctum`) | Solo cliente (`isClient()`) |
| POST | `/api/stores/{store}/visit` | Si (`auth:sanctum`) | Solo cliente (`isClient()`) |
| POST | `/api/stores/{store}/follow` | Si (`auth:sanctum`) | Auth (placeholder closure) |
| DELETE | `/api/stores/{store}/follow` | Si (`auth:sanctum`) | Auth (placeholder closure) |
| GET | `/api/merchant/orders` | Si (`auth:sanctum`) | Intencion comerciante (sin middleware de rol; depende de tienda del usuario) |
| PUT | `/api/merchant/orders/{id}/status` | Si (`auth:sanctum`) | Comerciante de la tienda de la orden |
| GET | `/api/merchant/customers` | Si (`auth:sanctum`) | Intencion comerciante (requiere tienda del usuario) |
| GET | `/api/merchant/stats` | Si (`auth:sanctum`) | Auth (sin rol explicito en ruta) |
| POST | `/api/products` | Si (`auth:sanctum`) | Auth con tienda existente (usa tienda por `user_id`) |
| PUT | `/api/products/{product}` | Si (`auth:sanctum`) | Solo propietario (`product.user_id == auth.id`) |
| DELETE | `/api/products/{product}` | Si (`auth:sanctum`) | Solo propietario (`product.user_id == auth.id`) |
| POST | `/api/uploads/products` | Si (`auth:sanctum`) | Auth |
| POST | `/api/uploads/stores/logo` | Si (`auth:sanctum`) | Auth |
| POST | `/api/uploads/stores/cover` | Si (`auth:sanctum`) | Auth |
| POST | `/api/uploads/profiles/photo` | Si (`auth:sanctum`) | Auth |
| POST | `/api/categories` | Si (`auth:sanctum`) | Comerciante/Admin + tienda |
| PUT | `/api/categories/{category}` | Si (`auth:sanctum`) | Dueno de tienda de la categoria |
| DELETE | `/api/categories/{category}` | Si (`auth:sanctum`) | Dueno de tienda de la categoria |
| GET/POST/GET/PUT/DELETE | `/api/users` + `/api/users/{user}` | Si (`auth:sanctum`) | Auth (sin rol explicito en ruta) |
| GET/POST/GET/PUT/DELETE | `/api/cart` + `/api/cart/{cart}` | Si (`auth:sanctum`) | Auth |
| GET | `/api/cart/count` | Si (`auth:sanctum`) | Auth |
| POST | `/api/cart/clear` | Si (`auth:sanctum`) | Auth |
| GET/POST/GET/PUT/DELETE | `/api/cart-products` + `/api/cart-products/{cart_product}` | Si (`auth:sanctum`) | Auth (sin ownership estricto en controlador) |
| GET/POST/GET/PUT/DELETE | `/api/orders` + `/api/orders/{order}` | Si (`auth:sanctum`) | Auth (ownership parcial) |
| GET/POST/GET/PUT/DELETE | `/api/subscriptions` + `/api/subscriptions/{subscription}` | Si (`auth:sanctum`) | Auth (sin rol explicito en ruta) |

### 1.2 Tabla de rutas Web (Breeze/Blade)

| Metodo | Endpoint | Auth | Rol |
|---|---|---|---|
| GET | `/` | No | Publico (welcome view) |
| GET | `/dashboard` | Si (`auth`,`verified`) | Usuario autenticado verificado |
| GET/PATCH/DELETE | `/profile` | Si (`auth`) | Usuario autenticado |
| GET/POST | `/login` | Guest | Publico invitado |
| GET/POST | `/register` | Guest | Publico invitado |
| GET/POST | `/forgot-password` | Guest | Publico invitado |
| GET/POST | `/reset-password/{token}` / `/reset-password` | Guest | Publico invitado |
| GET/POST | `/verify-email` / `/email/verification-notification` | Si (`auth`) | Usuario autenticado |
| GET | `/verify-email/{id}/{hash}` | Si (`auth`,`signed`,`throttle`) | Usuario autenticado |
| GET/POST | `/confirm-password` | Si (`auth`) | Usuario autenticado |
| PUT | `/password` | Si (`auth`) | Usuario autenticado |
| POST | `/logout` | Si (`auth`) | Usuario autenticado |

## 2) Backend - Controladores, middlewares, authz

### 2.1 Controladores API presentes

`AuthController`, `CartController`, `CartProductController`, `CategoryController`, `ChannelController`, `ClaimController`, `CustomerController`, `DemoImageController`, `ExternalProductController`, `HeroImageController`, `LocationController`, `NotificacionController`, `OrderController`, `OrderMessageController`, `OrderProductController`, `ProductController`, `ProfileController`, `PruebaController`, `PublicCategoryController`, `PublicProductController`, `PublicStoreController`, `RatingController`, `RoleController`, `SaleController`, `SettingController`, `StatsController`, `StoreController`, `SubscriptionController`, `TutorialController`, `UploadController`, `UserController`, `WompiController`.

### 2.2 Controladores API realmente enroutados hoy

`AuthController`, `CartController`, `CartProductController`, `CategoryController`, `CustomerController`, `OrderController`, `ProductController`, `PublicCategoryController`, `PublicProductController`, `StatsController`, `StoreController`, `SubscriptionController`, `UploadController`, `UserController`, `WompiController`.

### 2.3 Middlewares y autorizacion

- En rutas API, solo se aplica `auth:sanctum` (no hay middleware de rol aplicado en `routes/api.php`).
- Existe Spatie Permission instalado (`spatie/laravel-permission`) y configurado.
- `User` usa trait `HasRoles`.
- Politica registrada: `ProductPolicy` en `AuthServiceProvider`.
- Control de permisos/ownership mayormente dentro de controladores (checks por `user_id`, `store_id`, `hasRole`, `isClient`, etc).

Archivos clave:
- `app/Http/Kernel.php`
- `bootstrap/app.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Policies/ProductPolicy.php`
- `app/Http/Middleware/EnsureRole.php`
- `app/Http/Middleware/RoleMiddleware.php`

## 3) Backend - Modelos y relaciones

### 3.1 Modelos principales (dominio ecommerce)

| Modelo | Relaciones observadas |
|---|---|
| `User` | hasOne `Store`, hasOne `PublicStore`, hasMany `Cart`, hasMany `Order`, hasManyThrough `Product` via `Store`, hasMany `Customer` |
| `Store` | belongsTo `User`, hasMany `Product`, hasMany `Customer`, hasMany `Category` |
| `Product` | belongsTo `Store`, belongsTo `Category`, hasMany `Rating` |
| `Category` | hasMany `Product`, belongsTo `Store`, belongsTo `parent` (self), hasMany `children` (self) |
| `Cart` | belongsTo `User` |
| `CartProduct` | belongsTo `Cart`, belongsTo `Product` |
| `Order` | belongsTo `User`, belongsTo `Store`, hasMany `OrderProduct` (`ordenproducts`) |
| `OrderProduct` | belongsTo `Order`, belongsTo `Product` |
| `Customer` | belongsTo `Store`, belongsTo `User` |
| `Rating` | belongsTo `User`, belongsTo `Product` |
| `PublicStore` | belongsTo `User`, belongsTo `Store` |
| `UserSubscription` | belongsTo `User` |

### 3.2 Otros modelos en repo

`ActivityLog`, `Channel`, `Claim`, `Location`, `Notification`, `OrderMessage`, `Profile`, `Role`, `Sale`, `Setting`, `Tutorial`.

## 4) Configuracion clave

Archivos revisados:
- `config/cors.php`
- `config/sanctum.php`
- `config/filesystems.php`
- `.env.example`
- `config/services.php`

Hallazgos de configuracion (inventario):
- CORS permite `localhost:5173`, `127.0.0.1:5173`, dominio Vercel principal y patron `*.vercel.app`.
- Sanctum configurado para flujo Bearer token (`stateful` vacio, `guard` vacio, `withCredentials=false` en frontend).
- Uploads por `CloudinaryService`: usa Cloudinary si hay credenciales, fallback a disco `public`.
- `.env.example` incluye variables de Wompi, Cloudinary, frontend URL y CORS allowed origins.

## 5) Frontend (React/Vite) - Inventario real

Fuente principal:
- `comercio-plus-frontend/src/app/App.tsx`
- `comercio-plus-frontend/src/services/api.ts`
- `comercio-plus-frontend/src/services/auth-session.ts`
- `comercio-plus-frontend/src/components/auth/*`

Nota de arquitectura:
- El frontend activo hoy es React en `comercio-plus-frontend` (root Vite apunta alli).
- Existe base legacy Vue en `resources/js` (no es la app principal activa).

### 5.1 Tabla de paginas (React router activo)

| Ruta UI | Requiere login | Rol |
|---|---|---|
| `/` | No | Publico |
| `/stores` | No | Publico |
| `/store/create` | No (ruta publica) | Redirige a `/dashboard/store` |
| `/store/:id` | No | Publico |
| `/stores/:storeSlug/products` | No | Publico |
| `/how-it-works` | No | Publico |
| `/products` | No | Publico |
| `/products/:id` | No | Publico |
| `/product/:id` | No | Publico |
| `/cart` | No | Publico |
| `/checkout` | No | Publico |
| `/category/:id` | No | Publico |
| `/privacy` | No | Publico |
| `/terms` | No | Publico |
| `/about`, `/team`, `/careers`, `/blog`, `/press`, `/help`, `/contact`, `/faq`, `/status`, `/report`, `/cookies`, `/returns`, `/warranty`, `/sitemap`, `/accessibility` | No | Publico |
| `/crear-tienda` | No | Redirect a `/store/create` |
| `/login` | No | Publico |
| `/register` | No | Publico |
| `/forgot-password` | No | Publico |
| `/dashboard` | Si | `merchant` (RequireAuth + RequireRole) |
| `/dashboard/customers` | Si | `merchant` |
| `/dashboard/store` | Si | `merchant` |
| `/dashboard/products` | Si | `merchant` |
| `/dashboard/products/create` | Si | `merchant` |
| `/dashboard/products/:id/edit` | Si | `merchant` |

### 5.2 Cliente HTTP y sesion

- Cliente API: `comercio-plus-frontend/src/services/api.ts` (axios).
- Adjunta Bearer token en interceptor request desde `localStorage.token`.
- Manejo 401: limpia `localStorage.user/token` y redirige a `/login`.
- Persistencia de sesion:
  - `localStorage.token`
  - `localStorage.user`
  - carrito en `localStorage.cart`
- Logout:
  - `POST /logout` donde aplica (dashboard)
  - limpia storage y redirige a login.

### 5.3 Layout, navegacion y tema

- Layouts:
  - `PublicLayout`
  - `AuthLayout`
  - `DashboardLayout`
- Navbar/Footer:
  - En `PublicLayout`, navbar/footer se muestran solo en home (`/`).
- Tema:
  - `ThemeProvider` con `light/dark`, persistido en `localStorage` (`cp-theme`).
  - `ThemeToggle` disponible.

## 6) Mapa de flujos (real en codigo)

### 6.1 Visitante -> registro/login

1. Visitante va a `/register` o `/login`.
2. Registro envia `POST /api/register` con rol (`merchant` o `client`).
3. Login envia `POST /api/login`.
4. Si hay token, frontend hidrata sesion con `GET /api/me`.

### 6.2 Seleccion de rol

- En registro React se selecciona `merchant` o `client`.
- `AuthController` normaliza y persiste rol; tambien intenta asignar rol Spatie (`comerciante`/`cliente`).

### 6.3 Comerciante

1. Post-login merchant:
   - Si `has_store` true -> `/dashboard/products`
   - Si `has_store` false -> `/dashboard/store`
2. En dashboard store:
   - `GET /api/my/store`
   - Si no existe, permite crear con `POST /api/stores`
   - Si existe, edita con `PUT /api/stores/{id}`
3. Productos:
   - Lista por tienda `GET /api/products?store_id=...`
   - Crear `POST /api/products`
   - Editar `PUT /api/products/{id}`
   - Eliminar `DELETE /api/products/{id}`
4. Categorias:
   - `GET /api/categories`
   - Crear `POST /api/categories` (desde panel de productos)

### 6.4 Cliente

1. Navega catalogo/tiendas publicas.
2. Puede registrarse como cliente de tienda en `/stores`:
   - `POST /api/stores/register-customer`
3. Entra a productos de tienda (`/stores/:storeSlug/products`), agrega a carrito local.
4. Checkout:
   - `POST /api/orders/create`
   - `POST /api/payments/wompi/create`
   - Redireccion a checkout URL de Wompi.

### 6.5 Admin

- Hay infraestructura parcial backend (Spatie roles/permisos, checks puntuales como categorias).
- No hay panel/admin routes React activos dedicados en `App.tsx`.

## 7) Observaciones de inventario (para preparar Fase 1)

- Hay doble base frontend:
  - React activo (`comercio-plus-frontend`)
  - Vue legacy en `resources/js` (no principal)
- Existen controladores/modelos no enroutados actualmente.
- En API no hay middleware de rol aplicado por ruta; la autorizacion descansa en validaciones dentro de controladores.
- Algunas vistas React publicas usan datos mock (ej. `src/pages/Products.tsx`, `src/pages/ProductDetail.tsx`) y no API real.

---

Fase 0 completada. No se avanzo a Fase 1 en este corte.

## Actualizacion - Auditoria Frontend UX/UI

Fecha: 2026-02-19  
Alcance: auditoria tecnica de frontend React/Vite/Tailwind + vistas Blade existentes en repo.  
Estado: **Condicional** para produccion (deuda de consistencia visual, responsive y accesibilidad).

### 1) Inventario visual completo

#### 1.1 Rutas/paginas React activas (router real)

Fuente:
- `comercio-plus-frontend/src/app/App.tsx:34`

| Ruta UI | Archivo renderizado | Login | Rol |
|---|---|---|---|
| `/` | `comercio-plus-frontend/src/pages/Home.tsx` | No | Publico |
| `/stores` | `comercio-plus-frontend/src/app/stores/page.tsx` | No | Publico |
| `/store/create` | `comercio-plus-frontend/src/app/store/create/page.tsx` | No | Publico |
| `/store/:id` | `comercio-plus-frontend/src/app/store/page.tsx` | No | Publico |
| `/stores/:storeSlug/products` | `comercio-plus-frontend/src/pages/StoreProducts.tsx` | No | Publico |
| `/products` | `comercio-plus-frontend/src/pages/Products.tsx` | No | Publico |
| `/products/:id`, `/product/:id` | `comercio-plus-frontend/src/pages/ProductDetail.tsx` | No | Publico |
| `/cart` | `comercio-plus-frontend/src/pages/Cart.tsx` | No | Publico |
| `/checkout` | `comercio-plus-frontend/src/pages/Checkout.tsx` | No | Publico |
| `/login` | `comercio-plus-frontend/src/app/login/page.tsx` | No | Publico |
| `/register` | `comercio-plus-frontend/src/app/register/page.tsx` | No | Publico |
| `/forgot-password` | `comercio-plus-frontend/src/pages/ForgotPassword.tsx` | No | Publico |
| `/dashboard*` | `comercio-plus-frontend/src/app/dashboard/*.tsx` + `comercio-plus-frontend/src/pages/DashboardStore.tsx` | Si | merchant |

#### 1.2 Paginas existentes pero no enrutadas (deuda tecnica)

- `comercio-plus-frontend/src/app/page.tsx`
- `comercio-plus-frontend/src/app/products/page.tsx`
- `comercio-plus-frontend/src/app/product/page.tsx`
- `comercio-plus-frontend/src/app/how-it-works/page.tsx`
- `comercio-plus-frontend/src/app/dashboard/store/page.tsx`
- `comercio-plus-frontend/src/pages/Login.tsx`
- `comercio-plus-frontend/src/pages/ProductList.tsx`

#### 1.3 Layouts

| Layout | Ubicacion | Proposito |
|---|---|---|
| AppShell | `comercio-plus-frontend/src/components/layouts/AppShell.tsx` | shell base |
| PublicLayout | `comercio-plus-frontend/src/components/layouts/PublicLayout.tsx` | rutas publicas |
| AuthLayout | `comercio-plus-frontend/src/components/layouts/AuthLayout.tsx` | auth con hero visual |
| DashboardLayout | `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx` | panel merchant |

#### 1.4 Componentes reutilizables (detectados)

| Componente | Ubicacion | Estado |
|---|---|---|
| Button (nuevo) | `comercio-plus-frontend/src/components/ui/button.tsx` | Activo |
| Button (legacy) | `comercio-plus-frontend/src/components/Button.tsx` | Activo (duplicado) |
| Input (nuevo) | `comercio-plus-frontend/src/components/ui/Input.tsx` | Activo |
| Input (legacy) | `comercio-plus-frontend/src/components/Input.tsx` | Activo (duplicado) |
| Badge (nuevo) | `comercio-plus-frontend/src/components/ui/Badge.tsx` | Activo |
| Badge (legacy) | `comercio-plus-frontend/src/components/Badge.tsx` | Activo (duplicado) |
| Card/GlassCard (nuevo) | `comercio-plus-frontend/src/components/ui/Card.tsx`, `comercio-plus-frontend/src/components/ui/GlassCard.tsx` | Activo |
| Card (legacy) | `comercio-plus-frontend/src/components/Card.tsx` | Activo (duplicado) |
| ProductCard (2 versiones) | `comercio-plus-frontend/src/components/ProductCard.tsx`, `comercio-plus-frontend/src/components/products/ProductCard.tsx` | Activo (duplicado) |
| ThemeToggle | `comercio-plus-frontend/src/components/theme/ThemeToggle.tsx` | Existe, no montado en layout |

#### 1.5 Blade (si aplica)

Se confirmo presencia de capa visual Blade adicional (no unificada con React):
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/marketing.blade.php`
- `resources/views/layouts/dashboard.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/welcome.blade.php`

### 2) Analisis de diseno y estilo

#### 2.1 Color system

Fuente principal de tokens:
- `comercio-plus-frontend/tailwind.config.js:7`

Hallazgos:
- Primarios coexistentes: `#FF6A00` (`comercioplus`), `#FF9800` (`brand`), `#FF6B35` hardcodeado.
- Se detectan hex hardcodeados en componentes (41 valores unicos en TS/TSX).
- Contraste aproximado:
  - `#FFFFFF` sobre `#FF6B35` = 2.84 (falla texto normal WCAG)
  - `#FFFFFF` sobre `#E65A2B` = 3.58 (falla texto normal)
  - `#1F2937` sobre `#FFFFFF` = 14.68 (ok)

#### 2.2 Tipografia

- Fuentes definidas: `Plus Jakarta Sans`, `Space Grotesk` (`comercio-plus-frontend/tailwind.config.js:78`).
- Escala existe (`h1/h2/h3/body/caption`), pero se rompe con uso alto de utilidades arbitrarias `text-[...]`.

#### 2.3 Espaciado, bordes y sombras

- Sistema base Tailwind correcto pero inconsistente por combinacion de:
  - Tokens (`shadow-premium`)
  - Hardcoded (`shadow-[...]`, `rounded-[8px]`, `text-[15px]`)
- Conteo de utilidades arbitrarias detectadas: 212 ocurrencias.

#### 2.4 Botones e inputs

Botones:
- 2 sistemas activos con comportamiento distinto:
  - `comercio-plus-frontend/src/components/ui/button.tsx:3`
  - `comercio-plus-frontend/src/components/Button.tsx:29`

Inputs:
- Base `input-dark` no tiene variante dark completa:
  - `comercio-plus-frontend/src/app/globals.css:50`
- Labels con color inline fijo:
  - `comercio-plus-frontend/src/components/ui/Input.tsx:46`

#### 2.5 Tablas y cards

- Tabla principal no responsive real en mobile por `min-w-[980px]`:
  - `comercio-plus-frontend/src/app/dashboard/customers/page.tsx:124`
- Cards de producto con dos estilos incompatibles:
  - legacy: `comercio-plus-frontend/src/components/ProductCard.tsx:30`
  - nuevo: `comercio-plus-frontend/src/components/products/ProductCard.tsx:34`

### 3) Responsive y layout

Breakpoints en uso: `sm`, `md`, `lg`, `xl`.

Problemas relevantes:
- Sidebar dashboard fijo sin colapso mobile:
  - `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx:102`
  - `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx:162`
- Navbar/Footer solo en home por condicion:
  - `comercio-plus-frontend/src/components/layouts/PublicLayout.tsx:8`
- Footer con newsletter en fila fija puede comprimirse en mobile:
  - `comercio-plus-frontend/src/components/Footer.tsx:268`

### 4) Modo claro/oscuro

Implementacion base:
- `darkMode: 'class'` en `comercio-plus-frontend/tailwind.config.js:3`
- provider correcto en `comercio-plus-frontend/src/providers/theme-provider.tsx:27`
- montado en `comercio-plus-frontend/src/app/main.tsx:10`

Riesgos:
- Cobertura dark desigual en rutas activas (varias sin `dark:`).
- `ThemeToggle` existe pero no se usa en layout global:
  - `comercio-plus-frontend/src/components/theme/ThemeToggle.tsx:3`
- `GlassCard` base forzada a blanco:
  - `comercio-plus-frontend/src/components/ui/GlassCard.tsx:12`

### 5) UX - fortalezas y riesgos

Fortalezas:
- Home/Auth con buena direccion visual, motion y branding.
- Hay estados de loading/empty en varias vistas nuevas.

Problemas criticos:
- Rutas core renderizan paginas legacy con mocks y alerts.
- Feedback UX con `alert()` en flujos de compra/pago.
- Navegacion publica inconsistente (home vs resto de rutas).

### 6) Hallazgos priorizados (con evidencia y fix pequeno)

| ID | Severidad | Hallazgo | Evidencia | Reproduccion resumida | Causa probable | Fix sugerido |
|---|---|---|---|---|---|---|
| UI-01 | Critical | Router mezcla paginas legacy y nuevas | `comercio-plus-frontend/src/app/App.tsx:19` | Ir a `/products` y `/product/:id`; renderiza vistas legacy con mocks | Migracion parcial | Enrutar `/products` y detalle a `src/app/products/page.tsx` y `src/app/product/page.tsx` |
| UI-02 | Critical | Duplicacion de sistema UI (Button/Input/Badge/Card) | `comercio-plus-frontend/src/components/ui/button.tsx:3`, `comercio-plus-frontend/src/components/Button.tsx:29` | Revisar imports en paginas: conviven ambos | Falta de consolidacion DS | Definir `components/ui/*` como fuente unica y migrar imports |
| UI-03 | Major | Navbar/Footer solo en home | `comercio-plus-frontend/src/components/layouts/PublicLayout.tsx:12` | Navegar de `/` a `/stores`; desaparece nav/footer | Condicion `isHomeRoute` | Renderizar navbar/footer para rutas publicas (exceptos explicitos) |
| UI-04 | Critical | Dashboard no mobile-friendly (sidebar fijo) | `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx:102` | Abrir dashboard en viewport movil | No hay drawer/collapse | Cambiar a `hidden lg:block` + drawer movil + `ml-0 lg:ml-60` |
| UI-05 | Major | Clases Tailwind no definidas (`z-header`, `z-modal`, `shadow-card`, `dark:bg-panel`) | `comercio-plus-frontend/src/components/Navbar.tsx:77`, `comercio-plus-frontend/src/components/products/ProductQuickViewModal.tsx:58` | Inspeccionar clases en runtime | Tokens faltantes en config | Reemplazar por clases validas (`z-50`, `shadow-premium`, `dark:bg-slate-900`) |
| UI-06 | Major | Contraste insuficiente en CTA naranja con texto blanco | `comercio-plus-frontend/src/components/ui/button.tsx:5` | Validar ratio de contraste | Color primario sin validacion WCAG | Oscurecer fondo o cambiar color de texto segun variante |
| UI-07 | Major | Dark mode parcial + toggle no expuesto | `comercio-plus-frontend/src/components/theme/ThemeToggle.tsx:3` | Activar dark por clase; varias vistas quedan light | Integracion incompleta | Montar toggle en layout + pass de `dark:` en pantallas activas |
| UI-08 | Major | Tabla clientes no responsive real | `comercio-plus-frontend/src/app/dashboard/customers/page.tsx:124` | Ver en mobile: scroll horizontal permanente | Tabla desktop-first | Agregar vista card/lista en `<md` |
| UI-09 | Major | UX de errores/acciones usa `alert` y redireccion dura | `comercio-plus-frontend/src/pages/Checkout.tsx:40`, `comercio-plus-frontend/src/services/api.ts:44` | Ejecutar acciones de checkout o 401 | Manejo temporal | Sustituir por toast/banner y `navigate()` |
| UI-10 | Minor | React y Blade con lenguaje visual no unificado | `resources/views/layouts/admin.blade.php:31`, `resources/views/layouts/app.blade.php:15` | Comparar panel React vs Blade | Coexistencia sin contrato DS | Definir frontera: migrar o estandarizar tokens compartidos |

### 7) Mini design system recomendado

Accion estructural:
1. Unificar componentes base en `comercio-plus-frontend/src/components/ui/*`.
2. Centralizar tokens de color/radius/shadow/typography en `tailwind.config.js` y eliminar hardcodes.
3. Crear componentes base faltantes: `Modal`, `TableResponsive`, `EmptyState`, `Toast`.
4. Integrar `ThemeToggle` en layout principal.
5. Establecer checklist de accesibilidad minima (focus visible, aria-label, contraste).

### 8) Score final

Puntuacion general: **5.3 / 10**

| Dimension | Score |
|---|---|
| Consistencia visual | 3.8 |
| Profesionalismo | 6.4 |
| Escalabilidad UI | 4.2 |
| UX | 5.9 |
| Responsive | 5.0 |
| Accesibilidad | 4.6 |

Nivel actual: **Profesional intermedio (fragmentado)**  
No alcanza aun nivel marketplace competitivo sin consolidacion visual y responsive.

### 9) Checklist "Ready for Production" (Frontend)

- [ ] Un solo sistema de componentes (`ui/*`) en rutas activas
- [ ] Rutas core sin mocks ni paginas legacy
- [ ] Navbar/Footer consistentes en todo flujo publico
- [ ] Dashboard usable en mobile (drawer/collapse)
- [ ] Dark mode completo y toggle visible
- [ ] Contraste AA en botones principales
- [ ] Tablas con alternativa responsive
- [ ] Feedback UX sin `alert()` y sin redireccion dura
- [ ] Cobertura minima de accesibilidad basica (labels, focus, aria)
