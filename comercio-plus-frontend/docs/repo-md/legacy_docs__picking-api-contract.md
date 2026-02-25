<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Contrato API de Picking (ComercioPlus)

## 1) Proposito
Este documento define el contrato API para el alistamiento de pedidos (modo escaner + modo manual) en el dashboard del comerciante.

Esta escrito como objetivo de implementacion para los PRs pendientes.

## 2) Realidad actual vs contrato objetivo

### 2.1 Ya existe en el repositorio hoy
- Auth: rutas API protegidas con `auth:sanctum`.
- Listado/actualizacion de pedidos de comerciante:
  - `GET /api/merchant/orders`
  - `PUT /api/merchant/orders/{id}/status`
- Relacion de items usada por controllers/API: `order_products` via `Order::ordenproducts()`.
- Ya existe servicio de inventario (`InventoryService`) y kardex, activado por cambios de estado de pago de la orden.

### 2.2 Nuevo en este contrato (por implementar)
- Endpoints de picking bajo `/api/merchant/orders/{order}/picking`.
- Modelo de codigos de producto (`product_codes`).
- Bitacora de picking (`order_picking_events`).
- Sesion de picking para controlar intentos de escaneo (`order_picking_sessions`).
- Nuevo campo operativo en orders: `fulfillment_status` (separado de `status` de pago).

## 3) Reglas de diseno (obligatorias)
1. No usar `order_items` para logica de picking. Usar `order_products`.
2. No mezclar estado de pago (`orders.status`) con estado de alistamiento (`orders.fulfillment_status`).
3. No aplicar descuento de inventario dos veces.
4. Despues de 3 fallos consecutivos de escaneo, el API debe exigir fallback manual.
5. Toda escritura de picking debe correr en transacciones.

## 4) Autenticacion y autorizacion

### 4.1 Autenticacion
- Bearer token via Sanctum en `Authorization: Bearer <token>`.

### 4.2 Autorizacion
- El usuario debe ser comerciante.
- La orden debe pertenecer a la tienda del comerciante.
- Si orden/tienda no coincide: `403`.

## 5) Modelo de datos (objetivo)

### 5.1 Orders (existente + nuevo)
- `orders.status` se mantiene para pago/flujo comercial.
- Nuevo `orders.fulfillment_status`:
  - `pending_pick`
  - `picking`
  - `picked`
  - `packed`
  - `ready`
  - `delivered`
  - `cancelled`

### 5.2 Lineas de orden (order_products)
- Existentes:
  - `id`
  - `order_id`
  - `product_id`
  - `quantity`
  - `unit_price`
  - `base_price`
  - `tax_amount`
  - `tax_rate_applied`
  - `total_line`
- Nuevos:
  - `qty_picked` (int, default 0)
  - `qty_packed` (int, default 0)
  - `qty_missing` (int, default 0)

### 5.3 Codigos de producto (tabla nueva: `product_codes`)
- `id`
- `product_id`
- `store_id`
- `type` enum: `barcode | qr | sku`
- `value` string
- `is_primary` bool
- llave unica: `(store_id, type, value)`

### 5.4 Eventos de picking (tabla nueva: `order_picking_events`)
- `id`
- `order_id`
- `order_product_id` nullable
- `product_id` nullable
- `user_id`
- `mode` enum: `scanner | manual | system`
- `action` enum:
  - `scan_ok`
  - `scan_error`
  - `manual_pick`
  - `manual_missing`
  - `manual_note`
  - `fallback_triggered`
  - `picking_completed`
  - `picking_reset`
- `code` nullable
- `qty` default 0
- `error_code` nullable
- `message` nullable
- `created_at`

### 5.5 Sesiones de picking (tabla nueva: `order_picking_sessions`)
- `id`
- `order_id`
- `user_id`
- `scan_consecutive_failures` int default 0
- `fallback_required` bool default false
- `last_error_code` nullable
- `last_code` nullable
- `updated_at`

## 6) Envoltorio comun de respuestas

### 6.1 Exito
```json
{
  "message": "string",
  "data": {},
  "meta": {}
}
```

### 6.2 Error de validacion (422)
Usar formato Laravel + metadatos opcionales:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["..."]
  },
  "error_code": "SOME_CODE",
  "meta": {}
}
```

### 6.3 Errores auth/permisos
- `401` no autenticado.
- `403` sin autorizacion para orden/tienda.
- `404` orden no encontrada.

## 7) Endpoints

---
## 7.1 GET /api/merchant/orders/{order}/picking
Devuelve todo el contexto de picking para una orden.

### Request
- Method: `GET`
- Auth: requerido
- Path param:
  - `order` (int)

### Response 200
```json
{
  "message": "Picking context",
  "data": {
    "order": {
      "id": 123,
      "store_id": 9,
      "status": "paid",
      "fulfillment_status": "picking",
      "invoice_number": "FAC-20260222-000123",
      "created_at": "2026-02-22T11:50:00Z"
    },
    "lines": [
      {
        "order_product_id": 501,
        "product_id": 44,
        "product_name": "Cadena 428H",
        "image_url": "https://...",
        "quantity": 3,
        "qty_picked": 1,
        "qty_packed": 0,
        "qty_missing": 0,
        "pending_qty": 2,
        "codes": [
          { "type": "barcode", "value": "7701234567890", "is_primary": true },
          { "type": "sku", "value": "CAD-428H", "is_primary": false }
        ]
      }
    ]
  },
  "meta": {
    "totals": {
      "ordered_units": 8,
      "picked_units": 4,
      "missing_units": 0,
      "completion_pct": 50
    },
    "session": {
      "scan_consecutive_failures": 0,
      "fallback_required": false
    }
  }
}
```

---
## 7.2 POST /api/merchant/orders/{order}/picking/scan
Escanea un codigo (barcode/QR/SKU) en modo escaner.

### Request
- Method: `POST`
- Auth: requerido
- Body:
```json
{
  "code": "7701234567890",
  "qty": 1
}
```

### Validacion
- `code`: requerido, string, max 128.
- `qty`: opcional, int, min 1, max 999 (default 1).

### Exito 200
```json
{
  "message": "Scan applied",
  "data": {
    "line": {
      "order_product_id": 501,
      "product_id": 44,
      "quantity": 3,
      "qty_picked": 2,
      "qty_missing": 0,
      "pending_qty": 1
    },
    "scan": {
      "code": "7701234567890",
      "qty_applied": 1
    }
  },
  "meta": {
    "session": {
      "scan_consecutive_failures": 0,
      "fallback_required": false
    }
  }
}
```

### Error 422 (fallo de escaneo, intento 1 o 2)
```json
{
  "message": "Scan failed",
  "errors": {
    "code": ["Code not found in this store"]
  },
  "error_code": "CODE_NOT_FOUND",
  "meta": {
    "session": {
      "scan_consecutive_failures": 1,
      "fallback_required": false
    }
  }
}
```

### Error 422 (tercer fallo consecutivo)
```json
{
  "message": "Scan failed. Manual fallback required.",
  "errors": {
    "code": ["Fallback required after 3 failed scans"]
  },
  "error_code": "FALLBACK_REQUIRED",
  "meta": {
    "session": {
      "scan_consecutive_failures": 3,
      "fallback_required": true
    }
  }
}
```

### Codigos de error de escaneo
- `CODE_NOT_FOUND`
- `CODE_NOT_IN_ORDER`
- `ITEM_ALREADY_COMPLETE`
- `QTY_EXCEEDED`
- `INVALID_QTY`
- `SCAN_INVALID_STATE`
- `FALLBACK_REQUIRED`

---
## 7.3 POST /api/merchant/orders/{order}/picking/manual
Acciones manuales para fallback o flujo manual normal.

### Request
- Method: `POST`
- Auth: requerido
- Body:
```json
{
  "action": "pick_item",
  "order_product_id": 501,
  "qty": 1,
  "reason": "Manual verification",
  "note": "Package was opened and rechecked"
}
```

### Acciones permitidas
- `pick_item`
- `pick_by_code`
- `mark_missing`
- `add_note`

### Reglas de payload por accion
1. `pick_item`
   - requerido: `order_product_id`, `qty`
2. `pick_by_code`
   - requerido: `code`, opcional `qty`
3. `mark_missing`
   - requerido: `order_product_id`, `qty`, `reason`
4. `add_note`
   - requerido: `order_product_id`, `note`

### Exito 200
```json
{
  "message": "Manual action applied",
  "data": {
    "action": "mark_missing",
    "line": {
      "order_product_id": 501,
      "quantity": 3,
      "qty_picked": 2,
      "qty_missing": 1,
      "pending_qty": 0
    }
  },
  "meta": {
    "session": {
      "scan_consecutive_failures": 0,
      "fallback_required": false
    }
  }
}
```

---
## 7.4 POST /api/merchant/orders/{order}/picking/fallback
Endpoint explicito para registrar decision de fallback.

### Request
```json
{
  "selected_mode": "manual",
  "reason": "three_scan_failures"
}
```

### Exito 200
```json
{
  "message": "Fallback mode activated",
  "data": {
    "selected_mode": "manual"
  },
  "meta": {
    "session": {
      "scan_consecutive_failures": 3,
      "fallback_required": true
    }
  }
}
```

---
## 7.5 POST /api/merchant/orders/{order}/picking/complete
Completa el proceso de picking.

### Request (estricto por default)
```json
{
  "completion_mode": "strict",
  "note": "Optional closing note"
}
```

### Modos de completado
- `strict`:
  - exige en cada linea `qty_picked + qty_missing == quantity`.
  - si queda pendiente, responde 422.

### Exito 200
```json
{
  "message": "Picking completed",
  "data": {
    "order_id": 123,
    "fulfillment_status": "picked"
  },
  "meta": {
    "totals": {
      "ordered_units": 8,
      "picked_units": 7,
      "missing_units": 1,
      "pending_units": 0
    }
  }
}
```

### Error 422
```json
{
  "message": "Picking cannot be completed yet",
  "errors": {
    "order": ["There are pending quantities"]
  },
  "error_code": "PICKING_INCOMPLETE"
}
```

---
## 7.6 POST /api/merchant/orders/{order}/picking/reset
Reinicia el progreso de picking de la orden.

### Request
```json
{
  "confirm": true
}
```

### Exito 200
```json
{
  "message": "Picking reset",
  "data": {
    "order_id": 123,
    "fulfillment_status": "pending_pick"
  }
}
```

## 8) Maquina de estados

### 8.1 Estado de pago/comercial (`orders.status`, existente)
- `pending`
- `processing`
- `paid`
- `approved`
- `completed`
- `cancelled`

### 8.2 Estado de alistamiento (`orders.fulfillment_status`, nuevo)
- `pending_pick` -> `picking` -> `picked` -> `packed` -> `ready` -> `delivered`
- Ruta de cancelacion:
  - cualquier estado activo -> `cancelled`

### 8.3 Reglas de transicion
1. El primer pick valido (scan/manual) mueve `fulfillment_status` de `pending_pick` a `picking`.
2. `complete` mueve a `picked`.
3. Transiciones de empaque/entrega se manejan en flujos posteriores.

## 9) Regla de consistencia de inventario
Los endpoints de picking no deben generar descuentos duplicados de stock.

Comportamiento actual del repositorio:
- El movimiento de venta ya se dispara por flujo de pago de la orden.

Regla de contrato:
1. Antes de cualquier decremento por picking, verificar si ya existe movimiento:
   - `inventory_movements.reference_type = "order"`
   - `inventory_movements.reference_id = order.id`
   - `inventory_movements.type = "sale"`
2. Si existe, no volver a descontar.

## 10) Regla de frontend para 3 fallos de escaneo
Comportamiento requerido en frontend:
1. Mantener input de escaner visible y enfocado.
2. En cada error de scan, actualizar contador local desde `meta.session`.
3. Si `error_code = FALLBACK_REQUIRED` o `meta.session.fallback_required = true`:
   - abrir modal bloqueante de fallback;
   - mostrar opciones:
     - Manual por codigo
     - Manual por seleccion de linea
     - Marcar faltante
     - Reintentar escaner
4. Cualquier accion exitosa (scan/manual) resetea fallos consecutivos a 0.

## 11) Resumen de HTTP status codes
- `200` exito de lectura/accion.
- `401` no autenticado.
- `403` orden/tienda sin permiso.
- `404` orden no encontrada.
- `409` opcional para conflicto/lock (si se implementa).
- `422` validacion o reglas de dominio.
- `500` error interno inesperado.

## 12) Resumen tipo OpenAPI

### Seguridad
- `bearerAuth` requerido en todas las rutas de picking.

### Paths
- `GET /api/merchant/orders/{order}/picking`
- `POST /api/merchant/orders/{order}/picking/scan`
- `POST /api/merchant/orders/{order}/picking/manual`
- `POST /api/merchant/orders/{order}/picking/fallback`
- `POST /api/merchant/orders/{order}/picking/complete`
- `POST /api/merchant/orders/{order}/picking/reset`

## 13) Checklist de implementacion
1. Crear migraciones para `fulfillment_status`, campos de picking, product codes, events y sessions.
2. Crear modelos Eloquent y relaciones.
3. Crear controller API y rutas.
4. Agregar chequeos de autorizacion por tienda.
5. Agregar tests de scan ok/error, fallback al intento 3, acciones manuales, complete/reset.
6. Agregar servicio frontend + pagina picking + modal fallback.


