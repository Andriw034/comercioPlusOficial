# Ejemplos de código para probar todas las rutas API con scopes incluidos, filtros, orden y paginación

Este documento contiene ejemplos para el método `index` de cada controlador API, mostrando cómo aplicar los scopes `included()`, `filter()`, `sort()` y paginación para facilitar las pruebas en Postman.

---

## CartController

```php
public function index()
{
    $cart = Cart::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'message' => 'Carritos obtenidos correctamente',
        'data' => $cart,
    ]);
}
```

---

## CartProductController

```php
public function index()
{
    $cartProduct = CartProduct::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'message' => 'Productos de carrito obtenidos correctamente',
        'data' => $cartProduct,
    ]);
}
```

---

## CategoryController

```php
public function index()
{
    $category = Category::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'message' => 'Categorías obtenidas correctamente',
        'data' => $category,
    ]);
}
```

---

## ChannelController

```php
public function index()
{
    $channel = Channel::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'message' => 'Canales obtenidos correctamente',
        'data' => $channel,
    ]);
}
```

---

## ClaimController

```php
public function index()
{
    $claim = Claim::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

    return response()->json([
        'status' => 'ok',
        'message' => 'Reclamos obtenidos correctamente',
        'data' => $claim,
    ]);
}
```

---

## Ejemplos de consultas en Postman para todos los endpoints

- Para incluir relaciones:  
  `GET /api/{resource}?include=relacion1,relacion2`

- Para filtrar:  
  `GET /api/{resource}?filter[campo]=valor`

- Para ordenar:  
  `GET /api/{resource}?sort=campo` (ascendente)  
  `GET /api/{resource}?sort=-campo` (descendente)

- Para paginar:  
  `GET /api/{resource}?page=1&per_page=10`

Reemplaza `{resource}` por el nombre del recurso, por ejemplo: `carts`, `categories`, `claims`, etc.

---

Si deseas, puedo continuar generando estos métodos para todos los controladores restantes o ayudarte con otros métodos (store, show, update, destroy).

---

# Ejemplos de solicitudes Postman para todos los métodos CRUD

## CartController

### Crear (store)
```
POST /api/carts
Content-Type: application/json

{
  "user_id": 1
}
```

### Mostrar (show)
```
GET /api/carts/{id}
```

### Actualizar (update)
```
PUT /api/carts/{id}
Content-Type: application/json

{
  "user_id": 2
}
```

### Eliminar (destroy)
```
DELETE /api/carts/{id}
```

---

## CartProductController

### Crear (store)
```
POST /api/cart-products
Content-Type: application/json

{
  "cart_id": 1,
  "product_id": 5
}
```

### Mostrar (show)
```
GET /api/cart-products/{id}
```

### Actualizar (update)
```
PUT /api/cart-products/{id}
Content-Type: application/json

{
  "cart_id": 2,
  "product_id": 6
}
```

### Eliminar (destroy)
```
DELETE /api/cart-products/{id}
```

---

## ChannelController

### Crear (store)
```
POST /api/channels
Content-Type: application/json

{
  "type": "social",
  "link": "https://facebook.com/example"
}
```

### Mostrar (show)
```
GET /api/channels/{id}
```

### Actualizar (update)
```
PUT /api/channels/{id}
Content-Type: application/json

{
  "type": "social",
  "link": "https://twitter.com/example"
}
```

### Eliminar (destroy)
```
DELETE /api/channels/{id}
```

---

## ClaimController

### Crear (store)
```
POST /api/claims
Content-Type: application/json

{
  "message": "Problema con el producto",
  "date": "2024-06-01",
  "contact_method": "email"
}
```

### Mostrar (show)
```
GET /api/claims/{id}
```

### Actualizar (update)
```
PUT /api/claims/{id}
Content-Type: application/json

{
  "message": "Actualización del problema",
  "date": "2024-06-02",
  "contact_method": "phone"
}
```

### Eliminar (destroy)
```
DELETE /api/claims/{id}
```

---

## LocationController

### Crear (store)
```
POST /api/locations
Content-Type: application/json

{
  "address": "Calle Falsa 123",
  "city": "Madrid",
  "state": "Madrid",
  "postal_code": "28013",
  "country": "España",
  "latitude": 40.4168,
  "longitude": -3.7038
}
```

### Mostrar (show)
```
GET /api/locations/{id}
```

### Actualizar (update)
```
PUT /api/locations/{id}
Content-Type: application/json

{
  "address": "Calle Verdadera 456",
  "city": "Barcelona",
  "state": "Barcelona",
  "postal_code": "08001",
  "country": "España",
  "latitude": 41.3851,
  "longitude": 2.1734
}
```

### Eliminar (destroy)
```
DELETE /api/locations/{id}
```

---

## NotificacionController

### Crear (store)
```
POST /api/notificaciones
Content-Type: application/json

{
  "title": "Nueva notificación",
  "message": "Tienes un nuevo mensaje",
  "is_read": false
}
```

### Mostrar (show)
```
GET /api/notificaciones/{id}
```

### Actualizar (update)
```
PUT /api/notificaciones/{id}
Content-Type: application/json

{
  "title": "Notificación actualizada",
  "message": "Mensaje actualizado",
  "is_read": true
}
```

### Eliminar (destroy)
```
DELETE /api/notificaciones/{id}
```

---

## OrderController

### Crear (store)
```
POST /api/orders
Content-Type: application/json

{
  "user_id": 1,
  "date": "2024-06-01",
  "payment_method": "credit_card"
}
```

### Mostrar (show)
```
GET /api/orders/{id}
```

### Actualizar (update)
```
PUT /api/orders/{id}
Content-Type: application/json

{
  "user_id": 2,
  "date": "2024-06-02",
  "payment_method": "paypal"
}
```

### Eliminar (destroy)
```
DELETE /api/orders/{id}
```

---

## TutorialController

### Crear (store)
```
POST /api/tutorials
Content-Type: application/json

{
  "language": "es",
  "content": "Contenido del tutorial"
}
```

### Mostrar (show)
```
GET /api/tutorials/{id}
```

### Actualizar (update)
```
PUT /api/tutorials/{id}
Content-Type: application/json

{
  "language": "en",
  "content": "Updated tutorial content"
}
```

### Eliminar (destroy)
```
DELETE /api/tutorials/{id}
```
