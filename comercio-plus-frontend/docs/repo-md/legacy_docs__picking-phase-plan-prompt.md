<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Plan por fases y prompt de ejecucion (Picking)

## Objetivo
Implementar picking de pedidos con escaner + manual, fallback obligatorio tras 3 fallos y frontend funcional sin diseno final, dejando una ultima fase para diseno visual con Claude.

## Estado real del proyecto (22-02-2026)
- `PR-00` completado: estabilizacion de dashboard de pedidos y contrato real de estados.
- `PR-01` completado: migraciones y modelos de dominio (`fulfillment_status`, `product_codes`, eventos y sesiones).
- `PR-02` completado: API de picking con rutas y reglas de dominio.
- `PR-03` completado: frontend funcional de picking sin depender de diseno final.
- `PR-04` pendiente recomendado: endurecimiento final (concurrencia, telemetria y pruebas extra de regresion).
- `PR-05` pendiente recomendado: diseno visual final con Claude sin tocar reglas de negocio.

## Fase 1 (PR-01) - Datos y dominio
Entregables:
- Migraciones:
  - `database/migrations/2026_02_22_000010_add_fulfillment_status_to_orders_table.php`
  - `database/migrations/2026_02_22_000011_add_picking_fields_to_order_products_table.php`
  - `database/migrations/2026_02_22_000012_create_product_codes_table.php`
  - `database/migrations/2026_02_22_000013_create_order_picking_events_table.php`
  - `database/migrations/2026_02_22_000014_create_order_picking_sessions_table.php`
- Modelos:
  - `app/Models/ProductCode.php`
  - `app/Models/OrderPickingEvent.php`
  - `app/Models/OrderPickingSession.php`
- Ajustes de relaciones:
  - `app/Models/Order.php`
  - `app/Models/OrderProduct.php`
  - `app/Models/Product.php`
  - `app/Models/Store.php`

## Fase 2 (PR-02) - API y reglas
Entregables:
- Controlador:
  - `app/Http/Controllers/Api/Merchant/OrderPickingController.php`
- Rutas:
  - `GET /api/merchant/orders/{order}/picking`
  - `POST /api/merchant/orders/{order}/picking/scan`
  - `POST /api/merchant/orders/{order}/picking/manual`
  - `POST /api/merchant/orders/{order}/picking/fallback`
  - `POST /api/merchant/orders/{order}/picking/complete`
  - `POST /api/merchant/orders/{order}/picking/reset`
- Reglas implementadas:
  - autorizacion estricta por tienda
  - transacciones en escritura
  - fallback obligatorio al tercer fallo de escaneo
  - reset de fallos tras accion exitosa scan/manual
  - separacion de `orders.status` y `orders.fulfillment_status`
  - no descuento duplicado de inventario desde picking

## Fase 3 (PR-03) - Frontend funcional sin diseno final
Entregables:
- Servicio API:
  - `comercio-plus-frontend/src/services/picking.ts`
- Vista funcional:
  - `comercio-plus-frontend/src/app/dashboard/orders/picking/page.tsx`
- Ruteo:
  - `comercio-plus-frontend/src/app/App.tsx`
- Punto de entrada desde pedidos:
  - `comercio-plus-frontend/src/app/dashboard/orders/page.tsx`

Comportamiento funcional:
- escaneo por codigo
- modo manual por linea y por codigo
- marcar faltantes
- notas manuales
- complete/reset
- deteccion y manejo de fallback requerido

## Fase 4 (PR-04) - Endurecimiento recomendado antes de produccion
Checklist:
- prueba de concurrencia de escaneo sobre la misma orden con 2 sesiones
- prueba de carga basica en endpoint `scan`
- monitoreo de eventos `scan_error` y `fallback_triggered` por tienda
- validacion de indices SQL en tablas de picking
- pruebas de regresion en `OrderStatusFlowTest` + inventario

## Fase 5 (PR-05) - Diseno visual con Claude (sin romper logica)
Handoff tecnico:
- usar `docs/picking-claude-design-handoff.md` como fuente principal.
- Claude solo debe tocar presentacion, layout y componentes visuales.
- Claude no debe cambiar contratos API ni nombres de payload.

## Prompt maestro para ejecucion incremental
Usa este prompt cuando quieras continuar por PRs sin alucinaciones:

```txt
Actua como desarrollador senior full stack en este repositorio.
Trabaja por PRs pequeÃ±os y verificables, sin romper contratos existentes.
Prioridad: exactitud sobre velocidad.

Objetivo:
Completar y endurecer la integracion de picking (escaner + manual + fallback al 3er fallo) para ComercioPlus.

Reglas:
1) No inventes tablas ni campos; usa los existentes en el proyecto.
2) Mantener separado orders.status (pago/comercial) de orders.fulfillment_status (alistamiento).
3) Toda escritura de picking en transacciones.
4) Mantener fallback obligatorio al 3er fallo de escaneo.
5) No aplicar decremento duplicado de inventario desde picking.
6) Antes de cerrar cada PR: correr pruebas relevantes y reportar resultados reales.

Plan de PRs:
- PR actual: endurecimiento final (concurrencia, errores y tests adicionales).
- PR final: preparar handoff visual para Claude, sin tocar logica de negocio.

Salida esperada por PR:
- archivos modificados
- razones de cambio
- pruebas ejecutadas y resultado
- riesgos residuales y siguiente PR sugerido
```


