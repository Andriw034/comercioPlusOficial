# Auditoria e integracion - Ingreso por escaner (IN)

Fecha: 2026-02-22

## 1) Hallazgos de auditoria

### Backend relevante
- Rutas API: `routes/api.php`
- Inventario:
  - `app/Http/Controllers/Api/InventoryController.php`
  - `app/Services/InventoryService.php`
  - `app/Models/InventoryMovement.php`
- Picking:
  - `app/Http/Controllers/Api/Merchant/OrderPickingController.php`
  - `app/Models/Order.php`
  - `app/Models/OrderProduct.php`
  - `app/Models/ProductCode.php`
- Flujo actual OUT por pago/orden:
  - `app/Observers/OrderObserver.php`
  - `app/Services/OrderBillingService.php`
  - `app/Providers/AppServiceProvider.php` (registro observer)

### Frontend relevante
- Router dashboard: `comercio-plus-frontend/src/app/App.tsx`
- Cliente API: `comercio-plus-frontend/src/lib/api.ts`
- Inventario dashboard actual: `comercio-plus-frontend/src/app/dashboard/inventory/page.tsx`
- Sidebar dashboard: `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`
- Picking UI actual: `comercio-plus-frontend/src/app/dashboard/orders/picking/page.tsx`

### Estado encontrado
- Ya existe ledger de inventario: `inventory_movements`.
- Ya existe stock real en `products.stock`.
- Ya existe flujo picking OUT (scan/manual) separado de inventario.
- OUT actual de inventario se sigue disparando por transicion de estado de orden pagada via `OrderObserver`/`OrderBillingService` (no por `complete picking`).
- No existia flujo dedicado `IN` por escaner para comerciante.

## 2) Implementacion realizada (IN por escaner)

### Backend
- Nuevo controlador:
  - `app/Http/Controllers/Api/Merchant/InventoryReceiveController.php`
- Nuevas rutas protegidas Sanctum:
  - `POST /api/merchant/inventory/scan-in`
  - `POST /api/merchant/inventory/create-from-scan`
  - `GET /api/merchant/inventory/movements`
- Seguridad:
  - valida usuario comerciante y tienda propia.
- Trazabilidad:
  - siempre registra `inventory_movements` tipo `purchase` (entrada IN).
  - actualiza `products.stock` en transaccion.
- Idempotencia:
  - soporte por `request_id` opcional, evitando duplicados por reintento.

### Migracion de ledger
- Nuevo archivo:
  - `database/migrations/2026_02_22_000015_add_receive_fields_to_inventory_movements_table.php`
- Campos agregados:
  - `reason` (nullable)
  - `reference` (nullable)
  - `request_id` (nullable + unique)

### Modelo
- Actualizado:
  - `app/Models/InventoryMovement.php`
- Se agregan a fillable:
  - `reason`, `reference`, `request_id`

### Tests backend
- Nuevo archivo:
  - `tests/Feature/InventoryReceiveApiTest.php`
- Casos cubiertos:
  - scan-in existente: suma stock + crea movimiento IN
  - scan-in con `request_id` repetido: no duplica
  - scan-in inexistente: 404 `PRODUCT_NOT_FOUND`
  - create-from-scan: crea producto + codigo + movimiento IN + stock inicial

### Frontend
- Nuevo servicio API:
  - `comercio-plus-frontend/src/services/inventoryReceive.ts`
- Nuevo hook:
  - `comercio-plus-frontend/src/hooks/useInventoryReceive.ts`
- Nueva pantalla:
  - `comercio-plus-frontend/src/app/dashboard/inventory/receive/page.tsx`
- Router:
  - `comercio-plus-frontend/src/app/App.tsx` agrega `/dashboard/inventory/receive`
- Navegacion:
  - `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx`
  - `comercio-plus-frontend/src/app/dashboard/inventory/page.tsx` agrega boton de acceso

## 3) Resultado funcional
- Flujo IN:
  - escanea codigo + qty
  - si existe: suma stock y registra movimiento
  - si no existe: muestra opcion alta rapida (crear producto y sumar)
- UX:
  - foco continuo en input de escaner
  - feedback humano en espanol
  - toggle de sonido opcional (persistido en localStorage)
  - historial de ultimos ingresos

## 4) Compatibilidad y no regresion
- No se cambiaron contratos existentes de picking OUT.
- No se removieron rutas existentes de inventario.
- Se mantuvo auth con Sanctum y convencion `/api`.

## 5) Riesgos residuales detectados
- El flujo OUT global actual sigue ligado al estado de orden pagada (observer), no a `complete picking`.
- Si el objetivo final es OUT solo al completar picking, eso requiere un PR dedicado de migracion de regla de negocio.

## 6) Limpieza legacy aplicada (order_items)
- Se retiro el uso de `order_items` del seeder principal:
  - `database/seeders/ComercioPlusSeeder.php`
- Se agrego migracion de limpieza:
  - `database/migrations/2026_02_22_000016_drop_legacy_order_items_table.php`
- Objetivo:
  - consolidar lineas de pedido en `order_products` para evitar ambiguedad funcional.

## 7) Verificacion posterior a limpieza
- Migracion ejecutada correctamente:
  - `php artisan migrate --force`
- Pruebas de integracion criticas en verde:
  - `Tests\\Feature\\InventoryReceiveApiTest`
  - `Tests\\Feature\\OrderPickingApiTest`
  - `Tests\\Feature\\FullCommerceFlowTest`
- Resultado:
  - 11 pruebas, 112 aserciones, sin regresiones en flujo IN/OUT validado.

## 8) Limpieza de repo no funcional (higiene)
- Eliminados artefactos que no participan en ejecucion:
  - `exports/`
  - `informe/`
  - `inforem/`
  - `colorful-creative-login-form/`
  - `playwright-report/`
  - `test-results/`
  - temporales `.tmp_*`, `tmp_user_php.txt`, `vite.log`, `vercel.root.backup.json`
- Actualizado `.gitignore` para evitar reingreso de esos artefactos.
