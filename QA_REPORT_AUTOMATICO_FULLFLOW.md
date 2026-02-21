# QA Automatica ComercioPlus

- Fecha: 2026-02-20 19:19:08
- DB host: switchback.proxy.rlwy.net
- DB name: railway
- Total pruebas: 21
- Exitosas: 21
- Fallidas: 0

## Contexto generado

- Merchant: qa_merchant_20260220_191728@gmail.com
- Client: qa_client_20260220_191728@gmail.com
- Store ID: 3
- Category ID: 9
- Product ID: 3
- Order ID: 5
- Order2 ID: 6

## Resultado detallado

| Modulo | Paso | Estado | Duracion (ms) | Detalle |
|---|---|---|---:|---|
| Infra | Health endpoint | PASS | 115.29 | {"status":"ok"} |
| Merchant | Register merchant | PASS | 6595.64 | {"merchant_id":13} |
| Merchant | Me before store | PASS | 1278.79 | {"id":13,"name":"QA Merchant 191728","email":"qa_merchant_20260220_191728@gmail.com","phone":null,"role":"merchant","has_store":false,"store_id":null} |
| Merchant | My store returns 404 before create | PASS | 1255.31 | Tienda no encontrada |
| Merchant | Create store | PASS | 1613.55 | {"store_id":3} |
| Merchant | My store after create | PASS | 1321.96 | {"store_name":"QA Store 191737"} |
| Merchant | Tax settings GET | PASS | 1880.42 | {"id":3,"store_id":3,"enable_tax":false,"tax_name":"IVA","tax_rate":0.19,"tax_rate_percent":19,"prices_include_tax":false,"tax_rounding_mode":"HALF_UP"} |
| Merchant | Tax settings PUT | PASS | 1886 | {"example_input":100000,"base_sin_iva":84033.61,"iva":15966.39,"total":100000} |
| Merchant | Create category | PASS | 1876.6 | {"category_id":9} |
| Merchant | Create product | PASS | 2807.36 | {"product_id":3} |
| Merchant | Merchant endpoints after store | PASS | 8454.22 | {"orders_count":0,"customers_status":200,"inventory_status":200,"reports_status":200} |
| Client | Register client | PASS | 5251.99 | {"client_id":14} |
| Client | Client me + public catalog | PASS | 3452.32 | {"client_role":"client","catalog_items":1} |
| Client | Register customer visit | PASS | 2795.94 | {"status":"ok","message":"Visita registrada"} |
| Client | Create order via /api/orders | PASS | 6839.78 | {"order_id":5,"total":238000} |
| Client | Create order via /api/orders/create (checkout flow) | PASS | 5592.94 | {"order2_id":6} |
| Merchant | Merchant sees orders and marks first as paid | PASS | 13984.45 | {"order_paid":5} |
| Merchant | Inventory + invoices + reports after paid order | PASS | 17441.47 | {"movement_rows":1,"sale_rows_for_order":1,"gross_sales":238000,"tax_total":38000} |
| Client/Merchant | Order visibility and detail | PASS | 10582.36 | {"client_orders":2,"order_status":"paid"} |
| DB | Persistencia en tablas clave | PASS | 3091.45 | {"users_ok":true,"store_id":3,"product_id":3,"order_id":5,"order_lines":1,"inventory_sale_rows":1} |
| Auth | Logout merchant and client | PASS | 2487.05 | {"merchant":"Sesion cerrada correctamente","client":"Sesion cerrada correctamente"} |

## Estado final

PASS - Todos los flujos validados correctamente.