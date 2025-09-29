# TODO: Corregir Navegación del Panel de Comerciante

## Información Recopilada
- El panel usa layouts/admin.blade.php con sidebar
- Muchas opciones del sidebar apuntan a "#" en lugar de rutas válidas
- Las rutas existentes funcionan (productos, categorías, dashboard)
- Faltan rutas y vistas para: inventario, apariencia, pagos, envíos, dominio, seguridad, notificaciones

## Plan de Corrección

### 1. Rutas Faltantes (routes/web.php)
- [ ] /admin/inventory - Inventario
- [ ] /admin/store/appearance - Apariencia de la tienda
- [ ] /admin/store/payments - Métodos de pago
- [ ] /admin/store/shipping - Configuración de envíos
- [ ] /admin/store/domain - Dominio/URL pública
- [ ] /admin/profile/security - Seguridad del perfil
- [ ] /admin/profile/notifications - Notificaciones

### 2. Controladores Nuevos
- [ ] StoreSettingsController - Para configuración de tienda
- [ ] ProfileSettingsController - Para configuración de perfil

### 3. Vistas Nuevas (resources/views/admin/)
- [ ] inventory/index.blade.php - Gestión de inventario
- [ ] store/appearance.blade.php - Apariencia de la tienda
- [ ] store/payments.blade.php - Métodos de pago
- [ ] store/shipping.blade.php - Configuración de envíos
- [ ] store/domain.blade.php - Dominio/URL pública
- [ ] profile/security.blade.php - Seguridad del perfil
- [ ] profile/notifications.blade.php - Configuración de notificaciones

### 4. Actualizar Sidebar (layouts/admin.blade.php)
- [ ] Cambiar enlaces de "#" a rutas válidas
- [ ] Agregar clases activas para navegación actual

### 5. Funcionalidades Adicionales
- [ ] Crear vistas para gestión (órdenes, clientes, etc.)
- [ ] Implementar lógica básica en controladores
- [ ] Agregar validaciones y permisos

## Próximos Pasos
1. Crear rutas faltantes en routes/web.php
2. Crear controladores básicos
3. Crear vistas básicas con layouts/admin
4. Actualizar sidebar con rutas correctas
5. Probar navegación entre secciones
