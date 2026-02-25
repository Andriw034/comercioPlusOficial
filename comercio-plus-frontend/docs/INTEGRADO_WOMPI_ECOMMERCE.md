<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# INTEGRADO_WOMPI_ECOMMERCE

Fecha de consolidacion: 2026-02-25
Base: README + GUIA_LARAVEL_WOMPI historicos.

## 1) Objetivo del bloque
Documentar el alcance de integracion de pagos Wompi en flujo ecommerce (carrito, checkout, orden, confirmacion).

## 2) Componentes historicamente asociados
Frontend:
- `CartContext`
- `Cart`
- `Checkout`
- `ProductCard`/`Navbar` (en variantes historicas)

Backend:
- migraciones/orders
- controlador Wompi
- rutas `/api/orders/create` y `/api/payments/wompi/*`
- configuracion en `config/services.php` y `.env`

## 3) Flujo funcional esperado
1. Cliente agrega productos al carrito.
2. Cliente inicia checkout.
3. API crea orden.
4. API crea transaccion Wompi.
5. Cliente completa pago.
6. Webhook/status actualiza estado de orden.

## 4) Nota de vigencia
Estas guias incluyen pasos historicos y plantillas iniciales.
Para implementacion actual del proyecto, cruzar siempre con:
1. rutas reales en `routes/api.php`
2. controladores activos en `app/Http/Controllers/Api`
3. frontend actual en `comercio-plus-frontend/src`

## 5) Fuentes consolidadas
- `README.md`
- `GUIA_LARAVEL_WOMPI.md`

