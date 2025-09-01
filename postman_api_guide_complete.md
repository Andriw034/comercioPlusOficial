# Guía Completa de API para Postman - ComercioRealPlus

## Configuración Base

### URL Base
```
http://localhost:8000/api/v1/
```

### Headers Comunes
```
Content-Type: application/json
Accept: application/json
```

---

## 🔐 Autenticación

### 1. Login (Obtener Token)
```
POST http://localhost:8000/api/v1/login
Headers:
  Content-Type: application/json

Body:
{
  "email": "admin@example.com",
  "password": "your_admin_password"
}
```

### 2. Registro
```
POST http://localhost:8000/api/v1/register
Headers:
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

### 3. Perfil de Usuario (Requiere Token)
```
GET http://localhost:8000/api/v1/me
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### 4. Logout
```
POST http://localhost:8000/api/v1/logout
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 👤 Usuarios (Requieren Token)

### Listar Usuarios
```
GET http://localhost:8000/api/v1/users
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Usuario
```
POST http://localhost:8000/api/v1/users
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "name": "Usuario Ejemplo",
  "email": "usuario@example.com",
  "password": "your_secure_password",
  "role_id": 2
}
```

### Ver Usuario Específico
```
GET http://localhost:8000/api/v1/users/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Actualizar Usuario
```
PUT http://localhost:8000/api/v1/users/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "name": "Nombre Actualizado"
}
```

### Eliminar Usuario
```
DELETE http://localhost:8000/api/v1/users/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 🏪 Tiendas (Requieren Token)

### Listar Tiendas
```
GET http://localhost:8000/api/v1/stores
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Tienda (con archivos)
```
POST http://localhost:8000/api/v1/stores
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Form Data:
- name: Mi Tienda
- slug: mi-tienda
- description: Descripción de la tienda
- logo: (seleccionar archivo)
- cover: (seleccionar archivo)
- primary_color: #FF6000
- text_color: #333333
- button_color: #FF6000
- background_color: #FFFFFF
- direccion: Dirección de la tienda
- telefono: 1234567890
- categoria_principal: Categoría Principal
```

### Ver Tienda Específica
```
GET http://localhost:8000/api/v1/stores/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Actualizar Tienda
```
PUT http://localhost:8000/api/v1/stores/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Form Data:
- name: Nombre Actualizado
- logo: (seleccionar archivo - opcional)
```

### Eliminar Tienda
```
DELETE http://localhost:8000/api/v1/stores/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 📦 Productos (Requieren Token)

### Listar Productos
```
GET http://localhost:8000/api/v1/products
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Producto
```
POST http://localhost:8000/api/v1/products
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

### Ver Producto Específico
```
GET http://localhost:8000/api/v1/products/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Actualizar Producto
```
PUT http://localhost:8000/api/v1/products/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "price": 120.50
}
```

### Eliminar Producto
```
DELETE http://localhost:8000/api/v1/products/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 📂 Categorías (Requieren Token)

### Listar Categorías
```
GET http://localhost:8000/api/v1/categories
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Categoría
```
POST http://localhost:8000/api/v1/categories
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "name": "Categoría Ejemplo",
  "store_id": 1
}
```

### Ver Categoría Específica
```
GET http://localhost:8000/api/v1/categories/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Actualizar Categoría
```
PUT http://localhost:8000/api/v1/categories/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "name": "Categoría Actualizada"
}
```

### Eliminar Categoría
```
DELETE http://localhost:8000/api/v1/categories/{id}
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 🌐 Endpoints Públicos (Sin Token)

### Listar Tiendas Públicas
```
GET http://localhost:8000/api/v1/public-stores
Headers:
  Content-Type: application/json
```

### Ver Tienda Pública por Slug
```
GET http://localhost:8000/api/v1/public-stores/{slug}
Headers:
  Content-Type: application/json
```

### Health Check
```
GET http://localhost:8000/api/ping
Headers:
  Content-Type: application/json
```

---

## 🔍 Parámetros de Consulta Avanzados

### Incluir Relaciones
```
?included=relacion1,relacion2,relacion3
```

### Filtrar por Campos
```
?filter[campo]=valor
```

### Ordenar Resultados
```
?sort=campo          # Ascendente
?sort=-campo         # Descendente
```

### Paginación
```
?page=1&per_page=10
```

---

## 📋 Ejemplos Prácticos con Parámetros

### 1. Usuarios con sus productos y roles
```
GET http://localhost:8000/api/v1/users?included=products,roles
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### 2. Productos con tienda y categoría, filtrados por precio
```
GET http://localhost:8000/api/v1/products?included=store,category&filter[price]=100&sort=-created_at&page=1&per_page=5
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### 3. Tiendas con productos, ordenadas por nombre
```
GET http://localhost:8000/api/v1/stores?included=products&sort=name
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### 4. Categorías con productos, paginadas
```
GET http://localhost:8000/api/v1/categories?included=products&page=2&per_page=10
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

---

## 🛒 Carritos y Órdenes (Requieren Token)

### Listar Carritos
```
GET http://localhost:8000/api/v1/carts
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Carrito
```
POST http://localhost:8000/api/v1/carts
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "user_id": 1
}
```

### Listar Órdenes
```
GET http://localhost:8000/api/v1/orders
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

### Crear Orden
```
POST http://localhost:8000/api/v1/orders
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "user_id": 1,
  "total": 250.75,
  "status": "pending"
}
```

---

## 🚨 Solución de Problemas Comunes

### Error 401 (No autorizado)
- Verificar que el token sea válido
- Asegurar que el header Authorization esté correcto

### Error 404 (No encontrado)
- Verificar que la ruta exista (todas las rutas API empiezan con `/api/v1/`)
- Confirmar que el ID del recurso exista

### Error 422 (Validación fallida)
- Revisar los datos enviados en el body
- Verificar campos requeridos y formatos

### Error 405 (Método no permitido)
- Verificar que el método HTTP sea correcto (GET, POST, PUT, DELETE)

### Error 500 (Error interno)
- Revisar logs de Laravel en `storage/logs/laravel.log`

---

## 💡 Consejos para Postman

1. **Variables de Entorno**: Crea variables para `base_url` y `token`
2. **Colecciones**: Organiza las requests en colecciones por recurso
3. **Tests**: Agrega tests automáticos para verificar respuestas
4. **Pre-request Scripts**: Configura headers automáticamente
5. **Documentación**: Exporta la colección con documentación

---

## 🔄 Flujo de Trabajo Recomendado

1. **Login** → Obtener token
2. **Guardar token** → En variable de entorno
3. **Realizar requests** → Con token en headers
4. **Logout** → Cuando termines

Esta guía te permitirá probar todos los endpoints API correctamente en Postman. Si encuentras algún error específico, comparte el mensaje de error para ayudarte a resolverlo.
