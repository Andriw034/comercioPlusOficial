# TODO: Traducción Completa de la Aplicación a Español

## Información Recopilada
- La aplicación es un e-commerce Laravel con frontend Vue.js
- Usa Inertia.js para la comunicación entre backend y frontend
- Tiene vistas Blade y componentes Vue
- Incluye controladores, modelos, seeders, validaciones, etc.

## Plan de Traducción

### 1. Vistas Blade (resources/views/)
- [ ] welcome.blade.php - Texto del hero, categorías, etc.
- [ ] auth/login.blade.php - Formulario de login
- [ ] auth/register.blade.php - Formulario de registro
- [ ] auth/forgot-password.blade.php - Recuperar contraseña
- [ ] auth/reset-password.blade.php - Resetear contraseña
- [ ] store/create.blade.php - Crear tienda
- [ ] dashboard/products.blade.php - Dashboard de productos
- [ ] admin/products/index.blade.php - Lista de productos admin
- [ ] products/index.blade.php - Lista de productos pública
- [ ] layouts/app.blade.php - Layout principal
- [ ] layouts/admin.blade.php - Layout admin
- [ ] layouts/guest.blade.php - Layout invitado
- [ ] layouts/marketing.blade.php - Layout marketing

### 2. Componentes Vue (resources/js/Pages/ y resources/js/components/)
- [ ] Welcome.vue - Página de bienvenida
- [ ] Login.vue - Login
- [ ] Register.vue - Registro
- [ ] Home.vue - Página principal
- [ ] Products/Index.vue - Lista de productos
- [ ] Products/Show.vue - Detalle de producto
- [ ] Cart/Index.vue - Carrito
- [ ] Checkout/Index.vue - Checkout
- [ ] Stores/Show.vue - Mostrar tienda
- [ ] Navbar.vue - Barra de navegación
- [ ] Footer.vue - Pie de página

### 3. Controladores (app/Http/Controllers/)
- [ ] Mensajes de éxito/error en controladores web
- [ ] Mensajes de validación
- [ ] Mensajes flash

### 4. Modelos y Validaciones
- [ ] Mensajes de validación en Request classes
- [ ] Atributos fillable y otros textos

### 5. Seeders y Factories
- [ ] database/seeders/ - Datos de prueba
- [ ] database/factories/ - Factories

### 6. Configuración y Archivos de Sistema
- [ ] config/app.php - Nombres de aplicación
- [ ] Archivos de idioma (resources/lang/)

### 7. Documentación
- [ ] README.md
- [ ] PLAN_*.md
- [ ] Documentación en informes/

### 8. Tests
- [ ] tests/ - Mensajes en tests

## Estrategia de Traducción
1. Mantener consistencia en terminología técnica
2. Usar español neutro y profesional
3. Preservar placeholders y variables
4. Traducir solo texto visible al usuario
5. Mantener slugs y rutas en inglés si es necesario

## Próximos Pasos
- Comenzar con las vistas principales (welcome, login, register)
- Continuar con componentes Vue
- Traducir controladores y validaciones
- Finalizar con seeders y documentación
