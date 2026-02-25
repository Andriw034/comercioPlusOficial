<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# EXECUTION_PLAN_SENIOR

Fecha: 2026-02-25
Proyecto: ComercioPlus (Laravel + Sanctum API + React/Vite + Tailwind)
Alcance: plan senior ejecutable + estado real de implementacion en repo.

---

## 1) Critica senior del analisis previo y huecos detectados

El analisis previo (`TODO_COMPREHENSIVE_ANALYSIS.md`) estaba bien orientado en logout, layout publico, modal de registro forzado y deuda de UX.

Huecos que no estaban cerrados y si afectan producto:

1. No existia mecanismo de contraste automatico por portada/logo.
2. No existia componente robusto para logo/cover reutilizable (1:1 contain y cover controlado).
3. No existia persistencia local de tema de cabecera por tienda (`header_theme`) para estabilizar legibilidad.
4. Iconografia estaba fragmentada (sidebar con emoji hardcoded, resto con lucide).
5. Checkout no exigia autenticacion de forma explicita con redirect de retorno.
6. El modal de registro forzado en `/stores` agregaba friccion en el funnel.

---

## 2) Matriz priorizada de problemas (P0/P1/P2)

| Prioridad | Problema | Impacto negocio | Estado |
|---|---|---|---|
| P0 | Friccion de conversion: modal forzado de registro en entrada a tienda | Alto | Resuelto |
| P0 | Checkout sin gate de auth con redirect de retorno | Alto | Resuelto |
| P0 | Contraste insuficiente en portadas/logos (textos/badges/botones) | Alto | Resuelto base + pendiente QA visual final |
| P1 | Iconografia inconsistente (emoji hardcoded vs iconos vector) | Medio/Alto | Resuelto base (variant global `fa|emoji`) |
| P1 | Navbar/Footer inconsistentes entre rutas publicas | Alto | Resuelto (layout publico unificado) |
| P1 | `alert()` en flujos publicos | Medio | Resuelto en Checkout/Footer/Products |
| P2 | Lint legacy global con errores previos no relacionados a este scope | Medio | Pendiente (deuda existente) |

---

## 3) Plan ejecutable por PRs (orden recomendado)

## PR-01 Quick Wins (flujo cliente + auth checkout + logout unificado)

Objetivo:
- El cliente navega libremente por tiendas y productos.
- Gate de auth solo en checkout.
- Logout consistente (frontend + backend).

Archivos (real):
- `comercio-plus-frontend/src/app/stores/page.tsx`
- `comercio-plus-frontend/src/pages/Checkout.tsx`
- `comercio-plus-frontend/src/app/login/page.tsx`
- `comercio-plus-frontend/src/components/Navbar.tsx`
- `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`

Pasos exactos:
1. Eliminar modal `register-customer` en `/stores` y navegar directo a `/stores/:slug/products`.
2. En checkout, validar token (`getStoredToken`) y redirigir a `/login?redirect=/checkout` si no autenticado.
3. Reemplazar `alert()` por notice UI en checkout.
4. Convertir forgot-password de texto a link real `/forgot-password`.
5. Unificar logout en Navbar y Sidebar con `POST /logout` + `clearSession` + redirect a `/login`.

Criterios de aceptacion:
1. Entrar a una tienda desde `/stores` no pide registro.
2. Usuario anonimo en `/checkout` termina en `/login?redirect=%2Fcheckout`.
3. Al loguear, vuelve a `/checkout`.
4. Carrito se mantiene intacto tras login.
5. Logout desde Navbar y Sidebar invalida sesion visualmente y redirige a login.

Pruebas manuales:
1. Ir a `/stores`, click tienda, validar ingreso directo.
2. Con carrito cargado y sin token, abrir `/checkout` y validar redirect.
3. Login exitoso y retorno a checkout.
4. Ejecutar logout desde navbar y desde sidebar.

Riesgos:
- Dependencia en guard de login por query param `redirect`.

Rollback:
- Revertir archivos de PR-01 y restaurar flujo anterior.

Estado: Implementado.

---

## PR-02 UI Contrast + image components robustos

Objetivo:
- Contraste automatico por portada/logo.
- Imagenes robustas para formatos irregulares.
- Persistencia local de `header_theme` por tienda.

Archivos (real):
- `comercio-plus-frontend/src/utils/imageTheme.ts`
- `comercio-plus-frontend/src/utils/cloudinary.ts`
- `comercio-plus-frontend/src/ui/images/LogoImage.tsx`
- `comercio-plus-frontend/src/ui/images/CoverImage.tsx`
- `comercio-plus-frontend/src/pages/DashboardStore.tsx`
- `comercio-plus-frontend/src/pages/StoreProducts.tsx`
- `comercio-plus-frontend/src/app/store/page.tsx`
- `comercio-plus-frontend/src/app/stores/page.tsx`
- `comercio-plus-frontend/src/pages/Home.tsx`
- `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`

Pasos exactos:
1. Crear util `getImageBrightness(url) -> "dark"|"light"` con canvas + cache memory/localStorage.
2. Crear helper de clases adaptativas (`getThemeClassesByBrightness`).
3. Persistir tema por tienda en localStorage (`cp-store-header-theme:{id}`).
4. Crear `LogoImage` (1:1, contain, fondo neutro, padding).
5. Crear `CoverImage` (ratio configurable 21:9/16:9/free, overlay, callback de brillo).
6. Aplicar componentes y contraste en:
   - cabecera publica de tienda,
   - dashboard store preview,
   - cards/hero de tiendas y sidebar.
7. Añadir transformaciones Cloudinary frontend-first cuando URL sea cloudinary (`f_auto,q_auto,c_fill/c_pad,g_auto,w_,h_`).

Criterios de aceptacion:
1. Portada oscura: texto/chips/badges legibles.
2. Portada clara: overlay corrige legibilidad.
3. Logo no se deforma (contain).
4. Cover no se deforma (cover + ratio).
5. Cambiar portada en dashboard actualiza deteccion de contraste.

Pruebas manuales:
1. Subir portada oscura (ej: negro/azul) y validar contraste.
2. Subir portada clara (ej: blanco/beige) y validar contraste.
3. Subir logo rectangular y validar que no se corta ni deforma.
4. Validar cards de `/stores` y hero de `/store/:id`.

Riesgos:
- CORS de imagen externa puede impedir lectura de canvas.

Mitigacion:
- Fallback a `dark` por defecto si canvas falla.

Rollback:
- Revertir nuevos utils/componentes y volver a `<img>` legacy.

Estado: Implementado base tecnica + pendiente QA visual final en navegador real.

---

## PR-03 Icon system (fa/emoji mapping)

Objetivo:
- Capa unica de iconos configurable globalmente (`variant="emoji"|"fa"`).
- Reemplazo de iconos clave en navegacion.

Archivos (real):
- `comercio-plus-frontend/src/ui/icon-config.ts`
- `comercio-plus-frontend/src/ui/icons.ts`
- `comercio-plus-frontend/src/ui/Icon.tsx`
- `comercio-plus-frontend/src/components/Icon.tsx`
- `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`
- `comercio-plus-frontend/src/pages/DashboardStore.tsx`

Pasos exactos:
1. Crear mapeo emoji para iconos clave (`cart`, `store`, `file-text`, `package`, `logout`, etc).
2. Agregar preferencia global en localStorage `cp-icon-variant`.
3. Extender `Icon` para resolver variante.
4. Migrar Sidebar de emoji hardcoded a `Icon` consistente.
5. Agregar selector de variante en dashboard store (global session setting).

Criterios de aceptacion:
1. Cambiar variante a `emoji` refleja iconos en sidebar.
2. Variante `fa` usa iconografia vectorial estable de la app.
3. Iconos clave reemplazados en navegacion de merchant.

Pruebas manuales:
1. Cambiar selector iconografia en dashboard store.
2. Navegar dashboard y validar iconos de pedidos, inventario, tienda, logout.

Riesgos:
- Instalacion de FontAwesome bloqueada por entorno offline/cache.

Estado real:
- Implementado con stack actual (`lucide` + emoji mapping).
- Intento de instalar FontAwesome fallo por `ENOTCACHED` en npm.

Comando fallido (evidencia):
- `npm --prefix comercio-plus-frontend install @fortawesome/react-fontawesome @fortawesome/free-solid-svg-icons`

Plan de cierre cuando haya red:
1. Instalar paquetes FontAwesome.
2. Cambiar renderer `variant="fa"` a `FontAwesomeIcon`.
3. Mantener fallback a lucide para iconos no mapeados.

---

## PR-04 Feedback UX + layout publico consistente

Objetivo:
- Evitar feedback bloqueante.
- Navbar/Footer consistentes en rutas publicas.

Archivos (real):
- `comercio-plus-frontend/src/components/layouts/PublicLayout.tsx`
- `comercio-plus-frontend/src/components/Footer.tsx`
- `comercio-plus-frontend/src/pages/Products.tsx`
- `comercio-plus-frontend/src/pages/ProductDetail.tsx`
- `comercio-plus-frontend/src/pages/HowItWorks.tsx`
- `comercio-plus-frontend/src/pages/StoreProducts.tsx`
- `comercio-plus-frontend/src/pages/Cart.tsx`

Pasos exactos:
1. PublicLayout siempre renderiza Navbar/Footer.
2. Eliminar headers locales duplicados en paginas publicas.
3. Reemplazar `alert()` por feedback inline en Footer y Products.
4. Agregar breadcrumbs basicos en cart/checkout/product detail.

Criterios de aceptacion:
1. Navbar/Footer aparecen en home, stores, products, cart, checkout, how-it-works.
2. No hay `alert()` en flujos publicos principales.
3. Breadcrumb visible en carrito/checkout/detalle.

Pruebas manuales:
1. Recorrer rutas publicas y validar header/footer.
2. Enviar newsletter y validar feedback no bloqueante.
3. Agregar producto en `/products` y validar notice.

Riesgos:
- Cambios visuales por eliminacion de header legacy.

Rollback:
- Restaurar `PublicLayout` condicional y headers por pagina.

Estado: Implementado.

---

## PR-05 Hardening pendiente (deuda transversal)

Objetivo:
- Dejar pipeline sin errores de lint relevantes.

Hallazgos actuales de lint (preexistentes al scope principal):
1. `src/app/dashboard/categories/page.tsx` error `no-unused-expressions`.
2. `src/components/ProductCard.tsx` error `Math.random()` en render.
3. `src/context/CartContext.tsx` error `setState` en effect.
4. warnings de hooks en varios modulos dashboard.

Accion:
- PR tecnico separado para no mezclar con UX release.

---

## 4) Validaciones ejecutadas en este turno

Comandos ejecutados:
1. Busquedas obligatorias (`rg`) de cover/logo, layout, alert/confirm, register-customer, icon libs.
2. Build frontend:
   - `npm --prefix comercio-plus-frontend run build`
   - Resultado: OK (Vite build exitoso).
3. Lint frontend:
   - `npm --prefix comercio-plus-frontend run lint`
   - Resultado: falla por deuda legacy (no por errores nuevos criticos de este scope).

---

## 5) Checklist QA final para release (Vercel + Railway)

## Frontend funcional
1. Navegacion cliente: `/stores -> /stores/:slug/products -> /cart -> /checkout`.
2. Checkout anonimo redirige a login con `redirect=/checkout`.
3. Post-login retorna a checkout y mantiene carrito.
4. Navbar/Footer consistentes en rutas publicas.
5. No `alert()` en checkout/footer/products.

## Contraste e imagenes
1. Probar portada oscura y clara en dashboard store.
2. Verificar legibilidad de textos/chips/overlays en:
   - `/store/:id`
   - `/stores/:slug/products`
   - cards de `/stores` y `/`.
3. Subir logo horizontal/vertical y validar `contain` sin deformacion.
4. Subir portada irregular y validar `cover` + ratio.

## Iconografia
1. Cambiar variante a `emoji` y validar sidebar.
2. Volver a `fa` y validar iconografia vectorial.
3. Verificar iconos clave: tienda, pedidos, inventario, logout, carrito.

## Deploy
1. Build en Vercel con mismo commit del backend API contract.
2. Variables frontend correctas (`VITE_API_URL` o equivalente runtime).
3. Railway/API con CORS y Sanctum correctamente configurados.
4. Smoke test de login/logout/checkout sobre entorno deploy.

---

## 6) Plan de rollback por oleada

1. Si falla PR-01: revertir `stores/page.tsx`, `Checkout.tsx`, `Navbar.tsx`, `Sidebar.tsx`, `login/page.tsx`.
2. Si falla PR-02: revertir `imageTheme.ts`, `cloudinary.ts`, `ui/images/*` y usos en vistas.
3. Si falla PR-03: revertir `ui/icon-config.ts`, `ui/icons.ts`, `components/Icon.tsx`, cambios sidebar.
4. Si falla PR-04: revertir `PublicLayout.tsx` y headers por pagina.

---

## 7) Estado de produccion (resumen ejecutivo)

Implementacion actual deja el flujo cliente significativamente mas limpio (sin gate forzado, auth solo al pagar), eleva legibilidad por contraste automatico y robustece media/logo/cover.

El build de frontend esta OK.

Para cerrar al 99.9% production-ready faltan 2 bloques:
1. QA visual manual final con 2 portadas reales (oscura/clara) en browser.
2. PR de deuda lint legacy no relacionada directamente al alcance UX de esta ola.


