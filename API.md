# API.md

## Fuentes de evidencia

Fecha de corte: 2026-02-06  
Proyecto: `c:\xampp\htdocs\comercioPlusOficial`

Comandos ejecutados (reproducibles):
1. `php artisan route:list --path=api --except-vendor --json > .tmp_api_routes.json`
2. `rg -n "API\.(get|post|put|patch|delete)\(" comercio-plus-frontend/src`
3. `Get-Content routes/api.php`
4. `Get-Content app/Http/Controllers/Api/StoreController.php`
5. `Get-Content app/Http/Controllers/Api/ProductController.php`
6. `rg -n "class .*Store.*Request|class .*Product.*Request|function rules\(" app/Http/Requests app/Http/Controllers/Api`
7. Smoke HTTP real en puerto `8040` con headers `Accept: application/json` y `X-Requested-With: XMLHttpRequest`.

Archivos fuente de verdad usados:
- `routes/api.php`
- `app/Http/Controllers/Api/StoreController.php`
- `app/Http/Controllers/Api/ProductController.php`
- `.tmp_api_routes.json`

## Tabla de endpoints consumidos por React

| Endpoint | Consumo React (según `rg`) | Método usado por frontend | Método en ruta (Laravel) | Middleware literal (`route:list --json`) | Status reales observados (puerto 8040) | Shape real observado |
|---|---|---|---|---|---|---|
| `/api/health` | No consumido por React (según `rg`) | N/A | `GET|HEAD` | `["api"]` | `200` | `{"status":"ok"}` |
| `/api/register` | Sí (`/register`) | `POST` | `POST` | `["api","Illuminate\\Routing\\Middleware\\ThrottleRequests:5,1"]` | `201` éxito, `422` validación | éxito: `{"user":{...},"token":"..."}` |
| `/api/login` | Sí (`/login`) | `POST` | `POST` | `["api","Illuminate\\Routing\\Middleware\\ThrottleRequests:10,1"]` | `200` éxito, `422` credenciales inválidas | éxito: `{"user":{...},"token":"..."}` |
| `/api/logout` | Sí (`/logout`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` | `{"message":"Sesión cerrada correctamente"}` |
| `/api/me` | No consumido por React (según `rg`) | N/A | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` con token válido | `{"id","name","email","role","has_store"}` |
| `/api/categories` | Sí (`/categories`) | `GET` | `GET|HEAD` | `["api"]` | `200` | array de categorías |
| `/api/categories/{id}` | Sí (template ``/categories/${id}``) | `GET` | `GET|HEAD` (ruta real `{category}`) | `["api"]` | `200` con id existente (`/api/categories/2`) | objeto categoría |
| `/api/products` | Sí (`/products`) | `GET` | `GET|HEAD` | `["api"]` | `200` | paginado Laravel (`current_page`, `data`, `links`, etc.) |
| `/api/products/{id}` (show) | Sí (template ``/products/${id}``) | `GET` | `GET|HEAD` (ruta real `{product}`) | `["api"]` | `200` con id existente, `404` con id inexistente | `{"status":"ok","data":{...}}` o error JSON |
| `/api/public-stores` | Sí (`/public-stores`) | `GET` | `GET|HEAD` | `["api"]` | `200` | array de tiendas visibles |
| `/api/public-stores/{id}` | Sí (template ``/public-stores/${id}``) | `GET` | `GET|HEAD` (ruta real `{store}`) | `["api"]` | `200` con id existente, `404` con id inexistente | objeto tienda o error JSON |
| `/api/hero-images` | Sí (`/hero-images`) | `GET` | `GET|HEAD` | `["api"]` | `200` | `{"images":[...]}` |
| `/api/my/store` | Sí (`/my/store`) | `GET` | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum","App\\Http\\Middleware\\EnsureRole:merchant"]` | `401` sin token, `403` token client, `404` merchant sin tienda, `200` merchant con tienda | objeto tienda o `{"message":"..."}` |
| `/api/merchant/orders` | Sí (`/merchant/orders`) | `GET` | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum","App\\Http\\Middleware\\EnsureRole:merchant"]` | `404` merchant sin tienda, `200` merchant con tienda | `{"status":"ok","message":"Lista de órdenes","data":[...]}` |
| `/api/stores` (create) | Sí (`/stores`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `422` payload inválido, `201` create | `422`: `errors.name`; `201`: objeto tienda |
| `/api/stores/{id}` (update) | Sí (template ``/stores/${store.id}``) | `POST` + `_method=PUT` | `PUT|PATCH` (ruta real `{store}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` update real | objeto tienda actualizado |
| `/api/products` (create) | Sí (`/products`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `422` payload inválido, `201` create | `{"status":"created","data":{...}}` |
| `/api/products/{id}` (update) | Sí (template ``/products/${form.id}``) | `POST` + `_method=PUT` | `PUT|PATCH` (ruta real `{product}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` update real | `{"status":"updated","data":{...}}` |
| `/api/products/{id}` (delete) | Sí (template ``/products/${item.id}``) | `DELETE` | `DELETE` (ruta real `{product}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` delete real (`/api/products/2`) | `{"status":"deleted","message":"Producto eliminado correctamente"}` |

## Campos requeridos y validaciones reales

Fuente: validación inline en `validate()` dentro de controladores API.

Evidencia:
- `app/Http/Controllers/Api/StoreController.php`
- `app/Http/Controllers/Api/ProductController.php`
- `rg -n "class .*Store.*Request|class .*Product.*Request|function rules\(" app/Http/Requests app/Http/Controllers/Api`

Conclusión:
- No existe FormRequest API dedicado para Store/Product usado por estos endpoints.
- La validación activa está inline en controller.

### `POST /api/stores` (`StoreController@store`)
Requeridos:
- `name`: `required|string|max:255`

Opcionales (explícitos):
- `slug`: `nullable|string|max:255|unique:stores,slug`
- `description`: `nullable|string`
- `phone`: `nullable|string|max:50`
- `whatsapp`: `nullable|string|max:50`
- `support_email`: `nullable|email|max:255`
- `facebook`: `nullable|string|max:255`
- `instagram`: `nullable|string|max:255`
- `address`: `nullable|string|max:500`
- `is_visible`: `nullable|boolean`
- `logo`: `nullable|image|max:2048`
- `cover`: `nullable|image|max:4096`

### `POST /api/products` (`ProductController@store`)
Requeridos:
- `name`: `required|string|max:255`
- `price`: `required|numeric|min:0`
- `stock`: `required|integer|min:0`
- `category_id`: `required|exists:categories,id`

Opcionales (explícitos):
- `slug`: `nullable|string|max:255|unique:products,slug`
- `description`: `nullable|string`
- `status`: `nullable|in:draft,active,0,1,true,false`
- `image`: `nullable|image|max:2048`

Regla funcional observada:
- `store_id` se resuelve desde la tienda del usuario autenticado (`Store::where('user_id', ...)->firstOrFail()`).

## Evidencia smoke (puerto 8040)

### Muestras (status + body recortado)
- `GET /api/health` -> `200` -> `{"status":"ok"}`
- `GET /api/categories` -> `200` -> array con categorías (se tomó `category_id=2`)
- `POST /api/login` inválido -> `422` -> `{"message":"Credenciales inválidas","errors":{"email":[...]}}`
- `POST /api/register` inválido -> `422` -> `{"message":"The email field must be a valid email address.","errors":{"email":[...]}}`
- `POST /api/login` admin -> `200` -> token enmascarado `14|ww1H1...06dd2c`
- `GET /api/me` admin -> `200` -> `{"id":1,...,"has_store":true}`
- `GET /api/my/store` admin before create (if applies) -> `200` (ya existía tienda)
- `POST /api/stores` `{}` -> `422`
- `POST /api/stores` `{name}` -> `201` -> `store_id=2`
- `GET /api/my/store` admin after create -> `200`
- `POST /api/products` `{}` -> `422`
- `POST /api/products` `{name,price,stock,category_id}` -> `201` -> `product_id=2`
- `POST /api/stores/2` con `_method=PUT` y campo de Descripción -> `200`
- `POST /api/products/2` con `_method=PUT` y `stock` -> `200`
- `GET /api/products/2` -> `200`
- `GET /api/my/store` sin token -> `401` -> `{"message":"Unauthenticated."}`
- `POST /api/register` client válido -> `201`
- `POST /api/login` client válido -> `200` -> token enmascarado `16|w3Ub8...ea0361`
- `GET /api/my/store` con token client -> `403` -> `{"message":"No autorizado"}`
- `GET /api/products/999999999` -> `404` (incluye `exception/trace` en local)
- `GET /api/public-stores/999999999` -> `404` (incluye `exception/trace` en local)
- `GET /api/merchant/orders` merchant con tienda -> `200`
- `GET /api/merchant/orders` merchant sin tienda -> `404` -> `{"message":"Tienda no encontrada"}`
- `DELETE /api/products/2` -> `200` -> `{"status":"deleted","message":"Producto eliminado correctamente"}`
- `POST /api/logout` admin -> `200` -> `{"message":"Sesión cerrada correctamente"}`

Decisión contractual observada:
- merchant sin tienda => `404` con mensaje `"Tienda no encontrada"`.

## Reglas canónicas de error 401/403/404/422

Con evidencia real observada:
- `401`: sin token en ruta protegida (`/api/my/store`) -> `{"message":"Unauthenticated."}`
- `403`: token válido con rol no autorizado (`/api/my/store` con client) -> `{"message":"No autorizado"}`
- `404`: recurso inexistente o merchant sin tienda -> `{"message":"..."}`
- `422`: validación fallida -> `{"message":"...","errors":{...}}`

## Requisitos de producción (APP_DEBUG=false)

Requisito obligatorio para staging/producción:
- `APP_DEBUG=false`

Motivo (evidencia real en local):
- Con `APP_DEBUG=true`, los `404` de `/api/products/999999999` y `/api/public-stores/999999999` exponen `exception` y `trace`.

## Pendiente de verificar

1. `POST + _method=PUT` en `multipart/form-data` con archivos:
   - `POST /api/stores/{id}` con `logo` y/o `cover`
   - `POST /api/products/{id}` con `image`
   - Cómo verificar: repetir smoke en puerto local enviando archivos reales.
2. `DELETE /api/products/{id}` con id inexistente dentro del mismo smoke del puerto `8040`.
   - Cómo verificar: `DELETE /api/products/999999999` con token merchant y headers JSON.