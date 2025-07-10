# Reporte de Auditoría - ComercioPlus Frontend

## Mapa de Rutas y Archivos Clave

- `package.json`: Dependencias y scripts de desarrollo.
- `vite.config.js`: Configuración de Vite con proxy a backend Laravel.
- `tailwind.config.js`: Configuración Tailwind v4 con tema JBL (naranja, pills, sombras suaves).
- `postcss.config.js`: Configuración PostCSS con plugin @tailwindcss/postcss.
- `src/`
  - `app.js`: Punto de entrada JS.
  - `lib/`: Librerías y servicios API.
  - `views/Welcome.vue`: Página principal Vue 3 con Tailwind.
  - `components/`: Componentes Vue reutilizables.
- `tests/welcome.spec.js`: Pruebas Playwright para la página de bienvenida.

## Dependencias

- Vue 3, Vue Router, Pinia
- TailwindCSS v4 con plugins forms y typography
- Vite para bundling y desarrollo
- Playwright para pruebas E2E
- Vitest para pruebas unitarias

## Pruebas Implementadas

- Pruebas E2E con Playwright para la página de bienvenida:
  - Verificación de contenido estático y dinámico.
  - Navegación entre páginas.
  - Diseño responsivo en diferentes resoluciones.

## Ajustes Mínimos Realizados

- Corrección de sintaxis mínima en componentes Blade y Vue.
- Adaptación de componentes Blade para uso clásico sin Inertia.
- Configuración correcta de proxy API en Vite.
- Inclusión de Tailwind v4 con configuración JBL.

## Pasos para Ejecutar

### Backend (Laravel)

1. Configurar `.env` con base de datos y otros parámetros.
2. Ejecutar migraciones y seeders:
   ```
   php artisan migrate --seed
   ```
3. Iniciar servidor Laravel:
   ```
   php artisan serve
   ```

### Frontend (Vue 3 + Vite)

1. Instalar dependencias:
   ```
   npm install
   ```
2. Iniciar servidor de desarrollo:
   ```
   npm run dev
   ```
3. Abrir navegador en `http://localhost:5173`

### Pruebas

- Ejecutar pruebas unitarias:
  ```
  npm run test
  ```
- Ejecutar pruebas E2E Playwright:
  ```
  npm run test:e2e
  ```

---

# Reporte de Auditoría - ComercioPlus Backend

## Mapa de Rutas y Archivos Clave

- `app/Http/Controllers/`: Controladores API y web.
- `app/Models/`: Modelos Eloquent.
- `database/migrations/`: Migraciones de base de datos.
- `database/seeders/`: Seeders para datos iniciales.
- `resources/views/`: Vistas Blade clásicas.
- `routes/web.php` y `routes/api.php`: Rutas web y API.
- `tests/Feature/` y `tests/Unit/`: Pruebas backend.

## Dependencias

- Laravel 10
- MySQL
- Sanctum para autenticación API
- PHPUnit para pruebas unitarias y funcionales

## Pruebas Implementadas

- Pruebas funcionales para API de carrito, productos, usuarios, tiendas, suscripciones, etc.
- Pruebas unitarias para modelos y servicios.

## Ajustes Mínimos Realizados

- Corrección de sintaxis mínima en controladores y vistas.
- Adaptación de vistas para uso clásico Blade sin Inertia.
- Configuración de rutas y controladores para API y web.

## Pasos para Ejecutar

1. Configurar `.env` con base de datos y otros parámetros.
2. Ejecutar migraciones y seeders:
   ```
   php artisan migrate --seed
   ```
3. Iniciar servidor Laravel:
   ```
   php artisan serve
   ```
4. Ejecutar pruebas:
   ```
   php artisan test
   ```

---

Este reporte cubre la auditoría completa de ambos proyectos frontend y backend, con detalles de estructura, dependencias, pruebas y pasos para ejecución.
