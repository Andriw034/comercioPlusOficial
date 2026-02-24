# Maquina de Estados Picking (ComercioPlus)

## Objetivo
Definir transiciones validas para separar:
1. Estado de pago/comercial (`orders.status`).
2. Estado operativo de alistamiento (`orders.fulfillment_status`).

## 1) Estado de pago/comercial (existente)
Campo: `orders.status`

Valores actuales usados en backend:
- `pending`
- `processing`
- `paid`
- `approved`
- `completed`
- `cancelled`

Este estado no debe usarse para contabilizar avance de picking.

## 2) Estado de alistamiento (nuevo)
Campo: `orders.fulfillment_status`

Valores objetivo:
- `pending_pick`
- `picking`
- `picked`
- `packed`
- `ready`
- `delivered`
- `cancelled`

## 3) Reglas de transicion (fulfillment_status)

### 3.1 Inicio
- Estado inicial: `pending_pick`.

### 3.2 Durante alistamiento
- `pending_pick -> picking`
  - Trigger: primer scan/manual valido.

### 3.3 Cierre de picking
- `picking -> picked`
  - Trigger: endpoint `POST /picking/complete`.
  - Condicion recomendada: por cada linea, `qty_picked + qty_missing == quantity`.

### 3.4 Empaque y despacho
- `picked -> packed`
- `packed -> ready`
- `ready -> delivered`

### 3.5 Cancelacion
- Cualquier estado activo puede pasar a `cancelled` por cancelacion de orden.

## 4) Reglas de integridad
1. No permitir volver de `picked` a `pending_pick` sin endpoint de reset autorizado.
2. Reset solo con endpoint explicito y auditoria.
3. Toda transicion debe registrarse en `order_picking_events`.

## 5) Relacion con inventario
1. El cambio de `fulfillment_status` no debe duplicar descuento de stock.
2. Antes de descontar por picking, verificar si existe movimiento de venta en `inventory_movements` para la orden.
3. Si ya existe movimiento `sale` para `reference_type=order` y `reference_id=order.id`, no descontar otra vez.

## 6) Regla de 3 intentos de escaner
1. Fallo consecutivo de scan incrementa `scan_consecutive_failures`.
2. Exito en scan/manual resetea contador a 0.
3. Al llegar a 3 fallos consecutivos:
   - marcar `fallback_required=true` en sesion;
   - responder error `FALLBACK_REQUIRED`;
   - forzar flujo manual en frontend.

## 7) Tabla de eventos sugerida
`order_picking_events.action`:
- `scan_ok`
- `scan_error`
- `manual_pick`
- `manual_missing`
- `manual_note`
- `fallback_triggered`
- `picking_completed`
- `picking_reset`

## 8) Casos especiales
1. Producto escaneado no pertenece a la orden:
   - error `CODE_NOT_IN_ORDER`.
2. Producto ya completo:
   - error `ITEM_ALREADY_COMPLETE`.
3. Cantidad excedida:
   - error `QTY_EXCEEDED`.
4. Codigo no existe:
   - error `CODE_NOT_FOUND`.
5. Tercer fallo consecutivo:
   - error `FALLBACK_REQUIRED`.

