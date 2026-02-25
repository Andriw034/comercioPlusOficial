<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# TODO: TraducciÃ³n Completa de la AplicaciÃ³n a EspaÃ±ol

## InformaciÃ³n Recopilada
- La aplicaciÃ³n es un e-commerce Laravel con frontend Vue.js
- Usa Inertia.js para la comunicaciÃ³n entre backend y frontend
- Tiene vistas Blade y componentes Vue
- Incluye controladores, modelos, seeders, validaciones, etc.

## Plan de TraducciÃ³n

### 1. Vistas Blade (resources/views/)
- [ ] welcome.blade.php - Texto del hero, categorÃ­as, etc.
- [ ] auth/login.blade.php - Formulario de login
- [ ] auth/register.blade.php - Formulario de registro
- [ ] auth/forgot-password.blade.php - Recuperar contraseÃ±a
- [ ] auth/reset-password.blade.php - Resetear contraseÃ±a
- [ ] store/create.blade.php - Crear tienda
- [ ] dashboard/products.blade.php - Dashboard de productos
- [ ] admin/products/index.blade.php - Lista de productos admin
- [ ] products/index.blade.php - Lista de productos pÃºblica
- [ ] layouts/app.blade.php - Layout principal
- [ ] layouts/admin.blade.php - Layout admin
- [ ] layouts/guest.blade.php - Layout invitado
- [ ] layouts/marketing.blade.php - Layout marketing

### 2. Componentes Vue (resources/js/Pages/ y resources/js/components/)
- [ ] Welcome.vue - PÃ¡gina de bienvenida
- [ ] Login.vue - Login
- [ ] Register.vue - Registro
- [ ] Home.vue - PÃ¡gina principal
- [ ] Products/Index.vue - Lista de productos
- [ ] Products/Show.vue - Detalle de producto
- [ ] Cart/Index.vue - Carrito
- [ ] Checkout/Index.vue - Checkout
- [ ] Stores/Show.vue - Mostrar tienda
- [ ] Navbar.vue - Barra de navegaciÃ³n
- [ ] Footer.vue - Pie de pÃ¡gina

### 3. Controladores (app/Http/Controllers/)
- [ ] Mensajes de Ã©xito/error en controladores web
- [ ] Mensajes de validaciÃ³n
- [ ] Mensajes flash

### 4. Modelos y Validaciones
- [ ] Mensajes de validaciÃ³n en Request classes
- [ ] Atributos fillable y otros textos

### 5. Seeders y Factories
- [ ] database/seeders/ - Datos de prueba
- [ ] database/factories/ - Factories

### 6. ConfiguraciÃ³n y Archivos de Sistema
- [ ] config/app.php - Nombres de aplicaciÃ³n
- [ ] Archivos de idioma (resources/lang/)

### 7. DocumentaciÃ³n
- [ ] README.md
- [ ] PLAN_*.md
- [ ] DocumentaciÃ³n en informes/

### 8. Tests
- [ ] tests/ - Mensajes en tests

## Estrategia de TraducciÃ³n
1. Mantener consistencia en terminologÃ­a tÃ©cnica
2. Usar espaÃ±ol neutro y profesional
3. Preservar placeholders y variables
4. Traducir solo texto visible al usuario
5. Mantener slugs y rutas en inglÃ©s si es necesario

## PrÃ³ximos Pasos
- Comenzar con las vistas principales (welcome, login, register)
- Continuar con componentes Vue
- Traducir controladores y validaciones
- Finalizar con seeders y documentaciÃ³n

