# Guía Completa de Endpoints API - ComercioRealPlus

Este documento contiene todos los endpoints disponibles para probar en Postman, incluyendo ejemplos con scopes, filtros, ordenamiento y paginación.

## 🚀 Endpoints Principales Disponibles

### Base URL: `http://localhost:8000/api`

---

## 📋 LISTA COMPLETA DE ENDPOINTS DISPONIBLES

### 1. **USUARIOS** - `/api/users`
```
GET    /api/users                    # Listar usuarios
POST   /api/users                    # Crear usuario
GET    /api/users/{id}               # Ver usuario específico
PUT    /api/users/{id}               # Actualizar usuario
DELETE /api/users/{id}               # Eliminar usuario
```

### 2. **PRODUCTOS** - `/api/products`
```
GET    /api/products                 # Listar productos
POST   /api/products                 # Crear producto
GET    /api/products/{id}            # Ver producto específico
PUT    /api/products/{id}            # Actualizar producto
DELETE /api/products/{id}            # Eliminar producto
```

### 3. **CARRITOS** - `/api/carts`
```
GET    /api/carts                    # Listar carritos
POST   /api/carts                    # Crear carrito
GET    /api/carts/{id}               # Ver carrito específico
PUT    /api/carts/{id}               # Actualizar carrito
DELETE /api/carts/{id}               # Eliminar carrito
```

### 4. **PRODUCTOS DEL CARRITO** - `/api/cart-products`
```
GET    /api/cart-products            # Listar productos del carrito
POST   /api/cart-products            # Agregar producto al carrito
GET    /api/cart-products/{id}       # Ver producto del carrito
PUT    /api/cart-products/{id}       # Actualizar producto del carrito
DELETE /api/cart-products/{id}       # Eliminar producto del carrito
```

### 5. **CATEGORÍAS** - `/api/categories`
```
GET    /api/categories               # Listar categorías
POST   /api/categories               # Crear categoría
GET    /api/categories/{id}          # Ver categoría específica
PUT    /api/categories/{id}          # Actualizar categoría
DELETE /api/categories/{id}          # Eliminar categoría
```

### 6. **ÓRDENES** - `/api/orders`
```
GET    /api/orders                   # Listar órdenes
POST   /api/orders                   # Crear orden
GET    /api/orders/{id}              # Ver orden específica
PUT    /api/orders/{id}              # Actualizar orden
DELETE /api/orders/{id}              # Eliminar orden
```

### 7. **PRODUCTOS DE ÓRDENES** - `/api/order-products`
```
GET    /api/order-products           # Listar productos de órdenes
POST   /api/order-products           # Crear producto de orden
GET    /api/order-products/{id}      # Ver producto de orden
PUT    /api/order-products/{id}      # Actualizar producto de orden
DELETE /api/order-products/{id}      # Eliminar producto de orden
```

### 8. **OTROS ENDPOINTS DISPONIBLES**
```
GET    /api/channels                 # Canales
GET    /api/claims                   # Reclamos
GET    /api/locations                # Ubicaciones
GET    /api/notifications            # Notificaciones
GET    /api/order-messages           # Mensajes de órdenes
GET    /api/profiles                 # Perfiles
GET    /api/pruebas                  # Pruebas
GET    /api/ratings                  # Calificaciones
GET    /api/roles                    # Roles
GET    /api/sales                    # Ventas
GET    /api/settings                 # Configuraciones
GET    /api/tutorials                # Tutoriales
```

---

## 🔍 SOLUCIÓN AL PROBLEMA: Obtener Productos de Usuarios

### ❌ Endpoint Incorrecto (No existe):
```
GET /api/users.products
```

### ✅ Endpoints Correctos para obtener productos de usuarios:

#### Opción 1: Obtener usuarios con sus productos incluidos
```
GET /api/users?included=products
```

#### Opción 2: Obtener usuarios con productos y filtros
```
GET /api/users?included=products&filter[name]=Juan&sort=-created_at
```

#### Opción 3: Obtener productos filtrados por usuario
```
GET /api/products?filter[user_id]=1&included=user
```

---

## 📖 EJEMPLOS ESPECÍFICOS PARA POSTMAN

### 🧑‍💼 USUARIOS CON PRODUCTOS

#### 1. Obtener todos los usuarios con sus productos:
```
GET http://localhost:8000/api/users?included=products
```

#### 2. Obtener usuarios con productos, filtrado por nombre:
```
GET http://localhost:8000/api/users?included=products&filter[name]=Juan
```

#### 3. Obtener usuarios con productos y múltiples relaciones:
```
GET http://localhost:8000/api/users?included=products,sales,ratings
```

#### 4. Obtener usuarios con productos, ordenados y paginados:
```
GET http://localhost:8000/api/users?included=products&sort=-created_at&page=1&per_page=5
```

### 🛍️ PRODUCTOS POR USUARIO

#### 1. Obtener productos de un usuario específico:
```
GET http://localhost:8000/api/products?filter[user_id]=1
```

#### 2. Obtener productos con información del usuario:
```
GET http://localhost:8000/api/products?included=user&filter[user_id]=1
```

#### 3. Obtener productos con usuario y categoría:
```
GET http://localhost:8000/api/products?included=user,category&filter[user_id]=1
```

---

## 🔧 PARÁMETROS DE CONSULTA DISPONIBLES

### Para USUARIOS (`/api/users`):
- **Relaciones disponibles**: `products`, `sales`, `ratings`, `category`, `locations`, `notifications`, `setting`, `roles`, `profile`
- **Filtros disponibles**: `name`, `email`, `phone`, `status`, `address`, `role_id`
- **Ordenamiento disponible**: `name`, `email`, `phone`, `status`, `address`, `role_id`

### Para PRODUCTOS (`/api/products`):
- **Relaciones disponibles**: `user`, `sales`, `ratings`, `category`, `orderproduct`, `cartproducts`
- **Filtros disponibles**: Según configuración del modelo
- **Ordenamiento disponible**: Según configuración del modelo

---

## 📝 SINTAXIS DE PARÁMETROS

### Incluir relaciones:
```
?included=relacion1,relacion2,relacion3
```

### Filtrar por campos:
```
?filter[campo1]=valor1&filter[campo2]=valor2
```

### Ordenar resultados:
```
?sort=campo          # Ascendente
?sort=-campo         # Descendente
?sort=campo1,-campo2 # Múltiple ordenamiento
```

### Paginar resultados:
```
?page=1&per_page=10
```

### Combinar todo:
```
?included=products,sales&filter[status]=1&sort=-created_at&page=1&per_page=5
```

---
=======

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
