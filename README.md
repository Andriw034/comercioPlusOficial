# Comercio Plus - Monorepo

Plataforma de e-commerce para tiendas de repuestos de motos, construida como un monorepo con Laravel (backend) y Next.js (frontend).

## Estructura del Monorepo

Este proyecto está organizado como un monorepo con las siguientes partes:

### Backend (Laravel)
- **Ubicación**: Raíz del proyecto (`/`)
- **Tecnologías**: Laravel 11, PHP 8.2+, MySQL, Blade, Tailwind CSS
- **Funcionalidades**:
  - API REST para productos, categorías, tiendas, usuarios
  - Panel de administración Blade con tema oscuro + naranja
  - Autenticación y autorización
  - Gestión de tiendas por usuario
  - Sistema de productos y categorías por tienda

### Frontend (Next.js)
- **Ubicación**: `/frontend`
- **Tecnologías**: Next.js 14, TypeScript, Tailwind CSS, shadcn/ui, Firebase
- **Funcionalidades**:
  - Interfaz moderna para clientes
  - Dashboard de administración alternativo
  - Integración con Firebase para auth y datos
  - Componentes UI reutilizables

## Cómo ejecutar

### Backend (Laravel)

1. Instalar dependencias PHP:
```bash
composer install
```

2. Configurar entorno:
```bash
cp .env.example .env
# Editar .env con tus configuraciones de BD, etc.
```

3. Generar key y migrar BD:
```bash
php artisan key:generate
php artisan migrate --seed
```

4. Ejecutar servidor:
```bash
php artisan serve
# O con PM2: pm2 start ecosystem.config.js
```

Accede a: `http://localhost:8000`

### Frontend (Next.js)

1. Ir al directorio frontend:
```bash
cd frontend
```

2. Instalar dependencias:
```bash
npm install
```

3. Configurar entorno:
```bash
cp .env.example .env.local
# Configurar variables de Firebase y API backend
```

4. Ejecutar servidor de desarrollo:
```bash
npm run dev
```

Accede a: `http://localhost:3000`

## Flujo de trabajo recomendado

1. **Crear Categoría**: En el panel admin de Laravel (`/admin/categories/create`), crea categorías para tu tienda.
2. **Crear Producto**: Luego crea productos (`/admin/products/create`) seleccionando la categoría creada.
3. **Ver en Frontend**: Los productos y categorías estarán disponibles en la app Next.js.

## Arquitectura

- **Controladores organizados por capas**:
  - `App\Http\Controllers\Web\` - Vistas Blade (dashboard)
  - `App\Http\Controllers\Api\` - APIs JSON
- **Modelos con relaciones**: Store, Product, Category, User, etc.
- **Validación de ownership**: Productos y categorías pertenecen a stores específicas
- **Middleware**: `has.store` para rutas admin

## Desarrollo

- Usa el backend Laravel para lógica de negocio y APIs
- El frontend Next.js consume las APIs del backend
- Ambos proyectos usan Tailwind CSS con tema oscuro
- Tests con Pest (backend) y Playwright (frontend E2E)

## Contribución

1. Elige la parte del proyecto a trabajar (backend/frontend)
2. Crea una rama descriptiva
3. Implementa cambios
4. Ejecuta tests locales
5. Crea PR con descripción detallada

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Acerca de Laravel

Laravel es un marco de aplicación web con sintaxis expresiva y elegante. Creemos que el desarrollo debe ser una experiencia agradable y creativa para ser verdaderamente gratificante. Laravel elimina el dolor del desarrollo al facilitar tareas comunes utilizadas en muchos proyectos web, como:

- [Motor de enrutamiento simple y rápido](https://laravel.com/docs/routing).
- [Contenedor de inyección de dependencias potente](https://laravel.com/docs/container).
- Múltiples back-ends para [sesión](https://laravel.com/docs/session) y almacenamiento en [caché](https://laravel.com/docs/cache).
- ORM de base de datos expresivo e intuitivo](https://laravel.com/docs/eloquent).
- Migraciones de esquema agnósticas de base de datos](https://laravel.com/docs/migrations).
- [Procesamiento robusto de trabajos en segundo plano](https://laravel.com/docs/queues).
- [Transmisión de eventos en tiempo real](https://laravel.com/docs/broadcasting).

Laravel es accesible, potente y proporciona las herramientas necesarias para aplicaciones grandes y robustas.

## Aprendiendo Laravel

Laravel tiene la [documentación](https://laravel.com/docs) más extensa y completa y la biblioteca de tutoriales en video de todos los marcos de aplicación web modernos, lo que facilita comenzar con el marco.

También puedes probar el [Laravel Bootcamp](https://bootcamp.laravel.com), donde serás guiado a través de la construcción de una aplicación Laravel moderna desde cero.

Si no te gusta leer, [Laracasts](https://laracasts.com) puede ayudar. Laracasts contiene más de 2000 tutoriales en video sobre una variedad de temas, incluyendo Laravel, PHP moderno, pruebas unitarias y JavaScript. Mejora tus habilidades profundizando en nuestra biblioteca de video completa.

## Patrocinadores de Laravel

Nos gustaría extender nuestros agradecimientos a los siguientes patrocinadores por financiar el desarrollo de Laravel. Si estás interesado en convertirte en patrocinador, visita la página de Laravel [Patreon](https://patreon.com/taylorotwell).

### Socios Premium

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contribuyendo

¡Gracias por considerar contribuir al marco de Laravel! La guía de contribución se puede encontrar en la [documentación de Laravel](https://laravel.com/docs/contributions).

## Código de Conducta

Para asegurar que la comunidad de Laravel sea acogedora para todos, por favor revisa y cumple con el [Código de Conducta](https://laravel.com/docs/contributions#code-of-conduct).

## Vulnerabilidades de Seguridad

Si descubres una vulnerabilidad de seguridad en Laravel, por favor envía un correo electrónico a Taylor Otwell a través de [taylor@laravel.com](mailto:taylor@laravel.com). Todas las vulnerabilidades de seguridad serán abordadas de inmediato.

## Licencia

El marco de Laravel es software de código abierto licenciado bajo la [licencia MIT](https://opensource.org/licenses/MIT).
