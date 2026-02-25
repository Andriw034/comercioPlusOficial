<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# INTEGRADO_PICKING

Fecha de consolidacion: 2026-02-25
Base: contrato API + state machine + runbook + handoff visual + plan por fases.

## 1) Alcance funcional consolidado
Modulo de alistamiento de pedidos con:
1. Scanner (primario)
2. Manual (fallback/operacion)
3. Fallback obligatorio tras 3 fallos consecutivos
4. Auditoria de eventos y control por sesion

## 2) Endpoints operativos
- `GET /api/merchant/orders/{order}/picking`
- `POST /api/merchant/orders/{order}/picking/scan`
- `POST /api/merchant/orders/{order}/picking/manual`
- `POST /api/merchant/orders/{order}/picking/fallback`
- `POST /api/merchant/orders/{order}/picking/complete`
- `POST /api/merchant/orders/{order}/picking/reset`

## 3) Reglas de dominio no negociables
1. Separar `orders.status` (pago/comercial) de `orders.fulfillment_status` (operacion).
2. No descontar inventario dos veces por la misma orden.
3. Todas las escrituras de picking en transaccion.
4. Al tercer fallo consecutivo de escaneo: `FALLBACK_REQUIRED`.

## 4) Estado y transiciones
- Inicio: `pending_pick`
- Flujo: `pending_pick -> picking -> picked -> packed -> ready -> delivered`
- Cancelacion: `* -> cancelled`

## 5) Modelo de datos asociado
- `orders.fulfillment_status`
- `order_products.qty_picked`, `qty_packed`, `qty_missing`
- `product_codes`
- `order_picking_events`
- `order_picking_sessions`

## 6) Errores operativos estandar
- `CODE_NOT_FOUND`
- `CODE_NOT_IN_ORDER`
- `ITEM_ALREADY_COMPLETE`
- `QTY_EXCEEDED`
- `INVALID_QTY`
- `SCAN_INVALID_STATE`
- `FALLBACK_REQUIRED`
- `PICKING_INCOMPLETE`

## 7) Runbook minimo de soporte
1. Verificar autorizacion de tienda/orden.
2. Validar codigo en `product_codes`.
3. Confirmar si el item ya esta completo.
4. Revisar eventos de picking y sesion de fallos.
5. Si aplica, activar flujo manual y completar orden.

## 8) Alcance de diseno (Claude)
Permitido:
- UX visual y layout de vistas de picking.

No permitido:
- Cambiar endpoints, payloads, estados o reglas de negocio.

## 9) Fuentes consolidadas
- `docs/picking-api-contract.md`
- `docs/picking-state-machine.md`
- `docs/picking-runbook.md`
- `docs/picking-phase-plan-prompt.md`
- `docs/picking-claude-design-handoff.md`

