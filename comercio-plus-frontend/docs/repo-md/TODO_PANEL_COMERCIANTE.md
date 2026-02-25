<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# TODO: Corregir NavegaciÃ³n del Panel de Comerciante

## InformaciÃ³n Recopilada
- El panel usa layouts/admin.blade.php con sidebar
- Muchas opciones del sidebar apuntan a "#" en lugar de rutas vÃ¡lidas
- Las rutas existentes funcionan (productos, categorÃ­as, dashboard)
- Faltan rutas y vistas para: inventario, apariencia, pagos, envÃ­os, dominio, seguridad, notificaciones

## Plan de CorrecciÃ³n

### 1. Rutas Faltantes (routes/web.php)
- [ ] /admin/inventory - Inventario
- [ ] /admin/store/appearance - Apariencia de la tienda
- [ ] /admin/store/payments - MÃ©todos de pago
- [ ] /admin/store/shipping - ConfiguraciÃ³n de envÃ­os
- [ ] /admin/store/domain - Dominio/URL pÃºblica
- [ ] /admin/profile/security - Seguridad del perfil
- [ ] /admin/profile/notifications - Notificaciones

### 2. Controladores Nuevos
- [ ] StoreSettingsController - Para configuraciÃ³n de tienda
- [ ] ProfileSettingsController - Para configuraciÃ³n de perfil

### 3. Vistas Nuevas (resources/views/admin/)
- [ ] inventory/index.blade.php - GestiÃ³n de inventario
- [ ] store/appearance.blade.php - Apariencia de la tienda
- [ ] store/payments.blade.php - MÃ©todos de pago
- [ ] store/shipping.blade.php - ConfiguraciÃ³n de envÃ­os
- [ ] store/domain.blade.php - Dominio/URL pÃºblica
- [ ] profile/security.blade.php - Seguridad del perfil
- [ ] profile/notifications.blade.php - ConfiguraciÃ³n de notificaciones

### 4. Actualizar Sidebar (layouts/admin.blade.php)
- [ ] Cambiar enlaces de "#" a rutas vÃ¡lidas
- [ ] Agregar clases activas para navegaciÃ³n actual

### 5. Funcionalidades Adicionales
- [ ] Crear vistas para gestiÃ³n (Ã³rdenes, clientes, etc.)
- [ ] Implementar lÃ³gica bÃ¡sica en controladores
- [ ] Agregar validaciones y permisos

## PrÃ³ximos Pasos
1. Crear rutas faltantes en routes/web.php
2. Crear controladores bÃ¡sicos
3. Crear vistas bÃ¡sicas con layouts/admin
4. Actualizar sidebar con rutas correctas
5. Probar navegaciÃ³n entre secciones

