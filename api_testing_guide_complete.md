# Guía Completa de Testing API - ComercioRealPlus

## Configuración Inicial

### 1. Configurar dominio local
Editar archivo hosts:
```
127.0.0.1    comercioPlus
```

### 2. Configurar .env
```
APP_URL=http://comercioPlus
```

### 3. Iniciar servidor
```bash
php artisan serve --host=comercioPlus --port=80
```

---

## Endpoints Disponibles

### 🔐 Autenticación (No requiere token)

- **Login:**
  ```
  POST http://comercioPlus/api/v1/login
  Content-Type: application/json
  Body:
  {
    "email": "admin@example.com",
    "password": "your_admin_password"
  }
  ```

- **Registro:**
  ```
  POST http://comercioPlus/api/v1/register
  Content-Type: application/json
  Body:
  {
    "name": "Nuevo Usuario",
    "email": "nuevo@example.com",
    "password": "your_secure_password",
    "password_confirmation": "your_secure_password",
    "role_id": 2
  }
  ```

### 🔒 Endpoints Protegidos (Requieren token)

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### 👤 Usuarios
```
GET    http://comercioPlus/api/v1/users
POST   http://comercioPlus/api/v1/users
GET    http://comercioPlus/api/v1/users/{id}
PUT    http://comercioPlus/api/v1/users/{id}
DELETE http://comercioPlus/api/v1/users/{id}
```

#### 🏪 Tiendas
```
GET    http://comercioPlus/api/v1/stores
POST   http://comercioPlus/api/v1/stores
GET    http://comercioPlus/api/v1/stores/{id}
PUT    http://comercioPlus/api/v1/stores/{id}
DELETE http://comercioPlus/api/v1/stores/{id}
```

#### 📦 Productos
```
GET    http://comercioPlus/api/v1/products
POST   http://comercioPlus/api/v1/products
GET    http://comercioPlus/api/v1/products/{id}
PUT    http://comercioPlus/api/v1/products/{id}
DELETE http://comercioPlus/api/v1/products/{id}
```

#### 📂 Categorías
```
GET    http://comercioPlus/api/v1/categories
POST   http://comercioPlus/api/v1/categories
GET    http://comercioPlus/api/v1/categories/{id}
PUT    http://comercioPlus/api/v1/categories/{id}
DELETE http://comercioPlus/api/v1/categories/{id}
```

#### 🛒 Carritos
```
GET    http://comercioPlus/api/v1/carts
POST   http://comercioPlus/api/v1/carts
GET    http://comercioPlus/api/v1/carts/{id}
PUT    http://comercioPlus/api/v1/carts/{id}
DELETE http://comercioPlus/api/v1/carts/{id}
```

#### 📋 Órdenes
```
GET    http://comercioPlus/api/v1/orders
POST   http://comercioPlus/api/v1/orders
GET    http://comercioPlus/api/v1/orders/{id}
PUT    http://comercioPlus/api/v1/orders/{id}
DELETE http://comercioPlus/api/v1/orders/{id}
```

---

## Ejemplos Prácticos para Postman

### 1. Login y obtener token
```
POST http://comercioPlus/api/v1/login
Body:
{
  "email": "admin@example.com",
  "password": "your_admin_password"
}
```

### 2. Crear tienda (con token)
```
POST http://comercioPlus/api/v1/stores
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Form Data:
- name: Mi Tienda
- slug: mi-tienda
- description: Descripción de mi tienda
- logo: (seleccionar archivo)
- cover: (seleccionar archivo)
- primary_color: #FF6000
- text_color: #333333
- button_color: #FF6000
- background_color: #FFFFFF
```

### 3. Listar productos
```
GET http://comercioPlus/api/v1/products
Headers:
  Authorization: Bearer {token}
```

### 4. Crear producto
```
POST http://comercioPlus/api/v1/products
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "name": "Producto Ejemplo",
  "price": 99.99,
  "description": "Descripción del producto",
  "store_id": 1,
  "category_id": 1
}
```

### 5. Ver perfil de usuario
```
GET http://comercioPlus/api/v1/me
Headers:
  Authorization: Bearer {token}
```

---

## Parámetros de Consulta

### Incluir relaciones
```
?included=products,user,category
```

### Filtrar
```
?filter[name]=tienda&filter[status]=active
```

### Ordenar
```
?sort=created_at
?sort=-price
```

### Paginación
```
?page=1&per_page=10
```

---

## Ejemplo Completo con Parámetros

```
GET http://comercioPlus/api/v1/products?included=store,category&filter[price]=100&sort=-created_at&page=1&per_page=5
Headers:
  Authorization: Bearer {token}
```

---

## Endpoints Públicos (Sin autenticación)

### Ver tiendas públicas
```
GET http://comercioPlus/api/v1/public-stores
```

### Ver tienda específica
```
GET http://comercioPlus/api/v1/public-stores/{slug}
```

### Health check
```
GET http://comercioPlus/api/ping
```

---

## Solución de Problemas Comunes

### 1. Error 401 (No autorizado)
- Verificar que el token sea válido
- Asegurar que el header Authorization esté correcto

### 2. Error 404 (No encontrado)
- Verificar que la ruta exista en routes/api.php
- Confirmar que el ID del recurso exista

### 3. Error 422 (Validación fallida)
- Revisar los datos enviados en el body
- Verificar campos requeridos y formatos

### 4. Error 500 (Error interno)
- Revisar logs de Laravel
- Verificar conexión a base de datos

---

## Comandos Útiles

```bash
# Limpiar caché
php artisan optimize:clear

# Ver rutas disponibles
php artisan route:list

# Ejecutar migraciones
php artisan migrate

# Crear enlace de storage
php artisan storage:link
```

Esta guía te permitirá probar todos los endpoints API correctamente. Si encuentras algún error específico, comparte el mensaje de error para ayudarte a resolverlo.
