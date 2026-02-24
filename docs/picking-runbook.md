# Runbook de Picking (Operacion y Soporte)

## 1) Proposito
Guia operativa para usar, validar y solucionar incidencias del modulo de alistamiento (scanner + manual).

## 2) Flujo operativo recomendado
1. Abrir pedido en dashboard.
2. Entrar a pantalla de picking.
3. Usar escaner (lector tipo teclado) como camino principal.
4. Si falla 3 veces seguidas, cambiar a modo manual (forzado por sistema).
5. Completar picking cuando no queden unidades pendientes.

## 3) Pre-requisitos tecnicos
1. Usuario autenticado como comerciante.
2. Pedido pertenece a la tienda del comerciante.
3. Productos con codigos registrados (`product_codes`) para uso de escaner.

## 4) Politica de errores de escaneo
1. Error 1 y 2: mostrar mensaje y permitir reintento.
2. Error 3 consecutivo: activar fallback manual.
3. Exito de scan/manual: resetear contador de fallos.

## 5) Endpoints esperados de picking
- `GET /api/merchant/orders/{order}/picking`
- `POST /api/merchant/orders/{order}/picking/scan`
- `POST /api/merchant/orders/{order}/picking/manual`
- `POST /api/merchant/orders/{order}/picking/fallback`
- `POST /api/merchant/orders/{order}/picking/complete`
- `POST /api/merchant/orders/{order}/picking/reset`

## 6) Codigos de error operativos
- `CODE_NOT_FOUND`
- `CODE_NOT_IN_ORDER`
- `ITEM_ALREADY_COMPLETE`
- `QTY_EXCEEDED`
- `INVALID_QTY`
- `SCAN_INVALID_STATE`
- `FALLBACK_REQUIRED`
- `PICKING_INCOMPLETE`

## 7) Checklist de monitoreo diario
1. Revisar ordenes en estado `picking` con antiguedad alta.
2. Revisar cantidad de eventos `scan_error` por tienda.
3. Revisar casos `fallback_triggered` para detectar problemas de codificacion o hardware.
4. Confirmar que no hay movimientos duplicados en inventario por la misma orden.

## 8) Procedimiento ante incidencia

### 8.1 El escaner no reconoce codigos
1. Validar que el codigo exista en `product_codes`.
2. Validar que el codigo pertenezca a la misma tienda de la orden.
3. Validar que el item no este completo.
4. Si persiste, usar modo manual y registrar nota interna.

### 8.2 No deja completar picking
1. Revisar lineas pendientes (`quantity - qty_picked - qty_missing`).
2. Completar con pick manual o marcar faltante.
3. Reintentar endpoint `complete`.

### 8.3 Sospecha de doble descuento de inventario
1. Buscar en `inventory_movements` por:
   - `reference_type = order`
   - `reference_id = order.id`
   - `type = sale`
2. Si hay mas de un movimiento de venta para misma orden, levantar incidencia critica.

## 9) Comandos de verificacion recomendados
1. Backend tests:
   - `php artisan test --filter=OrderPicking`
   - `php artisan test --filter=Inventory`
2. Frontend build:
   - `npm --prefix comercio-plus-frontend run build`

## 10) Definicion de listo operativo
1. Escaneo funcional con fallback obligatorio en 3 fallos.
2. Flujo manual funcional para completar pedidos.
3. Auditoria completa en `order_picking_events`.
4. Sin doble impacto de inventario.
5. Errores visibles y accionables para comerciante.

## 11) Flujo adicional: Ingreso por escaner (IN)
Rutas:
- `POST /api/merchant/inventory/scan-in`
- `POST /api/merchant/inventory/create-from-scan`
- `GET /api/merchant/inventory/movements?limit=10`

Reglas:
1. Si el codigo existe en la tienda, se crea movimiento `purchase` y se incrementa stock.
2. Si no existe, API responde `PRODUCT_NOT_FOUND` para abrir alta rapida.
3. `request_id` opcional evita duplicados por reintento de red.
4. Toda entrada queda trazada en `inventory_movements`.

Pantalla frontend:
- `/dashboard/inventory/receive`

