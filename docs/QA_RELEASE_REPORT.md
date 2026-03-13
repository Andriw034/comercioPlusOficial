# QA_RELEASE_REPORT

REPORT_STATUS: COMPLETE_FASE_0_A_4  
REPORT_DATE: 2026-03-05  
EXECUTION_STATUS: FASE 3 (local) y FASE 4 (produccion) ejecutadas con evidencia

## 1) Alcance de este reporte

Este documento cubre:

- FASE 0: inventario y estado real del repo (evidencia levantada).
- FASE 1: consolidacion documental canonica.
- FASE 2: plan de pruebas exhaustivas (checklist paso a paso).
- FASE 3: ejecucion local (sin cambios de logica durante pruebas).
- FASE 4: ejecucion en produccion (Railway + Vercel).

No se inventan features: solo estado real detectado en codigo y rutas.

## 2) Evidencia FASE 0 (inventario sin cambios de codigo)

### 2.1 Comandos ejecutados y resultado

| Comando | Resultado |
|---|---|
| `git status --short` | Repo en estado sucio (modificados y nuevos en backend, frontend y docs). |
| `php artisan --version` | `Laravel Framework 11.47.0`. |
| `php artisan route:list` | `160` rutas Laravel totales. |
| `php artisan route:list --path=api` | `130` rutas API. |
| `php artisan route:list --path=api --json` | Inventario completo API: `23` publicas + `107` protegidas. |
| `composer.json` | Sanctum, PhpSpreadsheet, Spatie Permission, PHPUnit presentes. |
| `package.json` raiz | scripts `build`, `lint`, `test:e2e`, `build:legacy`; Playwright en devDependencies. |
| `comercio-plus-frontend/package.json` | React/Vite/Tailwind/Eslint stack activo. |
| `playwright.config.ts` | Config E2E activa (Chromium + mobile-chrome, web servers local API+frontend). |
| `docs/` inventario | Canonicidad anterior ambigua entre `UNIVERSAL_COMERCIOPLUS.md` y `_AI`. |
| `comercio-plus-frontend/vercel.json` | Rewrites a Railway confirmados para `/api`, `/sanctum`, `/storage`. |
| `config/cors.php` | Origen fijo Vercel + origenes locales + origenes por env. |

### 2.2 Mapa real consolidado

- Backend API en raiz Laravel: SI.
- Frontend React activo en `comercio-plus-frontend`: SI.
- Frontend legacy Vue en `resources/js`: SI (no removido).
- Deploy target detectado:
  - Frontend: `https://comercio-plus-oficial.vercel.app` (inferido por config/script).
  - API: `https://comercioplusoficial-production-d61e.up.railway.app` (rewrites).

## 3) Plan de pruebas exhaustivas (FASE 2)

Formato obligatorio aplicado: `Caso | Precondicion | Pasos | Resultado esperado | Evidencia`.

## 3.A) Pruebas Backend (Laravel API)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| BE-01 Health API | API local arriba | `GET /api/health` | `200` + `{status: ok}` | captura HTTP + body |
| BE-02 Integrations Health | API local + DB/config | `GET /api/health/integrations` | `200` o `503` documentado con detalle DB/cloudinary | status + JSON |
| BE-03 Auth register | DB limpia para email nuevo | `POST /api/register` | `201` + `user` + `token` | payload/request/response |
| BE-04 Auth login valido | Usuario existente | `POST /api/login` | `200` + token | status + token redacted |
| BE-05 Auth login invalido | Credenciales invalidas | `POST /api/login` | `401` + mensaje | status + body |
| BE-06 Me sin token | Ningun bearer | `GET /api/me` | `401` | status + body |
| BE-07 Me con token | Token valido | `GET /api/me` | `200` + role/store flags | status + body |
| BE-08 CRUD categorias | Merchant autenticado | `GET/POST/PUT/DELETE /api/categories` | ciclo CRUD funcional | ids creados/actualizados |
| BE-09 CRUD productos | Merchant autenticado | `POST/PUT/DELETE /api/products` | ciclo CRUD funcional | ids + status |
| BE-10 Inventario import | Merchant + archivo csv/xlsx | `POST /api/inventory/preview` y `/import` | preview y import con conteos | response JSON + conteos |
| BE-11 Pedidos merchant | Merchant con ordenes | `GET /api/merchant/orders` + `PUT status` | lista y cambio de estado | status + orden modificada |
| BE-12 Reportes API | Merchant con data | `GET /api/reports/*` | respuestas `200` y datos coherentes | response por endpoint |

## 3.B) Pruebas Frontend (React)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| FE-01 Landing carga | Frontend local arriba | abrir `/` | render completo sin pantalla en blanco | screenshot + consola limpia |
| FE-02 Login screen | frontend arriba | abrir `/login` | formulario visible + validaciones basicas | screenshot |
| FE-03 Register role select | frontend arriba | abrir `/register`, alternar merchant/client | selector funciona | screenshot + valores |
| FE-04 Guard rutas privadas | sin token | abrir `/dashboard` | redirect a `/login` | url final + screenshot |
| FE-05 Dashboard merchant | login merchant | abrir `/dashboard` | KPIs renderizan | screenshot |
| FE-06 Store editor | merchant con tienda | `/dashboard/store`, editar y guardar | persistencia en API | request/response + screenshot |
| FE-07 Productos dashboard | merchant | crear/editar/eliminar producto | tabla actualiza | screenshot antes/despues |
| FE-08 Categorias dashboard | merchant | crear/editar/eliminar categoria | arbol/lista actualiza | screenshot |
| FE-09 Inventario dashboard | merchant con inventario | abrir `/dashboard/inventory` | stats + tabla + acciones | screenshot |
| FE-10 Scanner receive | merchant | `/dashboard/inventory/receive`, simular codigo | scan-in o error controlado | screenshot + status |
| FE-11 Checkout client | client autenticado + carrito | `/checkout`, completar datos, pagar | redireccion a success | url + screenshot |
| FE-12 Resiliencia API | simular fallo endpoint clave | bloquear endpoint desde devtools/proxy | UI no queda blanca, muestra error/fallback | screenshot error controlado |

## 3.C) Pruebas E2E (Playwright)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| E2E-01 Smoke spec | dependencias e2e instaladas | ejecutar `npm run test:e2e` | suite smoke corre | reporte CLI + html |
| E2E-02 Registro merchant UI | e2e en limpio | flujo register merchant | redireccion dashboard | video/trace |
| E2E-03 Creacion store/product via API | token merchant | llamadas API desde test | store+producto creados | assert ids |
| E2E-04 Registro/login client UI | usuario client nuevo | register y login | redireccion `/` | trace |
| E2E-05 Add to cart from tienda | store con productos | abrir `/stores/:slug/products`, add cart | item en carrito | screenshot + assert |
| E2E-06 Checkout flow | carrito con items | checkout + mock wompi | `/checkout/success` | video + url |
| E2E-07 Merchant ve pedido | login merchant post-checkout | abrir dashboard pedidos | pedido visible y API contiene id | assert API+UI |
| E2E-08 Mobile project | playwright mobile-chrome | repetir smoke basico | sin regresiones responsive criticas | reporte project mobile |

## 3.D) Pruebas de Integracion (API + Front)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| INT-01 Runtime API base dev | `VITE_API_BASE_URL` vacio en dev | iniciar frontend | usa proxy `/api` | logs + network |
| INT-02 Runtime API base prod | build con env faltante | abrir login | error de configuracion controlado | screenshot + consola |
| INT-03 Home datos publicos | API arriba | abrir `/` | tiendas/productos/categorias cargan | network panel |
| INT-04 Stores list | API arriba | abrir `/stores` | consume `/api/public/stores` | request status |
| INT-05 Store products page | tienda valida | `/stores/:slug/products` | consume `/public/stores` + `/products` | requests |
| INT-06 Follow/unfollow tienda | client autenticado | click seguir/dejar seguir | `POST/DELETE /stores/{id}/follow` | status 200 |
| INT-07 Checkout crea orden | client autenticado + cart | finalizar checkout | `POST /orders/create` + `POST /payments/wompi/create` | status y payload |
| INT-08 Merchant products scanner | merchant autenticado | lookup de codigo en productos | respuesta found/not-found coherente | status + body |
| INT-09 Inventory import UI/API | merchant + archivo | preview/import desde UI | UI refleja conteos API | screenshot + response |
| INT-10 Picking UI/API | orden merchant | flujo scan/manual/fallback/complete | estados sincronizados UI/API | requests + UI estado |

## 3.E) Pruebas Produccion (Railway + Vercel)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| PROD-01 Detectar URLs reales | acceso repo | leer `vercel.json`, `config/cors.php`, docs | URLs documentadas sin ambiguedad | tabla urls |
| PROD-02 Vercel home | URL frontend real | `GET /` | `200` html | status + headers |
| PROD-03 Vercel stores | URL frontend real | `GET /stores` | `200` html SPA | status + headers |
| PROD-04 Vercel products | URL frontend real | `GET /products` | `200` html SPA | status |
| PROD-05 Vercel cart | URL frontend real | `GET /cart` | `200` html SPA | status |
| PROD-06 Vercel checkout | URL frontend real | `GET /checkout` | `200` html SPA (o redirect auth esperado) | status + comportamiento |
| PROD-07 Railway health | URL api real | `GET /api/health` | `200` | body |
| PROD-08 Railway public stores | URL api real | `GET /api/public/stores` | `200` con lista/array | body |
| PROD-09 Railway hero images | URL api real | `GET /api/hero-images` | `200` o fallback documentado | status + body |
| PROD-10 Railway barcode public | URL api real | `GET /api/public/barcode/search?code=...` | `200/404` esperado documentado | status + body |
| PROD-11 Rewrites `/api/*` en Vercel | frontend prod | llamar `https://frontend/api/health` | proxied a Railway | status + response headers |
| PROD-12 Drift local vs prod | evidencia local + prod | comparar rutas y respuestas clave | diferencias documentadas | tabla drift |

## 3.F) Pruebas de Seguridad (Auth, CORS, Rate Limit)

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| SEC-01 CORS preflight publico | origen permitido | `OPTIONS /api/public/stores` | headers CORS correctos | headers |
| SEC-02 CORS preflight privado | origen permitido | `OPTIONS /api/me` | headers CORS correctos | headers |
| SEC-03 CORS origen no permitido | origen no permitido | repetir preflight | bloqueo/ausencia de allow-origin | headers |
| SEC-04 `/api/me` sin token | ninguno | `GET /api/me` | `401` | status/body |
| SEC-05 `/api/me` con token valido | token valido | `GET /api/me` | `200` | status/body |
| SEC-06 logout invalida token | token activo | `POST /api/logout`, luego `/api/me` | luego `401` | secuencia status |
| SEC-07 Role guard merchant | token client | abrir rutas dashboard/API merchant | redirect/403 | status + UI |
| SEC-08 Throttle hero-images | burst requests | spam `/api/hero-images` | limit aplicado segun config | status timeline |
| SEC-09 Throttle barcode publico | burst requests | spam `/api/public/barcode/search` | limit aplicado | status timeline |
| SEC-10 Endpoint debug exposure | produccion | `GET /api/_debug/env` | documentar si expuesto y riesgo | status + body redacted |

## 3.G) Pruebas de Performance y resiliencia

| Caso | Precondicion | Pasos | Resultado esperado | Evidencia |
|---|---|---|---|---|
| PERF-01 Home TTFB prod | frontend prod | medir `GET /` | baseline documentado | ms + tool |
| PERF-02 API public stores | api prod | medir `GET /api/public/stores` | baseline documentado | ms + payload |
| PERF-03 API products | api prod | medir `GET /api/products` | baseline documentado | ms + payload |
| PERF-04 Inventario summary | merchant local/prod | medir `/api/inventory/summary` | tiempo aceptable o riesgo documentado | ms + rows |
| PERF-05 Carga lenta endpoint | simular latencia | abrir home/store pages | UI mantiene skeleton/fallback | screenshot |
| PERF-06 Error de endpoint critico | endpoint down simulado | abrir pagina dependiente | no pantalla blanca, mensaje controlado | screenshot |
| PERF-07 Build size check | frontend build | revisar assets generados | tamaÃ±os registrados | listado dist |
| PERF-08 Navegacion rutas clave | frontend preview | `/`, `/stores`, `/products`, `/cart`, `/checkout` | sin crasheos ni loop de redirects | screen recording |

## 4) Anexos de faltantes detectados (separados del reporte de pruebas)

Esta lista registra faltantes/parciales detectados en codigo.  
No mezcla resultados de ejecucion de pruebas.

| ID | Faltante detectado | Estado actual | Prioridad | Pasos de implementacion sugeridos |
|---|---|---|---|---|
| ANX-01 | Dashboard settings usa `/api/settings` no expuesto en rutas API activas | carga defaults + save probable 404 | P0 | exponer rutas `GET/PUT /api/settings` o adaptar UI a endpoints reales de store/tax/payment config |
| ANX-02 | Catalogo global `/products` usa mock local | no usa API real | P1 | reemplazar `mockProducts` por fetch paginado `/api/products` + filtros por categoria/precio |
| ANX-03 | Product detail `/products/:id` usa mock local | detalle no conectado API | P1 | consumir `GET /api/products/{id}`, manejar 404 y estados de stock reales |
| ANX-04 | Historial de pedidos del cliente en UI no existe | solo `/orders/:id` success puntual | P1 | crear pagina `/orders` para cliente con `GET /api/orders` y detalle |
| ANX-05 | Configuracion IVA detallada no visible en UI activa | solo toggle `taxes_enabled` | P1 | integrar formulario completo contra `GET/PUT /api/stores/{store}/tax-settings` (tasa, nombre, include tax, rounding) |
| ANX-06 | Flujo onboarding crear tienda desde UI publica es solo CTA | no formulario wizard en React activo | P2 | crear wizard de creacion de tienda y persistir por `POST /api/stores` |
| ANX-07 | Personalizacion visual de tienda (colores) no implementada | solo logo/cover | P2 | agregar campos de branding en modelo/store API + aplicar en tienda publica |
| ANX-08 | UI publica para busqueda de barcode no disponible | endpoint publico existe | P2 | crear pagina/accion de busqueda barcode para cliente y deep-link a producto/tienda |
| ANX-09 | Verificacion de tienda no tiene entrada visible en router dashboard | endpoints listos | P2 | agregar pagina dashboard de verificacion y flujo upload/estado |

## 5) Ejecucion FASE 3 (local, sin cambios de logica)

| Comando | Resultado real | Estado | Evidencia |
|---|---|---|---|
| `php artisan test` | `121 passed (402 assertions)` | PASS | salida CLI (usuario) |
| `php artisan route:list` | inventario completo de rutas Laravel | PASS | salida CLI (`160` rutas) |
| `npm ci --prefix comercio-plus-frontend` | instalacion completada (`273 packages`) | PASS | salida CLI |
| `npm run lint --prefix comercio-plus-frontend` | `eslint .` sin errores | PASS | salida CLI |
| `npm run build --prefix comercio-plus-frontend` | build React/Vite OK (`dist/` generado) | PASS | salida CLI + listado assets |
| `npm run build:legacy` | build legacy Vite OK (`public/build/manifest.json`) | PASS | salida CLI |
| `npm ls @playwright/test` | `@playwright/test@1.58.2` | PASS | salida CLI |
| `npm run test:e2e` | 2 pruebas: 2 passed (chromium + mobile-chrome) | PASS | salida CLI Playwright |
| smoke preview `npm run preview` + GET `/`,`/stores`,`/products`,`/cart` | todas 200 | PASS | checks HTTP locales |

Notas de ejecucion local:

- `npm ci` y builds requirieron ejecucion fuera de sandbox por restricciones `EPERM/EACCES` del entorno de pruebas.

## 6) Ejecucion FASE 4 (produccion Railway + Vercel)

### 6.1 URLs detectadas (fuente de verdad)

| Item | Valor | Evidencia |
|---|---|---|
| Frontend Vercel | `https://comercio-plus-oficial.vercel.app` | `comercio-plus-frontend/vercel-check.ps1`, `config/cors.php` |
| API Railway | `https://comercioplusoficial-production-d61e.up.railway.app` | `comercio-plus-frontend/vercel.json` rewrites |

### 6.2 Endpoints y paginas en produccion (GET)

| Endpoint | Status |
|---|---|
| `https://comercio-plus-oficial.vercel.app/` | 200 |
| `https://comercio-plus-oficial.vercel.app/stores` | 200 |
| `https://comercio-plus-oficial.vercel.app/products` | 200 |
| `https://comercio-plus-oficial.vercel.app/cart` | 200 |
| `https://comercio-plus-oficial.vercel.app/checkout` | 200 |
| `https://comercio-plus-oficial.vercel.app/api/health` | 200 |
| `https://comercioplusoficial-production-d61e.up.railway.app/api/health` | 200 |
| `https://comercioplusoficial-production-d61e.up.railway.app/api/public/stores` | 200 |
| `https://comercioplusoficial-production-d61e.up.railway.app/api/public/products` | 200 |
| `https://comercioplusoficial-production-d61e.up.railway.app/api/hero-images` | 404 |
| `https://comercioplusoficial-production-d61e.up.railway.app/api/public/barcode/search?code=TEST` | 404 |

### 6.3 CORS preflight (OPTIONS)

Origen probado: `https://comercio-plus-oficial.vercel.app`.

| Endpoint | Status | Allow-Origin | Allow-Methods | Allow-Credentials | Vary |
|---|---|---|---|---|---|
| `/api/public/stores` | 204 | `https://comercio-plus-oficial.vercel.app` | `GET, POST, PUT, PATCH, DELETE, OPTIONS` | `true` | `Origin, Access-Control-Request-Method, Access-Control-Request-Headers` |
| `/api/me` | 204 | `https://comercio-plus-oficial.vercel.app` | `GET, POST, PUT, PATCH, DELETE, OPTIONS` | `true` | `Origin, Access-Control-Request-Method, Access-Control-Request-Headers` |

### 6.4 Auth Sanctum en produccion

| Flujo | Status |
|---|---|
| `POST /api/register` | 201 |
| `POST /api/login` | 200 |
| token emitido en login | SI |
| `GET /api/me` sin token (`Accept: application/json`) | 401 |
| `GET /api/me` con `Bearer` | 200 |
| `GET https://comercio-plus-oficial.vercel.app/api/me` sin token (`Accept: application/json`) | 401 |

### 6.5 Rewrites y resiliencia frontend

| Validacion | Resultado |
|---|---|
| Rewrite Vercel `GET /api/health` hacia Railway | OK (200) |
| Script `comercio-plus-frontend/vercel-check.ps1` en prod | OK |
| SPA rutas (`/dashboard`, `/dashboard/reports`, `/dashboard/inventory`, `/login`) | 200 text/html |
| Assets principales de build en Vercel | 200 |

Conclusiones de resiliencia:

- El frontend en produccion no queda en blanco en rutas principales y dashboard (script de verificacion OK).
- Hay endpoints publicos esperados por codigo local que en produccion responden 404 (drift).

## 7) Drift local vs produccion

| Caso | Local (codigo/rutas) | Produccion | Impacto |
|---|---|---|---|
| `GET /api/hero-images` | ruta declarada en `routes/api.php` | 404 | home puede perder imagenes dinamicas y caer a fallback |
| `GET /api/public/barcode/search` | ruta declarada en `routes/api.php` | 404 | funcionalidad publica de busqueda por codigo no disponible en prod |
| `GET /api/me` sin `Accept: application/json` | en tests API se valida 401 sin token | 302 en curl basico (redirect web), 401 con header JSON | comportamiento mixto cliente web/API; documentado |
| Smoke E2E Chromium | esperado PASS | PASS | flujo completo merchant+client estable en chromium y mobile-chrome |

## 8) Cierre de release QA

Checklist de salida:

1. Documentacion canonica: COMPLETADA.
2. Plan QA exhaustivo A-G: COMPLETADO.
3. Ejecucion local FASE 3: PARCIAL (automatizado y smoke en verde; pendiente matriz manual FE/INT/PERF).
4. Ejecucion produccion FASE 4: PARCIAL (HTTP/CORS/Auth/rewrite OK, drift en 2 endpoints publicos).
5. Anexos de faltantes funcionales: COMPLETADOS (P0/P1/P2).

Estado final para promocion:

- LISTO para exposicion tecnica con evidencia real.
- NO listo para release productivo estricto sin atender:
  - drift de despliegue en `hero-images` y `public/barcode/search`,
  - ejecucion total de casos manuales FE/INT/PERF de la matriz A-G.

## 9) Actualizacion FASE 5 a FASE 7 (2026-03-06)

### 9.1 FASE 3 (validacion local re-ejecutada)

| Comando | Resultado | Estado |
|---|---|---|
| php artisan test | 123 passed (407 assertions) | PASS |
| php artisan route:list | 165 rutas | PASS |
| php artisan route:list --path=api | 135 rutas API | PASS |
| npm ci --prefix comercio-plus-frontend | instalacion completada | PASS |
| npm run lint --prefix comercio-plus-frontend | sin errores | PASS |
| npm run build --prefix comercio-plus-frontend | build OK | PASS |
| npm run build:legacy | build legacy OK | PASS |
| smoke preview (/, /stores, /products, /cart) | 200 en todas | PASS |

### 9.2 FASE 5 (produccion verificada)

- Vercel:
  - /dashboard/products => 200
  - /dashboard/products/create => 200
  - /dashboard/reports => 200
  - rewrite /api/health => 200
- Railway:
  - /api/health => 200
  - /api/public/stores => 200
  - /api/public/products => 200
  - /api/hero-images => 404
  - /api/public/barcode/search?code=TEST => 404
- CORS:
  - OPTIONS /api/public/stores => 204 + Access-Control-Allow-Origin correcto.
  - OPTIONS /api/me => 204 + Access-Control-Allow-Origin correcto.
- Auth:
  - POST /api/register => 201
  - POST /api/login => 200
  - GET /api/me sin token => 401
  - GET /api/me con bearer => 200

### 9.3 FASE 6 y FASE 7

- Rama unica de release: master.
- Sin divergencia: origin/master...HEAD = 0 0.
- Docs operativos actualizados con regla de instancias (:5173 React, :8000 Laravel/legacy), deploy oficial y checklist post-deploy.