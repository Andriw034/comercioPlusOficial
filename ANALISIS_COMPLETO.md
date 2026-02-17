# ANALISIS TECNICO INTEGRAL - COMERCIO PLUS

Fecha del analisis: 2026-02-17
Repositorio: `c:\xampp\htdocs\comercioPlusOficial`

## 1) Alcance y metodologia

Este informe se construyo con escaneo tecnico de carpetas y archivos de codigo propio (backend + frontend), incluyendo:

- Inventario de archivos por carpeta y lineas por archivo.
- Revision de arquitectura, rutas, modelos, controladores, migraciones, estilos, tests y tooling.
- Validacion de ejecucion real:
  - `php artisan route:list`
  - `php artisan test --filter=BasicApiTest`
  - `npm run build`
  - `npm run lint`

Exclusion intencional del analisis linea-a-linea: `vendor/`, `node_modules/`, artefactos binarios y carpetas de reportes (`playwright-report/`, `test-results/`) por ser codigo de terceros o salida generada.

## 2) Resumen ejecutivo

Estado global actual: **CRITICO / INCONSISTENTE**.

Diagnostico principal:

1. La API principal de negocio no esta publicada en runtime: el archivo `routes/api.php` actualmente solo registra rutas de Wompi.
2. El frontend React consume muchos endpoints que hoy no existen en el router activo.
3. Hay conflicto de migraciones de `orders` que rompe tests (tabla ya existe).
4. Configuracion base sensible fue sobrescrita por plantillas parciales (`config/services.php`, `.env.example`, `comercio-plus-frontend/.env.example`).
5. El diseño visual tiene buena direccion (branding naranja + componentes modernos), pero hay deuda de consistencia por coexistencia de capas React nueva + legado Vue/Blade.

Conclusion ejecutiva:

- El proyecto **compila frontend**, pero **no esta en estado estable de integracion end-to-end**.
- Antes de nuevas funcionalidades, se requiere una **fase de saneamiento estructural** (rutas API, migraciones, configuracion, limpieza de legado activo/inactivo).

## 3) Metricas de codigo auditado

Base auditada (codigo propio):

- `app`: 166 archivos, 9209 lineas
- `routes`: 6 archivos, 139 lineas
- `database`: 93 archivos, 4139 lineas
- `config`: 17 archivos, 1457 lineas
- `resources`: 149 archivos, 9052 lineas
- `comercio-plus-frontend/src`: 76 archivos, 8686 lineas
- `tests`: 28 archivos, 1203 lineas
- `tests-e2e`: 1 archivo, 31 lineas

Total auditado en anexo: **535 archivos**.

## 4) Estado por capa

### 4.1 Backend Laravel

Fortalezas:

- Base moderna: Laravel 11 + Sanctum + Spatie Permission + servicios de media (Cloudinary).
- Dominio amplio implementado en `app/Models` y `app/Http/Controllers/Api`.
- Controladores API con validaciones y casos de fallback en varios modulos (`ProductController`, `CategoryController`, `StoreController`).

Hallazgos criticos:

1. **Rutas API de negocio no cargadas**.
   - Evidencia: `routes/api.php` solo contiene `orders/create` y `payments/wompi/*`.
   - Evidencia runtime: `php artisan route:list` muestra 35 rutas totales, y en API solo Wompi.
   - Impacto: frontend no puede operar catalogo, auth API, tienda, categorias, clientes, dashboard.

2. **`config/services.php` sobrescrito con plantilla parcial**.
   - Solo define bloque `wompi`; faltan entradas estandar y proveedores adicionales usados por servicios.
   - Impacto: configuraciones de integraciones quedan incompletas/no confiables.

3. **`.env.example` invalido para bootstrap de Laravel**.
   - Contiene solo variables de Wompi/DB de ejemplo y no la estructura completa esperada de app.
   - Impacto: onboarding/deploy propenso a configuraciones incompletas.

4. **Conflicto de migraciones en `orders`**.
   - Existen dos migraciones activas que crean `orders`:
     - `database/migrations/2025_01_16_create_orders_table.php`
     - `database/migrations/2025_05_12_220900_create_orders_table.php`
   - Resultado real: `php artisan test --filter=BasicApiTest` falla 13/13 por `table "orders" already exists`.

5. **Modelo/tabla de pagos con nomenclaturas mixtas**.
   - `Order` usa campos `payment_approved_at`, `payment_failed_at`, `wompi_data`.
   - Migracion Wompi adicional crea `paid_at`, `payment_data`, `payment_error`.
   - Impacto: deuda de esquema y potencial incoherencia de datos.

6. **`RouteServiceProvider` y archivos de rutas auxiliares no alineados con runtime actual**.
   - Hay `routes/admin.php` con recursos admin, pero no esta montado en `bootstrap/app.php` ni en `routes/web.php` actual.
   - Impacto: controladores y vistas admin quedan huerfanos.

7. **Clase de servicio no resoluble por dependencia faltante**.
   - `app/Services/ColorPaletteService.php` usa `ColorThief\ColorThief`, pero no hay paquete en `composer.json`/`composer.lock`.
   - Impacto: error en runtime si se invoca.

### 4.2 Frontend React (`comercio-plus-frontend`)

Fortalezas:

- Stack actual moderno (`React + TS + Vite + Tailwind + Router`).
- Layouts bien separados (`PublicLayout`, `AuthLayout`, `DashboardLayout`).
- Build productivo funcional.

Resultados reales:

- `npm run build`: **OK** (con advertencia de chunk grande ~558 KB).
- `npm run lint`: **FALLA** (5 errores, 1 warning).

Errores de lint mas relevantes:

- `setState` dentro de efectos (Navbar, CartContext).
- uso de `Math.random()` durante render en componentes de tarjetas.
- warning de dependencias incompletas en `useEffect`.

Hallazgos criticos de integracion:

1. **Desalineacion severa backend/frontend en endpoints**.
   - Front usa: `/login`, `/register`, `/logout`, `/me`, `/my/store`, `/products`, `/categories`, `/public-stores`, `/merchant/orders`, `/merchant/customers`, etc.
   - Backend publicado hoy: solo endpoints Wompi.

2. **Checkout usa `fetch('/api/...')` directo y no cliente API central**.
   - `comercio-plus-frontend/src/pages/Checkout.tsx` llama `fetch('/api/orders/create')` y `fetch('/api/payments/wompi/create')`.
   - Riesgo: si frontend y backend no comparten origen, rompe flujo por URL relativa/CORS.

3. **Ruta de exito de pago no registrada**.
   - Checkout redirige a `${window.location.origin}/payment/success`.
   - En router principal no existe `Route` para `/payment/success`.

4. **`.env.example` del frontend incorrecto**.
   - Contiene variables de backend (`DB_*`, `WOMPI_*`) y no la base minima Vite (`VITE_API_BASE_URL`, etc.).

5. **Codigo duplicado/legado y convenciones mixtas**.
   - Coexisten `src/app/*` y `src/pages/*`.
   - Hay componentes funcionales con API real y otros con mock/TODO.

### 4.3 Frontend legado Vue + Blade

Estado:

- Existe un bloque legado amplio en `resources/js` y `resources/views`.
- `vite.config.js` actual apunta a `comercio-plus-frontend`, dejando `resources/js` como legado (config legado en `vite.legacy.config.js`).

Riesgo tecnico:

- Doble stack activo en repositorio (React nuevo + Vue/Blade legacy) sin frontera estricta.
- Incrementa costo de mantenimiento y ambiguedad operativa.

## 5) Analisis de rutas y logica de negocio

### 5.1 Rutas disponibles en runtime

`php artisan route:list` confirma:

- API: solo
  - `POST /api/orders/create`
  - `POST /api/payments/wompi/create`
  - `POST /api/payments/wompi/webhook`
  - `GET /api/payments/wompi/status/{transactionId}`
  - `GET /api/payments/wompi/pse-banks`
- Web: auth Breeze estandar + dashboard basico.

### 5.2 Rutas esperadas por frontend y no expuestas

Faltan en runtime (entre otras):

- `POST /api/login`
- `POST /api/register`
- `POST /api/logout`
- `GET /api/me`
- `GET /api/my/store`
- `GET/POST/PUT/DELETE /api/products`
- `GET/POST /api/categories`
- `GET /api/public-stores`
- `GET /api/merchant/orders`
- `GET /api/merchant/customers`

Impacto:

- El flujo comercial principal (catalogo, tienda, dashboard, auth API) queda bloqueado.

## 6) Base de datos y migraciones

Estado:

- 63 migraciones activas en `database/migrations` + historicos archivados.
- Se evidencia estrategia de archivo parcial (migraciones no-op) junto con migraciones activas nuevas.

Riesgos:

1. Conflicto de `create orders table` (comprobado en tests).
2. Historia de esquema compleja y heterogenea (nombres y columnas evolucionadas en multiples fases).
3. Seeders extensos con logica defensiva por existencia de columnas (sintoma de esquema variable por entorno).

Recomendacion inmediata:

- Definir una **linea base unica de migraciones** limpia para nuevos entornos.
- Resolver duplicado de `orders` y normalizar columnas de pago.

## 7) Diseno, paleta y UX

### 7.1 Paleta principal identificada

Paleta de marca (coherente y reutilizada):

- Primario: `#FF6A00` / gama `comercioplus`.
- Secundario oscuro: gama `slate` (`#0F172A`, `#1E293B`, etc.).
- Acento naranja: `#FF9800`, `#FFA62B`.
- Estados: `success #10B981`, `warning #F59E0B`, `danger #EF4444`.

Tipografia:

- `Plus Jakarta Sans` (base).
- `Space Grotesk` (display).

Fortalezas UX:

- Identidad visual moderna, tarjetas glass, gradientes, animaciones de entrada.
- Estructura de layout clara por contexto (publico/auth/dashboard).

Debilidades UX/UI:

1. Inconsistencia de tokens: coexistencia de clases Tailwind semanticas y hex hardcodeados.
2. Gradientes aleatorios/hardcoded no siempre alineados con branding principal.
3. Mezcla de componentes `ui/*` y componentes duplicados base (`Button.tsx` vs `ui/button.tsx`, etc.).
4. Varias pantallas conservan placeholders, TODOs o flujos simulados.

## 8) Calidad, pruebas y mantenibilidad

### 8.1 Pruebas automatizadas

Backend:

- Ejecucion: `php artisan test --filter=BasicApiTest`
- Resultado: **13 fallos de 13**, causa raiz: migraciones conflictivas (`orders already exists`).

Frontend:

- Build: **OK**
- Lint: **FAIL** (errores de pureza/render y hooks).

E2E:

- Existen dos configuraciones Playwright (`playwright.config.ts` y `playwright.config.js`) y dos ubicaciones de specs (`tests-e2e` y `tests/e2e`) con enfoque no uniforme.

### 8.2 Documentacion

- `README.md` raiz y `comercio-plus-frontend/README.md` no reflejan con precision el estado real actual.
- Existen multiples informes/TODO con snapshots de fases anteriores; no todos vigentes.

## 9) Hallazgos criticos priorizados

### Prioridad P0 (bloquean operacion)

1. Restaurar/reestructurar `routes/api.php` para exponer API funcional completa.
2. Corregir conflicto de migraciones `orders` y estabilizar esquema.
3. Recuperar `config/services.php` completo y `.env.example` realista (backend y frontend).
4. Alinear contratos frontend-backend (endpoints, payloads y auth token flow).

### Prioridad P1 (estabilidad y calidad)

1. Corregir errores de lint React (hooks/pureza render).
2. Unificar estrategia de ruteo frontend (`app/*` vs `pages/*`) y eliminar duplicidad funcional.
3. Eliminar o archivar definitivamente codigo legado no usado en runtime.
4. Completar ruta de retorno de pago (`/payment/success`) y manejo de estados transaccionales.

### Prioridad P2 (mantenibilidad)

1. Estandarizar tokens de diseno (evitar hardcoded hex dispersos).
2. Consolidar suites E2E y configuracion Playwright.
3. Actualizar documentacion oficial del proyecto (setup, arquitectura, contratos API).

## 10) Plan recomendado de recuperacion

Fase 1 - Recuperacion de backend (1-2 dias):

- Reconstruir `routes/api.php` con endpoints minimos de auth/store/products/categories/customers/orders.
- Resolver migraciones conflictivas y validar `php artisan migrate:fresh --seed`.
- Ejecutar y reparar suite Feature base.

Fase 2 - Integracion frontend (1-2 dias):

- Revisar cliente API central y eliminar llamadas relativas sueltas donde aplique.
- Corregir endpoints inexistentes y contratos de payload.
- Implementar ruta real de `payment/success` y errores de pago.

Fase 3 - Saneamiento tecnico (2-3 dias):

- Reducir legado no usado.
- Resolver lint y deudas de hooks.
- Unificar estilos/tokens y cerrar TODOs de UX criticos.

## 11) Estado actual de la aplicacion

Valoracion sintetica (hoy):

- Arquitectura potencial: **8/10**
- Implementacion funcional backend publicada: **3/10**
- Implementacion funcional frontend integrada: **4/10**
- Calidad automatizada (tests/lint): **3/10**
- Coherencia de diseno visual: **7/10**
- Mantenibilidad general: **4/10**

**Score tecnico estimado actual: 4.8/10**.

## 12) Anexo A - Inventario carpeta por carpeta, archivo por archivo, lineas por archivo
### `app`

- Archivos: **166**
- Lineas: **9209**

| Archivo | Lineas |
|---|---:|
| `app\Console\Commands\ClearUsers.php` | 35 |
| `app\Console\Commands\UpdatePasswords.php` | 27 |
| `app\Console\Kernel.php` | 26 |
| `app\Exceptions\Handler.php` | 42 |
| `app\helpers.php` | 40 |
| `app\Http\Controllers\Admin\CategoryController.php` | 100 |
| `app\Http\Controllers\Admin\ProductController.php` | 147 |
| `app\Http\Controllers\Admin\SettingsController.php` | 68 |
| `app\Http\Controllers\Admin\StatsPageController.php` | 18 |
| `app\Http\Controllers\AdminController.php` | 73 |
| `app\Http\Controllers\Api\AuthController.php` | 244 |
| `app\Http\Controllers\Api\CartController.php` | 102 |
| `app\Http\Controllers\Api\CartProductController.php` | 97 |
| `app\Http\Controllers\Api\CategoryController.php` | 135 |
| `app\Http\Controllers\Api\ChannelController.php` | 87 |
| `app\Http\Controllers\Api\ClaimController.php` | 102 |
| `app\Http\Controllers\Api\CustomerController.php` | 56 |
| `app\Http\Controllers\Api\DemoImageController.php` | 29 |
| `app\Http\Controllers\Api\ExternalProductController.php` | 68 |
| `app\Http\Controllers\Api\HeroImageController.php` | 25 |
| `app\Http\Controllers\Api\LocationController.php` | 65 |
| `app\Http\Controllers\Api\NotificacionController.php` | 95 |
| `app\Http\Controllers\Api\OrderController.php` | 149 |
| `app\Http\Controllers\Api\OrderMessageController.php` | 65 |
| `app\Http\Controllers\Api\OrderProductController.php` | 97 |
| `app\Http\Controllers\Api\ProductController.php` | 269 |
| `app\Http\Controllers\Api\ProfileController.php` | 65 |
| `app\Http\Controllers\Api\PruebaController.php` | 60 |
| `app\Http\Controllers\Api\PublicCategoryController.php` | 20 |
| `app\Http\Controllers\Api\PublicProductController.php` | 69 |
| `app\Http\Controllers\Api\PublicStoreController.php` | 88 |
| `app\Http\Controllers\Api\RatingController.php` | 65 |
| `app\Http\Controllers\Api\RoleController.php` | 62 |
| `app\Http\Controllers\Api\SaleController.php` | 99 |
| `app\Http\Controllers\Api\SettingController.php` | 89 |
| `app\Http\Controllers\Api\StatsController.php` | 80 |
| `app\Http\Controllers\Api\StoreController.php` | 219 |
| `app\Http\Controllers\Api\SubscriptionController.php` | 49 |
| `app\Http\Controllers\Api\TutorialController.php` | 93 |
| `app\Http\Controllers\Api\UploadController.php` | 88 |
| `app\Http\Controllers\Api\UserController.php` | 138 |
| `app\Http\Controllers\Api\WompiController.php` | 295 |
| `app\Http\Controllers\Auth\AuthenticatedSessionController.php` | 37 |
| `app\Http\Controllers\Auth\ConfirmablePasswordController.php` | 34 |
| `app\Http\Controllers\Auth\EmailVerificationController.php` | 25 |
| `app\Http\Controllers\Auth\EmailVerificationNotificationController.php` | 19 |
| `app\Http\Controllers\Auth\EmailVerificationPromptController.php` | 18 |
| `app\Http\Controllers\Auth\ForgotPasswordController.php` | 28 |
| `app\Http\Controllers\Auth\LoginController.php` | 84 |
| `app\Http\Controllers\Auth\NewPasswordController.php` | 55 |
| `app\Http\Controllers\Auth\PasswordController.php` | 24 |
| `app\Http\Controllers\Auth\PasswordResetLinkController.php` | 38 |
| `app\Http\Controllers\Auth\RegisterController.php` | 12 |
| `app\Http\Controllers\Auth\RegisteredUserController.php` | 42 |
| `app\Http\Controllers\Auth\ResetPasswordController.php` | 43 |
| `app\Http\Controllers\Auth\VerifyEmailController.php` | 22 |
| `app\Http\Controllers\CartController.php` | 7 |
| `app\Http\Controllers\CartProductController.php` | 7 |
| `app\Http\Controllers\CategoryController.php` | 41 |
| `app\Http\Controllers\ChannelController.php` | 7 |
| `app\Http\Controllers\ClaimController.php` | 7 |
| `app\Http\Controllers\Controller.php` | 9 |
| `app\Http\Controllers\Dashboard\BrandingController.php` | 47 |
| `app\Http\Controllers\Dashboard\DashboardController.php` | 14 |
| `app\Http\Controllers\DashboardController.php` | 37 |
| `app\Http\Controllers\DashboardProductsController.php` | 156 |
| `app\Http\Controllers\EducationController.php` | 30 |
| `app\Http\Controllers\HomeController.php` | 23 |
| `app\Http\Controllers\LocationController.php` | 7 |
| `app\Http\Controllers\messages.php` | 0 |
| `app\Http\Controllers\NotificationController.php` | 7 |
| `app\Http\Controllers\OrderController.php` | 7 |
| `app\Http\Controllers\OrderMessageController.php` | 7 |
| `app\Http\Controllers\OrderProductController.php` | 7 |
| `app\Http\Controllers\OrmController.php` | 86 |
| `app\Http\Controllers\ProductController.php` | 252 |
| `app\Http\Controllers\ProfileController.php` | 47 |
| `app\Http\Controllers\PruebaController.php` | 67 |
| `app\Http\Controllers\RatingController.php` | 7 |
| `app\Http\Controllers\RoleController.php` | 7 |
| `app\Http\Controllers\SaleController.php` | 7 |
| `app\Http\Controllers\SettingController.php` | 7 |
| `app\Http\Controllers\Settings\ProfileController.php` | 40 |
| `app\Http\Controllers\StoreController.php` | 120 |
| `app\Http\Controllers\TutorialController.php` | 7 |
| `app\Http\Controllers\UserController.php` | 95 |
| `app\Http\Controllers\Web\Admin\ExtProductDashboardController.php` | 109 |
| `app\Http\Controllers\Web\CartController.php` | 56 |
| `app\Http\Controllers\Web\CategoryController.php` | 27 |
| `app\Http\Controllers\Web\OrderController.php` | 56 |
| `app\Http\Controllers\Web\ProductController.php` | 78 |
| `app\Http\Controllers\Web\StorefrontController.php` | 83 |
| `app\Http\Controllers\Web\StoreWebController.php` | 79 |
| `app\Http\Controllers\WebController.php` | 21 |
| `app\Http\Kernel.php` | 43 |
| `app\Http\Middleware\Authenticate.php` | 14 |
| `app\Http\Middleware\EncryptCookies.php` | 14 |
| `app\Http\Middleware\EnsureEmailIsVerified.php` | 23 |
| `app\Http\Middleware\EnsureRole.php` | 23 |
| `app\Http\Middleware\EnsureStoreExists.php` | 20 |
| `app\Http\Middleware\EnsureUserHasStore.php` | 47 |
| `app\Http\Middleware\ForceCorsHeaders.php` | 51 |
| `app\Http\Middleware\HandleInertiaRequests.php` | 41 |
| `app\Http\Middleware\HasStore.php` | 26 |
| `app\Http\Middleware\PreventRequestsDuringMaintenance.php` | 14 |
| `app\Http\Middleware\RedirectAfterLogin.php` | 56 |
| `app\Http\Middleware\RedirectIfAuthenticated.php` | 25 |
| `app\Http\Middleware\RedirectIfHasStore.php` | 22 |
| `app\Http\Middleware\RedirectMerchantWithoutStore.php` | 45 |
| `app\Http\Middleware\RoleMiddleware.php` | 23 |
| `app\Http\Middleware\TrimStrings.php` | 16 |
| `app\Http\Middleware\TrustHosts.php` | 17 |
| `app\Http\Middleware\TrustProxies.php` | 24 |
| `app\Http\Middleware\ValidateSignature.php` | 19 |
| `app\Http\Middleware\VerifyCsrfToken.php` | 14 |
| `app\Http\Requests\Admin\StoreCategoryRequest.php` | 37 |
| `app\Http\Requests\Admin\StoreProductRequest.php` | 57 |
| `app\Http\Requests\Admin\UpdateAppearanceRequest.php` | 25 |
| `app\Http\Requests\Admin\UpdateCategoryRequest.php` | 40 |
| `app\Http\Requests\Admin\UpdateGeneralRequest.php` | 29 |
| `app\Http\Requests\Admin\UpdateNotificationsRequest.php` | 24 |
| `app\Http\Requests\Admin\UpdatePaymentsRequest.php` | 24 |
| `app\Http\Requests\Admin\UpdateProductRequest.php` | 55 |
| `app\Http\Requests\Admin\UpdateShippingRequest.php` | 25 |
| `app\Http\Requests\Admin\UpdateTaxesRequest.php` | 25 |
| `app\Http\Requests\Auth\LoginRequest.php` | 72 |
| `app\Http\Requests\BrandingThemeRequest.php` | 18 |
| `app\Http\Requests\ProfileUpdateRequest.php` | 27 |
| `app\Http\Requests\UpdateProfileRequest.php` | 33 |
| `app\Livewire\Admin\Forms\RoleForm.php` | 10 |
| `app\Models\ActivityLog.php` | 161 |
| `app\Models\Cart.php` | 16 |
| `app\Models\CartProduct.php` | 22 |
| `app\Models\Category.php` | 17 |
| `app\Models\Channel.php` | 61 |
| `app\Models\Claim.php` | 73 |
| `app\Models\Customer.php` | 31 |
| `app\Models\Location.php` | 90 |
| `app\Models\Notification.php` | 73 |
| `app\Models\Order.php` | 78 |
| `app\Models\OrderMessage.php` | 66 |
| `app\Models\OrderProduct.php` | 70 |
| `app\Models\Product.php` | 37 |
| `app\Models\Profile.php` | 73 |
| `app\Models\PublicStore.php` | 35 |
| `app\Models\Rating.php` | 73 |
| `app\Models\Role.php` | 67 |
| `app\Models\Sale.php` | 87 |
| `app\Models\Setting.php` | 55 |
| `app\Models\Store.php` | 44 |
| `app\Models\Tutorial.php` | 35 |
| `app\Models\User.php` | 201 |
| `app\Models\UserSubscription.php` | 21 |
| `app\Policies\ProductPolicy.php` | 15 |
| `app\Providers\AppServiceProvider.php` | 23 |
| `app\Providers\AuthServiceProvider.php` | 24 |
| `app\Providers\BroadcastServiceProvider.php` | 15 |
| `app\Providers\EventServiceProvider.php` | 33 |
| `app\Providers\RouteServiceProvider.php` | 41 |
| `app\Services\CloudinaryService.php` | 146 |
| `app\Services\ColorPaletteService.php` | 62 |
| `app\Services\ExternalProductApi.php` | 89 |
| `app\Services\PexelsApi.php` | 69 |
| `app\Support\MediaUploader.php` | 108 |
| `app\View\Components\AppLayout.php` | 14 |
| `app\View\Components\GuestLayout.php` | 14 |

### `routes`

- Archivos: **6**
- Lineas: **139**

| Archivo | Lineas |
|---|---:|
| `routes\admin.php` | 21 |
| `routes\api.php` | 29 |
| `routes\auth.php` | 43 |
| `routes\channels.php` | 15 |
| `routes\console.php` | 16 |
| `routes\web.php` | 15 |

### `database`

- Archivos: **93**
- Lineas: **4139**

| Archivo | Lineas |
|---|---:|
| `database\.gitignore` | 1 |
| `database\database.sqlite` | 98 |
| `database\database.sqlite.bak` | 154 |
| `database\factories\CartFactory.php` | 22 |
| `database\factories\CategoryFactory.php` | 15 |
| `database\factories\OrderFactory.php` | 26 |
| `database\factories\ProductFactory.php` | 61 |
| `database\factories\RoleFactory.php` | 20 |
| `database\factories\StoreFactory.php` | 19 |
| `database\factories\UserFactory.php` | 30 |
| `database\migrations\2014_10_12_100000_create_password_reset_tokens_table.php` | 25 |
| `database\migrations\2019_08_19_000000_create_failed_jobs_table.php` | 29 |
| `database\migrations\2019_12_14_000001_create_personal_access_tokens_table.php` | 30 |
| `database\migrations\2024_01_01_000003_create_categories_table.php` | 13 |
| `database\migrations\2024_01_01_000004_create_stores_table.php` | 13 |
| `database\migrations\2024_01_01_000005_create_products_table.php` | 13 |
| `database\migrations\2025_01_16_create_orders_table.php` | 64 |
| `database\migrations\2025_05_10_150149_create_roles_table.php` | 27 |
| `database\migrations\2025_05_10_150150_create_users_table.php` | 38 |
| `database\migrations\2025_05_10_160145_create_categories_table.php` | 52 |
| `database\migrations\2025_05_10_160151_create_products_table.php` | 33 |
| `database\migrations\2025_05_10_160152_create_ratings_table.php` | 29 |
| `database\migrations\2025_05_10_160353_create_notifications_table.php` | 28 |
| `database\migrations\2025_05_10_160459_create_settings_table.php` | 28 |
| `database\migrations\2025_05_12_101100_create_locations_table.php` | 32 |
| `database\migrations\2025_05_12_102000_create_profiles_table.php` | 29 |
| `database\migrations\2025_05_12_103000_create_sales_table.php` | 31 |
| `database\migrations\2025_05_12_220818_create_carts_table.php` | 26 |
| `database\migrations\2025_05_12_220834_create_cart_products_table.php` | 27 |
| `database\migrations\2025_05_12_220900_create_orders_table.php` | 30 |
| `database\migrations\2025_05_12_220930_create_order_products_table.php` | 28 |
| `database\migrations\2025_05_12_221002_create_order_messages_table.php` | 26 |
| `database\migrations\2025_05_12_221052_create_claims_table.php` | 28 |
| `database\migrations\2025_05_12_221111_create_channels_table.php` | 26 |
| `database\migrations\2025_05_12_221138_create_tutorials_table.php` | 27 |
| `database\migrations\2025_06_24_233646_add_parent_id_to_categories_table.php` | 29 |
| `database\migrations\2025_09_01_024240_create_user_subscriptions_table.php` | 28 |
| `database\migrations\2025_09_01_140806_add_slug_to_products_table.php` | 25 |
| `database\migrations\2025_09_01_140946_add_unit_price_to_cart_products_table.php` | 25 |
| `database\migrations\2025_09_01_141044_add_store_id_and_status_to_orders_table.php` | 51 |
| `database\migrations\2025_09_02_031628_add_email_verified_at_to_users_table.php` | 29 |
| `database\migrations\2025_09_03_010000_create_stores_table.php` | 34 |
| `database\migrations\2025_09_03_020000_add_store_id_to_products_table.php` | 37 |
| `database\migrations\2025_09_03_020000_add_theme_to_stores_table.php` | 25 |
| `database\migrations\2025_09_03_030000_fix_orders_table_columns.php` | 13 |
| `database\migrations\2025_09_03_210244_create_public_stores_table.php` | 38 |
| `database\migrations\2025_09_03_212138_add_description_to_categories_table.php` | 25 |
| `database\migrations\2025_09_03_230000_make_description_nullable_in_products_table.php` | 38 |
| `database\migrations\2025_09_04_000000_add_visits_to_stores_table.php` | 25 |
| `database\migrations\2025_09_04_130000_create_permission_tables.php` | 95 |
| `database\migrations\2025_09_16_023406_add_profile_fields_and_tokens_to_users_table.php` | 59 |
| `database\migrations\2025_09_22_232852_add_cover_columns_to_stores_table.php` | 26 |
| `database\migrations\2025_09_23_000000_add_logo_and_background_path_columns_to_stores_table.php` | 44 |
| `database\migrations\2025_09_25_033433_create_activity_logs_table.php` | 44 |
| `database\migrations\2025_09_27_012106_add_status_column_to_products_table.php` | 25 |
| `database\migrations\2025_09_27_012934_add_store_id_to_categories_table.php` | 30 |
| `database\migrations\2025_09_28_134038_add_popularity_columns_to_categories_table.php` | 13 |
| `database\migrations\2025_09_28_135458_add_storeid_to_categories_table.php` | 13 |
| `database\migrations\2025_09_28_224258_add_storeid_to_products_table.php` | 13 |
| `database\migrations\2025_10_19_175213_add_settings_fields_to_stores_table.php` | 69 |
| `database\migrations\2025_10_19_231951_add_logo_cover_to_stores_table.php` | 36 |
| `database\migrations\2025_10_20_153454_add_image_path_to_products_table.php` | 22 |
| `database\migrations\2025_10_20_214037_change_products_slug_unique_to_composite.php` | 25 |
| `database\migrations\2025_10_26_205957_create_sessions_table.php` | 28 |
| `database\migrations\2025_11_01_000000_add_promo_fields_to_products_table.php` | 26 |
| `database\migrations\2025_12_03_152323_add_is_on_promotion_to_products_table.php` | 25 |
| `database\migrations\2025_12_03_152931_add_soft_deletes_to_stores_table.php` | 25 |
| `database\migrations\2025_12_07_013337_create_order_items_table.php` | 25 |
| `database\migrations\2026_02_05_000000_add_role_to_users_table.php` | 29 |
| `database\migrations\2026_02_05_000001_add_social_links_to_stores_table.php` | 29 |
| `database\migrations\2026_02_06_150000_add_media_url_columns_to_stores_and_products.php` | 36 |
| `database\migrations\2026_02_13_170000_create_customers_table.php` | 28 |
| `database\migrations\2026_02_15_000001_add_wompi_fields_to_orders_table.php` | 53 |
| `database\migrations_archive\fase4b_20260206\2024_01_01_000003_create_categories_table.php` | 26 |
| `database\migrations_archive\fase4b_20260206\2024_01_01_000004_create_stores_table.php` | 35 |
| `database\migrations_archive\fase4b_20260206\2024_01_01_000005_create_products_table.php` | 33 |
| `database\migrations_archive\fase4b_20260206\2025_09_03_030000_fix_orders_table_columns.php` | 73 |
| `database\migrations_archive\fase4b_20260206\2025_09_28_134038_add_popularity_columns_to_categories_table.php` | 83 |
| `database\migrations_archive\fase4b_20260206\2025_09_28_135458_add_storeid_to_categories_table.php` | 74 |
| `database\migrations_archive\fase4b_20260206\2025_09_28_224258_add_storeid_to_products_table.php` | 70 |
| `database\seeders\CategorySeeder.php` | 29 |
| `database\seeders\ComercioPlusSeeder.php` | 730 |
| `database\seeders\DatabaseInsertInstructions.md` | 254 |
| `database\seeders\DatabaseSeeder.php` | 16 |
| `database\seeders\PermissionsSeeder.php` | 21 |
| `database\seeders\ProductionMinimalSeeder.php` | 107 |
| `database\seeders\ProductSeeder.php` | 15 |
| `database\seeders\RoleSeeder.php` | 20 |
| `database\seeders\StoreSeeder.php` | 11 |
| `database\seeders\TestUserSeeder.php` | 66 |
| `database\seeds\sample_products.sql` | 53 |
| `database\testing.sqlite` | 4 |
| `database\testing.sqlite-journal` | 1 |

### `config`

- Archivos: **17**
- Lineas: **1457**

| Archivo | Lineas |
|---|---:|
| `config\app.php` | 182 |
| `config\auth.php` | 66 |
| `config\broadcasting.php` | 58 |
| `config\cache.php` | 92 |
| `config\cloudinary.php` | 8 |
| `config\cors.php` | 45 |
| `config\database.php` | 194 |
| `config\filesystems.php` | 64 |
| `config\hashing.php` | 44 |
| `config\logging.php` | 104 |
| `config\mail.php` | 106 |
| `config\permission.php` | 155 |
| `config\queue.php` | 79 |
| `config\sanctum.php` | 45 |
| `config\services.php` | 16 |
| `config\session.php` | 169 |
| `config\view.php` | 30 |

### `resources/css`

- Archivos: **2**
- Lineas: **69**

| Archivo | Lineas |
|---|---:|
| `resources\css\app.css` | 63 |
| `resources\css\forms.css` | 6 |

### `resources/js`

- Archivos: **51**
- Lineas: **4350**

| Archivo | Lineas |
|---|---:|
| `resources\js\api\client.js` | 12 |
| `resources\js\app.js` | 4 |
| `resources\js\App.vue` | 27 |
| `resources\js\bootstrap.js` | 26 |
| `resources\js\components\AuthLogo.vue` | 12 |
| `resources\js\components\BrandAIPromo.vue` | 22 |
| `resources\js\components\CategoryChip.vue` | 5 |
| `resources\js\components\Footer.vue` | 195 |
| `resources\js\components\Header.vue` | 22 |
| `resources\js\components\HeroLogin.vue` | 103 |
| `resources\js\components\Navbar.vue` | 194 |
| `resources\js\components\NavigationBar.vue` | 49 |
| `resources\js\components\ProductCard.vue` | 48 |
| `resources\js\components\ProductsToolbar.vue` | 51 |
| `resources\js\components\StoreCard.vue` | 16 |
| `resources\js\components\Trustbar.vue` | 9 |
| `resources\js\components\ui\Button.vue` | 61 |
| `resources\js\composables\useProducts.js` | 32 |
| `resources\js\dashboard\products.js` | 336 |
| `resources\js\layouts\DashboardLayout.vue` | 63 |
| `resources\js\lib\utils.ts` | 8 |
| `resources\js\Pages\Cart.vue` | 6 |
| `resources\js\Pages\Cart\Index.vue` | 168 |
| `resources\js\Pages\Categories\Show.vue` | 289 |
| `resources\js\Pages\Checkout.vue` | 6 |
| `resources\js\Pages\Checkout\Index.vue` | 401 |
| `resources\js\Pages\Dashboard\Index.vue` | 136 |
| `resources\js\Pages\Home.vue` | 6 |
| `resources\js\Pages\Login.vue` | 142 |
| `resources\js\Pages\NotFound.vue` | 9 |
| `resources\js\Pages\Orders.vue` | 6 |
| `resources\js\Pages\ProductDetail.vue` | 25 |
| `resources\js\Pages\Products.vue` | 50 |
| `resources\js\Pages\Products\Index.vue` | 367 |
| `resources\js\Pages\Products\Show.vue` | 415 |
| `resources\js\Pages\ProductsDashboard.vue` | 20 |
| `resources\js\Pages\Profile.vue` | 6 |
| `resources\js\Pages\Register.vue` | 173 |
| `resources\js\Pages\Settings.vue` | 6 |
| `resources\js\Pages\StoreCreate.vue` | 35 |
| `resources\js\Pages\Stores\Index.vue` | 85 |
| `resources\js\Pages\Stores\Show.vue` | 307 |
| `resources\js\Pages\StoresList.vue` | 18 |
| `resources\js\Pages\Welcome.vue` | 69 |
| `resources\js\router\index.js` | 32 |
| `resources\js\stores\auth.ts` | 42 |
| `resources\js\stores\cart.ts` | 66 |
| `resources\js\stores\products.ts` | 72 |
| `resources\js\types\index.ts` | 75 |
| `resources\js\vue-test.js` | 8 |
| `resources\js\ziggy-stub.js` | 15 |

### `resources/views`

- Archivos: **95**
- Lineas: **4614**

| Archivo | Lineas |
|---|---:|
| `resources\views\admin\categories\create.blade.php` | 23 |
| `resources\views\admin\categories\edit.blade.php` | 23 |
| `resources\views\admin\categories\index.blade.php` | 71 |
| `resources\views\admin\dashboard.blade.php` | 80 |
| `resources\views\admin\ext-products\index.blade.php` | 146 |
| `resources\views\admin\products\card.blade.php` | 35 |
| `resources\views\admin\products\create.blade.php` | 242 |
| `resources\views\admin\products\edit.blade.php` | 129 |
| `resources\views\admin\products\index.blade.php` | 79 |
| `resources\views\admin\products\show.blade.php` | 137 |
| `resources\views\admin\settings\index.blade.php` | 83 |
| `resources\views\admin\settings\tabs\_appearance.blade.php` | 39 |
| `resources\views\admin\settings\tabs\_general.blade.php` | 60 |
| `resources\views\admin\settings\tabs\_notifications.blade.php` | 19 |
| `resources\views\admin\settings\tabs\_payments.blade.php` | 19 |
| `resources\views\admin\settings\tabs\_shipping.blade.php` | 29 |
| `resources\views\admin\settings\tabs\_taxes.blade.php` | 27 |
| `resources\views\admin\stats\index.blade.php` | 157 |
| `resources\views\admin\store\appearance.blade.php` | 102 |
| `resources\views\admin\users\create.blade.php` | 64 |
| `resources\views\admin\users\edit.blade.php` | 68 |
| `resources\views\admin\users\index.blade.php` | 58 |
| `resources\views\admin\users\show.blade.php` | 38 |
| `resources\views\app.blade.php` | 18 |
| `resources\views\auth\confirm-password.blade.php` | 22 |
| `resources\views\auth\dashboard.blade.php` | 19 |
| `resources\views\auth\forgot-password.blade.php` | 21 |
| `resources\views\auth\login.blade.php` | 39 |
| `resources\views\auth\passwords\email.blade.php` | 34 |
| `resources\views\auth\register.blade.php` | 42 |
| `resources\views\auth\reset-password.blade.php` | 32 |
| `resources\views\auth\verify-email.blade.php` | 26 |
| `resources\views\categories\create.blade.php` | 26 |
| `resources\views\categories\edit.blade.php` | 27 |
| `resources\views\categories\index.blade.php` | 51 |
| `resources\views\components\application-logo.blade.php` | 3 |
| `resources\views\components\auth-session-status.blade.php` | 6 |
| `resources\views\components\category-chip.blade.php` | 2 |
| `resources\views\components\danger-button.blade.php` | 3 |
| `resources\views\components\dropdown.blade.php` | 31 |
| `resources\views\components\dropdown-link.blade.php` | 1 |
| `resources\views\components\footer.blade.php` | 5 |
| `resources\views\components\header.blade.php` | 18 |
| `resources\views\components\input-error.blade.php` | 8 |
| `resources\views\components\input-label.blade.php` | 4 |
| `resources\views\components\modal.blade.php` | 75 |
| `resources\views\components\nav-link.blade.php` | 9 |
| `resources\views\components\primary-button.blade.php` | 3 |
| `resources\views\components\product-card.blade.php` | 26 |
| `resources\views\components\responsive-nav-link.blade.php` | 9 |
| `resources\views\components\secondary-button.blade.php` | 3 |
| `resources\views\components\store-card.blade.php` | 26 |
| `resources\views\components\text-input.blade.php` | 2 |
| `resources\views\components\trustbar.blade.php` | 7 |
| `resources\views\dashboard.blade.php` | 16 |
| `resources\views\dashboard\branding.blade.php` | 78 |
| `resources\views\dashboard\index.blade.php` | 0 |
| `resources\views\dashboard\products.blade.php` | 141 |
| `resources\views\errors\404.blade.php` | 15 |
| `resources\views\layouts\admin.blade.php` | 155 |
| `resources\views\layouts\app.blade.php` | 31 |
| `resources\views\layouts\dashboard.blade.php` | 93 |
| `resources\views\layouts\guest.blade.php` | 29 |
| `resources\views\layouts\marketing.blade.php` | 23 |
| `resources\views\layouts\navigation.blade.php` | 88 |
| `resources\views\layouts\storefront.blade.php` | 58 |
| `resources\views\layouts\test-comercioplus.ps1` | 49 |
| `resources\views\livewire\admin\forms\role-form.blade.php` | 3 |
| `resources\views\partials\dashboard\sidebar.blade.php` | 36 |
| `resources\views\partials\footer.blade.php` | 3 |
| `resources\views\partials\navbar.blade.php` | 10 |
| `resources\views\partials\sidebar.blade.php` | 35 |
| `resources\views\products\create.blade.php` | 60 |
| `resources\views\products\create_corrected.blade.php` | 0 |
| `resources\views\products\edit.blade.php` | 64 |
| `resources\views\products\index.blade.php` | 201 |
| `resources\views\profile\edit.blade.php` | 26 |
| `resources\views\profile\partials\delete-user-form.blade.php` | 45 |
| `resources\views\profile\partials\update-password-form.blade.php` | 41 |
| `resources\views\profile\partials\update-profile-information-form.blade.php` | 54 |
| `resources\views\settings\profile.blade.php` | 111 |
| `resources\views\store\create.blade.php` | 76 |
| `resources\views\store\index.blade.php` | 58 |
| `resources\views\store_wizard.blade.php` | 20 |
| `resources\views\storefront\index.blade.php` | 51 |
| `resources\views\storefront\show.blade.php` | 30 |
| `resources\views\stores\create.blade.php` | 80 |
| `resources\views\stores\create.blade.php.backup` | 68 |
| `resources\views\tailwind-test.blade.php` | 12 |
| `resources\views\users\create.blade.php` | 69 |
| `resources\views\users\edit.blade.php` | 76 |
| `resources\views\users\index.blade.php` | 72 |
| `resources\views\vendor\pagination\tailwind.blade.php` | 96 |
| `resources\views\vue-test.blade.php` | 14 |
| `resources\views\welcome.blade.php` | 101 |

### `comercio-plus-frontend/src`

- Archivos: **76**
- Lineas: **8686**

| Archivo | Lineas |
|---|---:|
| `comercio-plus-frontend\src\app\App.tsx` | 134 |
| `comercio-plus-frontend\src\app\category\page.tsx` | 81 |
| `comercio-plus-frontend\src\app\dashboard\customers\page.tsx` | 119 |
| `comercio-plus-frontend\src\app\dashboard\page.tsx` | 234 |
| `comercio-plus-frontend\src\app\dashboard\products\page.tsx` | 502 |
| `comercio-plus-frontend\src\app\dashboard\store\page.tsx` | 361 |
| `comercio-plus-frontend\src\app\globals.css` | 109 |
| `comercio-plus-frontend\src\app\how-it-works\page.tsx` | 34 |
| `comercio-plus-frontend\src\app\login\page.tsx` | 91 |
| `comercio-plus-frontend\src\app\main.tsx` | 15 |
| `comercio-plus-frontend\src\app\page.tsx` | 94 |
| `comercio-plus-frontend\src\app\privacy\page.tsx` | 18 |
| `comercio-plus-frontend\src\app\product\page.tsx` | 171 |
| `comercio-plus-frontend\src\app\products\page.tsx` | 271 |
| `comercio-plus-frontend\src\app\register\page.tsx` | 140 |
| `comercio-plus-frontend\src\app\store\create\page.tsx` | 15 |
| `comercio-plus-frontend\src\app\store\page.tsx` | 144 |
| `comercio-plus-frontend\src\app\stores\page.tsx` | 315 |
| `comercio-plus-frontend\src\app\terms\page.tsx` | 18 |
| `comercio-plus-frontend\src\components\auth\RequireAuth.tsx` | 20 |
| `comercio-plus-frontend\src\components\auth\RequireRole.tsx` | 32 |
| `comercio-plus-frontend\src\components\Badge.tsx` | 24 |
| `comercio-plus-frontend\src\components\Button.tsx` | 64 |
| `comercio-plus-frontend\src\components\Card.tsx` | 51 |
| `comercio-plus-frontend\src\components\Footer.tsx` | 363 |
| `comercio-plus-frontend\src\components\Header.tsx` | 99 |
| `comercio-plus-frontend\src\components\Icon.tsx` | 195 |
| `comercio-plus-frontend\src\components\Input.tsx` | 74 |
| `comercio-plus-frontend\src\components\layouts\AppShell.tsx` | 39 |
| `comercio-plus-frontend\src\components\layouts\AuthLayout.tsx` | 99 |
| `comercio-plus-frontend\src\components\layouts\DashboardLayout.tsx` | 156 |
| `comercio-plus-frontend\src\components\layouts\PublicLayout.tsx` | 18 |
| `comercio-plus-frontend\src\components\Navbar.tsx` | 348 |
| `comercio-plus-frontend\src\components\ProductCard.tsx` | 64 |
| `comercio-plus-frontend\src\components\products\ProductCard.tsx` | 100 |
| `comercio-plus-frontend\src\components\products\ProductQuickViewModal.tsx` | 157 |
| `comercio-plus-frontend\src\components\Sidebar.tsx` | 57 |
| `comercio-plus-frontend\src\components\StoreCard.tsx` | 58 |
| `comercio-plus-frontend\src\components\theme\ThemeToggle.tsx` | 28 |
| `comercio-plus-frontend\src\components\ui\Badge.tsx` | 22 |
| `comercio-plus-frontend\src\components\ui\button.tsx` | 40 |
| `comercio-plus-frontend\src\components\ui\Card.tsx` | 12 |
| `comercio-plus-frontend\src\components\ui\GlassCard.tsx` | 21 |
| `comercio-plus-frontend\src\components\ui\Input.tsx` | 54 |
| `comercio-plus-frontend\src\components\ui\Select.tsx` | 35 |
| `comercio-plus-frontend\src\components\ui\StatCard.tsx` | 24 |
| `comercio-plus-frontend\src\components\ui\Tabs.tsx` | 32 |
| `comercio-plus-frontend\src\components\ui\Textarea.tsx` | 29 |
| `comercio-plus-frontend\src\context\CartContext.tsx` | 109 |
| `comercio-plus-frontend\src\hooks\useRequireStore.tsx` | 83 |
| `comercio-plus-frontend\src\hooks\useTheme.ts` | 9 |
| `comercio-plus-frontend\src\lib\api.ts` | 1 |
| `comercio-plus-frontend\src\lib\api-response.ts` | 16 |
| `comercio-plus-frontend\src\lib\format.ts` | 33 |
| `comercio-plus-frontend\src\lib\runtime.ts` | 34 |
| `comercio-plus-frontend\src\pages\Cart.tsx` | 175 |
| `comercio-plus-frontend\src\pages\Checkout.tsx` | 377 |
| `comercio-plus-frontend\src\pages\CreateStore.tsx` | 277 |
| `comercio-plus-frontend\src\pages\DashboardStore.tsx` | 335 |
| `comercio-plus-frontend\src\pages\ForgotPassword.tsx` | 54 |
| `comercio-plus-frontend\src\pages\Home.tsx` | 226 |
| `comercio-plus-frontend\src\pages\HowItWorks.tsx` | 119 |
| `comercio-plus-frontend\src\pages\Login.tsx` | 301 |
| `comercio-plus-frontend\src\pages\ProductDetail.tsx` | 85 |
| `comercio-plus-frontend\src\pages\ProductList.tsx` | 230 |
| `comercio-plus-frontend\src\pages\Products.tsx` | 104 |
| `comercio-plus-frontend\src\pages\SimpleContentPage.tsx` | 31 |
| `comercio-plus-frontend\src\pages\StoreProducts.tsx` | 293 |
| `comercio-plus-frontend\src\providers\theme-provider.tsx` | 56 |
| `comercio-plus-frontend\src\services\api.ts` | 40 |
| `comercio-plus-frontend\src\services\auth-session.ts` | 27 |
| `comercio-plus-frontend\src\services\unsplashService.ts` | 253 |
| `comercio-plus-frontend\src\services\uploads.ts` | 65 |
| `comercio-plus-frontend\src\types\api.ts` | 76 |
| `comercio-plus-frontend\src\types\index.ts` | 33 |
| `comercio-plus-frontend\src\utils\imageUtils.ts` | 63 |

### `tests`

- Archivos: **28**
- Lineas: **1203**

| Archivo | Lineas |
|---|---:|
| `tests\CreatesApplication.php` | 16 |
| `tests\e2e\auth.spec.js` | 23 |
| `tests\Feature\Auth\AuthenticationTest.php` | 40 |
| `tests\Feature\Auth\EmailVerificationTest.php` | 43 |
| `tests\Feature\Auth\PasswordConfirmationTest.php` | 32 |
| `tests\Feature\Auth\PasswordResetTest.php` | 52 |
| `tests\Feature\Auth\PasswordUpdateTest.php` | 41 |
| `tests\Feature\Auth\RegistrationTest.php` | 24 |
| `tests\Feature\AuthSanctumTest.php` | 27 |
| `tests\Feature\BasicApiTest.php` | 76 |
| `tests\Feature\CartApiTest.php` | 58 |
| `tests\Feature\CategoriesApiTest.php` | 53 |
| `tests\Feature\CheckoutFlowTest.php` | 40 |
| `tests\Feature\ExampleTest.php` | 15 |
| `tests\Feature\HomePageTest.php` | 18 |
| `tests\Feature\OrderStatusFlowTest.php` | 65 |
| `tests\Feature\PermissionsTest.php` | 78 |
| `tests\Feature\ProductApiTest.php` | 72 |
| `tests\Feature\ProfileTest.php` | 78 |
| `tests\Feature\RegistrationTest.php` | 25 |
| `tests\Feature\StoresApiTest.php` | 47 |
| `tests\Feature\SubscriptionsApiTest.php` | 47 |
| `tests\Feature\UsersApiTest.php` | 75 |
| `tests\Feature\WebRoutesTest.php` | 24 |
| `tests\Pest.php` | 7 |
| `tests\TestCase.php` | 13 |
| `tests\Unit\ExampleTest.php` | 13 |
| `tests\Unit\Models\UserTest.php` | 101 |

### `tests-e2e`

- Archivos: **1**
- Lineas: **31**

| Archivo | Lineas |
|---|---:|
| `tests-e2e\smoke.spec.ts` | 31 |


