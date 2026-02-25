<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# INTEGRADO_TESTING_QA

Fecha de consolidacion: 2026-02-25
Base: planes de pruebas manuales + QA E2E historico.

## 1) Objetivo unificado
Validar de forma repetible que:
1. Rutas y vistas criticas renderizan sin errores.
2. Flujos de negocio core funcionan end-to-end.
3. No hay regresiones en auth, carrito, checkout, dashboard merchant.

## 2) Cobertura minima obligatoria

### A. Frontend React actual
- Publico: `/`, `/stores`, `/store/:id`, `/products`, `/cart`, `/checkout`.
- Auth: `/login`, `/register`, `/forgot-password`.
- Merchant: `/dashboard`, `/dashboard/store`, `/dashboard/products`, `/dashboard/orders`, `/dashboard/inventory`.

### B. API backend
- Auth: login/register/me/logout.
- Store/product/category CRUD merchant.
- Orders + merchant orders + status.
- Inventory summary/movements/scan-in.
- Uploads media.

## 3) Matriz de pruebas consolidada

### Smoke diario
1. Health API y login merchant.
2. Listado tiendas y apertura tienda publica.
3. Crear producto rapido en dashboard.
4. Crear pedido de prueba y ver en merchant orders.

### Regresion semanal
1. Auth completo (guest/client/merchant).
2. Catalogo y carrito (add/update/remove).
3. Checkout con redirect a login cuando no autenticado.
4. Inventario (entrada IN y ajustes).
5. Picking (si esta activo en entorno).

### E2E por release
1. Registro merchant -> crear tienda -> publicar producto.
2. Cliente -> explorar -> carrito -> checkout -> orden.
3. Merchant -> gestionar orden -> validar stock/estado.

## 4) Criterios de aceptacion
1. Sin errores criticos en consola navegador.
2. Sin 500/401 inesperados en flujo nominal.
3. Build/lint/tests en estado esperado para release.
4. Evidencia documentada (capturas, logs, ids de orden/prueba).

## 5) Herramientas y comandos base
- Backend: `php artisan test`
- Frontend: `npm --prefix comercio-plus-frontend run lint`
- Frontend build: `npm --prefix comercio-plus-frontend run build`
- E2E (si entorno lo permite): `npx playwright test`

## 6) Fuentes consolidadas
- `PLAN_PRUEBAS_EXHAUSTIVAS_ESPAÑOL.md`
- `PLAN_PRUEBAS_VISTAS_VUE.md`
- `TODO_PRUEBAS_EXHAUSTIVAS.md`
- `TODO_TESTING_VIEWS.md`
- `QA_E2E_REPORT.md`
- `QA_REPORT_AUTOMATICO_FULLFLOW.md`
- `docs/e2e-playwright.md`

