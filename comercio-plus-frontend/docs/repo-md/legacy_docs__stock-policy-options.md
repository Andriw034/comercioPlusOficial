<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Politica de stock - Opciones y recomendacion

Fecha: 2026-02-23

## Estado actual (implementado)
- El stock se descuenta cuando el pedido cambia a estado `paid` / `approved` / `completed`.
- El picking (`/merchant/orders/{order}/picking/*`) no descuenta stock por si mismo.
- La logica actual vive en:
  - `app/Observers/OrderObserver.php`
  - `app/Services/InventoryService.php`

## Opcion A - Descontar en `paid` (actual)
### Ventajas
- Alineado a confirmacion de venta/cobro.
- Menor riesgo de descontar en pedidos no cobrados.
- Ya probado y funcionando hoy.

### Riesgos
- Operacion de bodega puede ir atras del estado de pago.
- Si hay faltantes fisicos al alistar, requiere gestion posterior de ajustes/cancelaciones.

## Opcion B - Descontar al `complete picking`
### Ventajas
- Alineado a salida fisica real de bodega.
- Mejor para operacion logistica.

### Riesgos
- Si ya se cobro y no se completa picking a tiempo, inventario puede verse mayor al real comprometido.
- Requiere idempotencia fuerte para evitar doble descuento.

## Opcion C - Reservar al crear pedido, confirmar al pagar/entregar
### Ventajas
- Evita sobreventa.
- Separa stock disponible vs reservado.

### Riesgos
- Mayor complejidad (reservas, expiracion, rollback por cancelaciones).
- Requiere cambios de modelo y reporteria.

## Recomendacion pragmatica (corto plazo)
1. Mantener Opcion A en produccion inmediata (estable y ya testeada).
2. Fortalecer monitoreo de faltantes en picking y ajustes.
3. Planificar Opcion C como evolucion (no hotfix), si el volumen crece y aparece sobreventa.

## Guardrail tecnico recomendado
- Mantener idempotencia en movimientos (`reference_type/reference_id` o `request_id`).
- Logs/auditoria claros por pedido y producto.
- Tests de no doble descuento en transiciones repetidas de estado.

