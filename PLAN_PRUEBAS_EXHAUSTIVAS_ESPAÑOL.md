# Plan de Pruebas Exhaustivas Manuales (Laravel 11 + Inertia + Vue 3)

## Objetivo
Validar todas las vistas y funcionalidades de la aplicación en el servidor local, corrigiendo errores hasta que:
- Todas las rutas rendericen correctamente (Welcome, Login, Register, Dashboard, Stores, Products, Categories, Cart, Checkout, Orders, etc.).
- Los estilos y assets se carguen sin errores.
- No existan errores en consola del navegador.
- La navegación Inertia sea fluida (sin recargas completas).
- Se cumplan los flujos críticos de negocio end-to-end.

## Entorno de trabajo
- SO: Windows (PowerShell).
- Servidores:
  - `php artisan serve --host=127.0.0.1 --port=8000`
  - `npm run dev` (Vite)
- Variables en `.env`:
  - `APP_URL=http://127.0.0.1:8000`
  - `APP_DEBUG=true`
- Archivos clave:
  - `resources/views/app.blade.php` con `@vite('resources/js/app.js')`, `@inertiaHead`, `@inertia`.
  - `resources/js/app.js` montando Inertia.

## Preparación
- Limpiar cachés y estado de Vite:
  - `php artisan optimize:clear`
  - `if (Test-Path public\hot) { Remove-Item public\hot }`
- Levantar servidores:
  - `php artisan serve --host=127.0.0.1 --port=8000`
  - `npm run dev`
- Abrir `http://127.0.0.1:8000` y confirmar que Welcome carga.

## Rutas y vistas a probar (mínimo)
- `/` → Welcome
- `/login` → Login
- `/register` → Register
- `/dashboard` → Dashboard (prueba redirección y con usuario logueado)
- `/stores` → Stores/Index (paginación, filtros)
- `/products` → Listado (paginación/filtros/orden)
- `/categories` → Listado y detalle
- Carrito: añadir producto, listar, modificar cantidades, eliminar
- Checkout: iniciar checkout, confirmar orden
- Orders: listado, detalle, cambio de estado
- Perfil/Ajustes: edición básica
- Búsqueda y filtros en listados
- Rutas inexistentes (404)

## Checklist de verificación (UI/Render/Calidad)
- Sin errores 404 de assets (CSS/JS/imagenes).
- Sin errores en consola del navegador.
- Estilos aplicados (Tailwind/stylesheet) en todas las páginas.
- Navegación Inertia sin recargas completas (SPA feel).
- Paginación, filtros y orden funcionan.
- Formularios: validaciones, mensajes visibles.
- Estados de carga visibles (skeleton/spinner).
- Comportamiento en refresh (F5) desde rutas internas.
- Responsivo básico (desktop/tablet/móvil).
- Accesibilidad básica (focus, contraste, labels).

## Flujos críticos (end-to-end)
- Registro → Login → Dashboard
- Explorar tiendas y productos → agregar al carrito → checkout → ver pedido
- Filtrar y paginar productos/categorías/tiendas
- Editar perfil (si existe)
- Cerrar sesión y reingresar

## Corrección de errores (workflow)
- Registrar bug: título, pasos, resultado actual/esperado, evidencia (screenshot, logs).
- Corregir código (frontend/back).
- Verificar en servidor.
- Anotar fix: archivos, resumen, razón.
- No tocar migraciones salvo autorización.
- Seeds manuales si es necesario.

## Entregables
- Informe de pruebas (Markdown):
  - Resumen general.
  - Lista de issues y estado.
  - Evidencias (capturas, logs).
  - Cambios realizados.
  - Recomendaciones finales.
- Confirmación de:
  - Welcome, Login y Register renderizan OK.
  - Flujos críticos completados sin errores.
  - 0 errores en consola.
  - Estilos y assets cargan en todas las rutas.

---

Este plan servirá para guiar las pruebas exhaustivas y asegurar la calidad de la aplicación.
