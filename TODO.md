# TODO: Implementar Dashboard Layout y Componentes

## Plan de Implementación

### 1. Reemplazar Layout Dashboard
- [ ] Reemplazar `resources/views/layouts/dashboard.blade.php` con el código proporcionado

### 2. Reemplazar Controlador Dashboard
- [ ] Reemplazar `app/Http/Controllers/DashboardController.php` con el código completo del controlador

### 3. Reemplazar Vista de Productos
- [ ] Reemplazar `resources/views/dashboard/products/index.blade.php` con la vista de grid basado en tarjetas

### 4. Agregar Rutas Dashboard
- [ ] Agregar las rutas del dashboard a `routes/web.php` dentro del grupo auth middleware

### 5. Actualizar CSS
- [ ] Agregar la utilidad `.line-clamp-2` a `resources/css/app.css`

### 6. Ejecutar Comandos
- [ ] Limpiar cachés: `php artisan config:clear`, `php artisan cache:clear`, `php artisan view:clear`
- [ ] Crear enlace storage: `php artisan storage:link`
- [ ] Compilar assets: `npm run build`
- [ ] Verificar servidor: `php artisan serve`

## Pruebas Exhaustivas

### Funcionalidad del Dashboard
- [ ] Verificar que el dashboard renderiza correctamente después del login
- [ ] Verificar navegación del sidebar (Dashboard, Productos, Categorías, Pedidos)
- [ ] Verificar que muestra estadísticas correctas (productos, activos, categorías)
- [ ] Verificar que muestra productos recientes

### CRUD de Productos
- [ ] Crear producto: verificar formulario, validación, subida de imagen
- [ ] Listar productos: verificar grid de tarjetas, paginación
- [ ] Editar producto: verificar carga de datos, actualización
- [ ] Eliminar producto: verificar confirmación y eliminación
- [ ] Verificar permisos (solo productos de la tienda del usuario)

### Diseño y UX
- [ ] Verificar paleta de colores (gris oscuro, naranja)
- [ ] Verificar responsividad del layout
- [ ] Verificar transiciones y efectos hover
- [ ] Verificar mensajes de éxito/error

### Flujo de Autenticación
- [ ] Usuario registrado con tienda: login → dashboard de productos
- [ ] Usuario registrado sin tienda: login → crear tienda
- [ ] Verificar redirecciones correctas

### Vistas Adicionales
- [ ] Verificar que vistas de crear/editar producto existen y funcionan
- [ ] Verificar integración con layout dashboard
