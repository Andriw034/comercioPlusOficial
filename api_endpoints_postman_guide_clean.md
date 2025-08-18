# 🚀 Guía Completa de Endpoints API - ComercioRealPlus

## 🌐 Configuración de Dominio Personalizado

### Opción 1: Configurar dominio local `comercioPlus`

#### Paso 1: Editar archivo hosts del sistema
**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac:** `/etc/hosts`

Agregar esta línea:
```
127.0.0.1    comercioPlus
```

#### Paso 2: Configurar Laravel
Editar el archivo `.env` y cambiar:
```
APP_URL=http://comercioPlus
```

#### Paso 3: Configurar servidor web
**Para Apache:** Crear un VirtualHost
**Para Nginx:** Configurar server block
**Para desarrollo:** Usar `php artisan serve --host=comercioPlus --port=80`

### Opción 2: Usar dominio en producción
Si tienes un dominio real, configura:
```
APP_URL=http://comercioplus.com
```

## Base URLs Disponibles:
- **Desarrollo local:** `http://localhost:8000/api`
- **Dominio personalizado:** `http://comercioPlus/api`
- **Producción:** `http://tudominio.com/api`

---

## 🎯 EJEMPLOS CON DOMINIO PERSONALIZADO

### Con dominio `comercioPlus`:

#### 1. **Obtener usuarios con productos:**
```
GET http://comercioPlus/api/users?included=products,sales,ratings
```

#### 2. **Obtener productos de un usuario específico:**
```
GET http://comercioPlus/api/products?filter[user_id]=1
```

#### 3. **Obtener productos con información del usuario:**
```
GET http://comercioPlus/api/products?included=user&filter[user_id]=1
```

#### 4. **Verificar que la API está activa:**
```
GET http://comercioPlus/api/ping
```

---

## 🔍 SOLUCIÓN AL PROBLEMA: users.products

### ❌ Endpoint Incorrecto (No existe):
```
GET /api/users.products
```

### ✅ Endpoints Correctos para obtener productos de usuarios:

#### 1. Obtener usuarios con sus productos incluidos:
```
GET http://comercioPlus/api/users?included=products
```

#### 2. Obtener productos de un usuario específico:
```
GET http://comercioPlus/api/products?filter[user_id]=1
```

#### 3. Obtener productos con información del usuario:
```
GET http://comercioPlus/api/products?included=user&filter[user_id]=1
```

---

## 📋 TODOS LOS ENDPOINTS DISPONIBLES

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

## 🚨 RESUMEN DE LA SOLUCIÓN

**Problema Original:** 
- El endpoint `/api/users.products` no existe y no despliega consultas en Postman

**Solución:**
- Usar `/api/users?included=products` para obtener usuarios con sus productos
- Usar `/api/products?filter[user_id]=ID` para obtener productos de un usuario específico

**Endpoints Correctos para Postman:**
1. `GET http://comercioPlus/api/users?included=products` - Usuarios con productos
2. `GET http://comercioPlus/api/products?filter[user_id]=1` - Productos de usuario específico
3. `GET http://comercioPlus/api/products?included=user,category` - Productos con relaciones

**Todos estos endpoints soportan:**
- ✅ Filtros con `?filter[campo]=valor`
- ✅ Inclusión de relaciones con `?included=relacion1,relacion2`
- ✅ Ordenamiento con `?sort=campo` o `?sort=-campo`
- ✅ Paginación con `?page=1&per_page=10`

---

## 📖 PASOS PARA CONFIGURAR EL DOMINIO

### 1. **Editar archivo hosts (Como Administrador):**
```
# Windows: C:\Windows\System32\drivers\etc\hosts
# Linux/Mac: /etc/hosts

127.0.0.1    comercioPlus
```

### 2. **Editar archivo .env de Laravel:**
```
APP_URL=http://comercioPlus
```

### 3. **Reiniciar servidor Laravel:**
```bash
php artisan serve --host=comercioPlus --port=80
```

### 4. **Probar en Postman:**
```
GET http://comercioPlus/api/users?included=products,sales,ratings
```

---

**¡Ahora puedes usar `http://comercioPlus/api` en lugar de `http://localhost:8000/api` en Postman!**
