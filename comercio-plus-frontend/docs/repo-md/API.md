<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

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

| Endpoint | Consumo React (segÃºn `rg`) | MÃ©todo usado por frontend | MÃ©todo en ruta (Laravel) | Middleware literal (`route:list --json`) | Status reales observados (puerto 8040) | Shape real observado |
|---|---|---|---|---|---|---|
| `/api/health` | No consumido por React (segÃºn `rg`) | N/A | `GET|HEAD` | `["api"]` | `200` | `{"status":"ok"}` |
| `/api/register` | SÃ­ (`/register`) | `POST` | `POST` | `["api","Illuminate\\Routing\\Middleware\\ThrottleRequests:5,1"]` | `201` Ã©xito, `422` validaciÃ³n | Ã©xito: `{"user":{...},"token":"..."}` |
| `/api/login` | SÃ­ (`/login`) | `POST` | `POST` | `["api","Illuminate\\Routing\\Middleware\\ThrottleRequests:10,1"]` | `200` Ã©xito, `422` credenciales invÃ¡lidas | Ã©xito: `{"user":{...},"token":"..."}` |
| `/api/logout` | SÃ­ (`/logout`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` | `{"message":"SesiÃ³n cerrada correctamente"}` |
| `/api/me` | No consumido por React (segÃºn `rg`) | N/A | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` con token vÃ¡lido | `{"id","name","email","role","has_store"}` |
| `/api/categories` | SÃ­ (`/categories`) | `GET` | `GET|HEAD` | `["api"]` | `200` | array de categorÃ­as |
| `/api/categories/{id}` | SÃ­ (template ``/categories/${id}``) | `GET` | `GET|HEAD` (ruta real `{category}`) | `["api"]` | `200` con id existente (`/api/categories/2`) | objeto categorÃ­a |
| `/api/products` | SÃ­ (`/products`) | `GET` | `GET|HEAD` | `["api"]` | `200` | paginado Laravel (`current_page`, `data`, `links`, etc.) |
| `/api/products/{id}` (show) | SÃ­ (template ``/products/${id}``) | `GET` | `GET|HEAD` (ruta real `{product}`) | `["api"]` | `200` con id existente, `404` con id inexistente | `{"status":"ok","data":{...}}` o error JSON |
| `/api/public-stores` | SÃ­ (`/public-stores`) | `GET` | `GET|HEAD` | `["api"]` | `200` | array de tiendas visibles |
| `/api/public-stores/{id}` | SÃ­ (template ``/public-stores/${id}``) | `GET` | `GET|HEAD` (ruta real `{store}`) | `["api"]` | `200` con id existente, `404` con id inexistente | objeto tienda o error JSON |
| `/api/hero-images` | SÃ­ (`/hero-images`) | `GET` | `GET|HEAD` | `["api"]` | `200` | `{"images":[...]}` |
| `/api/my/store` | SÃ­ (`/my/store`) | `GET` | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum","App\\Http\\Middleware\\EnsureRole:merchant"]` | `401` sin token, `403` token client, `404` merchant sin tienda, `200` merchant con tienda | objeto tienda o `{"message":"..."}` |
| `/api/merchant/orders` | SÃ­ (`/merchant/orders`) | `GET` | `GET|HEAD` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum","App\\Http\\Middleware\\EnsureRole:merchant"]` | `404` merchant sin tienda, `200` merchant con tienda | `{"status":"ok","message":"Lista de Ã³rdenes","data":[...]}` |
| `/api/stores` (create) | SÃ­ (`/stores`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `422` payload invÃ¡lido, `201` create | `422`: `errors.name`; `201`: objeto tienda |
| `/api/stores/{id}` (update) | SÃ­ (template ``/stores/${store.id}``) | `POST` + `_method=PUT` | `PUT|PATCH` (ruta real `{store}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` update real | objeto tienda actualizado |
| `/api/products` (create) | SÃ­ (`/products`) | `POST` | `POST` | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `422` payload invÃ¡lido, `201` create | `{"status":"created","data":{...}}` |
| `/api/products/{id}` (update) | SÃ­ (template ``/products/${form.id}``) | `POST` + `_method=PUT` | `PUT|PATCH` (ruta real `{product}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` update real | `{"status":"updated","data":{...}}` |
| `/api/products/{id}` (delete) | SÃ­ (template ``/products/${item.id}``) | `DELETE` | `DELETE` (ruta real `{product}`) | `["api","Illuminate\\Auth\\Middleware\\Authenticate:sanctum"]` | `200` delete real (`/api/products/2`) | `{"status":"deleted","message":"Producto eliminado correctamente"}` |

## Campos requeridos y validaciones reales

Fuente: validaciÃ³n inline en `validate()` dentro de controladores API.

Evidencia:
- `app/Http/Controllers/Api/StoreController.php`
- `app/Http/Controllers/Api/ProductController.php`
- `rg -n "class .*Store.*Request|class .*Product.*Request|function rules\(" app/Http/Requests app/Http/Controllers/Api`

ConclusiÃ³n:
- No existe FormRequest API dedicado para Store/Product usado por estos endpoints.
- La validaciÃ³n activa estÃ¡ inline en controller.

### `POST /api/stores` (`StoreController@store`)
Requeridos:
- `name`: `required|string|max:255`

Opcionales (explÃ­citos):
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

Opcionales (explÃ­citos):
- `slug`: `nullable|string|max:255|unique:products,slug`
- `description`: `nullable|string`
- `status`: `nullable|in:draft,active,0,1,true,false`
- `image`: `nullable|image|max:2048`

Regla funcional observada:
- `store_id` se resuelve desde la tienda del usuario autenticado (`Store::where('user_id', ...)->firstOrFail()`).

## Evidencia smoke (puerto 8040)

### Muestras (status + body recortado)
- `GET /api/health` -> `200` -> `{"status":"ok"}`
- `GET /api/categories` -> `200` -> array con categorÃ­as (se tomÃ³ `category_id=2`)
- `POST /api/login` invÃ¡lido -> `422` -> `{"message":"Credenciales invÃ¡lidas","errors":{"email":[...]}}`
- `POST /api/register` invÃ¡lido -> `422` -> `{"message":"The email field must be a valid email address.","errors":{"email":[...]}}`
- `POST /api/login` admin -> `200` -> token enmascarado `14|ww1H1...06dd2c`
- `GET /api/me` admin -> `200` -> `{"id":1,...,"has_store":true}`
- `GET /api/my/store` admin before create (if applies) -> `200` (ya existÃ­a tienda)
- `POST /api/stores` `{}` -> `422`
- `POST /api/stores` `{name}` -> `201` -> `store_id=2`
- `GET /api/my/store` admin after create -> `200`
- `POST /api/products` `{}` -> `422`
- `POST /api/products` `{name,price,stock,category_id}` -> `201` -> `product_id=2`
- `POST /api/stores/2` con `_method=PUT` y campo de DescripciÃ³n -> `200`
- `POST /api/products/2` con `_method=PUT` y `stock` -> `200`
- `GET /api/products/2` -> `200`
- `GET /api/my/store` sin token -> `401` -> `{"message":"Unauthenticated."}`
- `POST /api/register` client vÃ¡lido -> `201`
- `POST /api/login` client vÃ¡lido -> `200` -> token enmascarado `16|w3Ub8...ea0361`
- `GET /api/my/store` con token client -> `403` -> `{"message":"No autorizado"}`
- `GET /api/products/999999999` -> `404` (incluye `exception/trace` en local)
- `GET /api/public-stores/999999999` -> `404` (incluye `exception/trace` en local)
- `GET /api/merchant/orders` merchant con tienda -> `200`
- `GET /api/merchant/orders` merchant sin tienda -> `404` -> `{"message":"Tienda no encontrada"}`
- `DELETE /api/products/2` -> `200` -> `{"status":"deleted","message":"Producto eliminado correctamente"}`
- `POST /api/logout` admin -> `200` -> `{"message":"SesiÃ³n cerrada correctamente"}`

DecisiÃ³n contractual observada:
- merchant sin tienda => `404` con mensaje `"Tienda no encontrada"`.

## Reglas canÃ³nicas de error 401/403/404/422

Con evidencia real observada:
- `401`: sin token en ruta protegida (`/api/my/store`) -> `{"message":"Unauthenticated."}`
- `403`: token vÃ¡lido con rol no autorizado (`/api/my/store` con client) -> `{"message":"No autorizado"}`
- `404`: recurso inexistente o merchant sin tienda -> `{"message":"..."}`
- `422`: validaciÃ³n fallida -> `{"message":"...","errors":{...}}`

## Requisitos de producciÃ³n (APP_DEBUG=false)

Requisito obligatorio para staging/producciÃ³n:
- `APP_DEBUG=false`

Motivo (evidencia real en local):
- Con `APP_DEBUG=true`, los `404` de `/api/products/999999999` y `/api/public-stores/999999999` exponen `exception` y `trace`.

## Pendiente de verificar

1. `POST + _method=PUT` en `multipart/form-data` con archivos:
   - `POST /api/stores/{id}` con `logo` y/o `cover`
   - `POST /api/products/{id}` con `image`
   - CÃ³mo verificar: repetir smoke en puerto local enviando archivos reales.
2. `DELETE /api/products/{id}` con id inexistente dentro del mismo smoke del puerto `8040`.
   - CÃ³mo verificar: `DELETE /api/products/999999999` con token merchant y headers JSON.
