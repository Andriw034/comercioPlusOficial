<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Plan de Pruebas Exhaustivas Manuales (Laravel 11 + Inertia + Vue 3)

## Objetivo
Validar todas las vistas y funcionalidades de la aplicaciÃ³n en el servidor local, corrigiendo errores hasta que:
- Todas las rutas rendericen correctamente (Welcome, Login, Register, Dashboard, Stores, Products, Categories, Cart, Checkout, Orders, etc.).
- Los estilos y assets se carguen sin errores.
- No existan errores en consola del navegador.
- La navegaciÃ³n Inertia sea fluida (sin recargas completas).
- Se cumplan los flujos crÃ­ticos de negocio end-to-end.

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

## PreparaciÃ³n
- Limpiar cachÃ©s y estado de Vite:
  - `php artisan optimize:clear`
  - `if (Test-Path public\hot) { Remove-Item public\hot }`
- Levantar servidores:
  - `php artisan serve --host=127.0.0.1 --port=8000`
  - `npm run dev`
- Abrir `http://127.0.0.1:8000` y confirmar que Welcome carga.

## Rutas y vistas a probar (mÃ­nimo)
- `/` â†’ Welcome
- `/login` â†’ Login
- `/register` â†’ Register
- `/dashboard` â†’ Dashboard (prueba redirecciÃ³n y con usuario logueado)
- `/stores` â†’ Stores/Index (paginaciÃ³n, filtros)
- `/products` â†’ Listado (paginaciÃ³n/filtros/orden)
- `/categories` â†’ Listado y detalle
- Carrito: aÃ±adir producto, listar, modificar cantidades, eliminar
- Checkout: iniciar checkout, confirmar orden
- Orders: listado, detalle, cambio de estado
- Perfil/Ajustes: ediciÃ³n bÃ¡sica
- BÃºsqueda y filtros en listados
- Rutas inexistentes (404)

## Checklist de verificaciÃ³n (UI/Render/Calidad)
- Sin errores 404 de assets (CSS/JS/imagenes).
- Sin errores en consola del navegador.
- Estilos aplicados (Tailwind/stylesheet) en todas las pÃ¡ginas.
- NavegaciÃ³n Inertia sin recargas completas (SPA feel).
- PaginaciÃ³n, filtros y orden funcionan.
- Formularios: validaciones, mensajes visibles.
- Estados de carga visibles (skeleton/spinner).
- Comportamiento en refresh (F5) desde rutas internas.
- Responsivo bÃ¡sico (desktop/tablet/mÃ³vil).
- Accesibilidad bÃ¡sica (focus, contraste, labels).

## Flujos crÃ­ticos (end-to-end)
- Registro â†’ Login â†’ Dashboard
- Explorar tiendas y productos â†’ agregar al carrito â†’ checkout â†’ ver pedido
- Filtrar y paginar productos/categorÃ­as/tiendas
- Editar perfil (si existe)
- Cerrar sesiÃ³n y reingresar

## CorrecciÃ³n de errores (workflow)
- Registrar bug: tÃ­tulo, pasos, resultado actual/esperado, evidencia (screenshot, logs).
- Corregir cÃ³digo (frontend/back).
- Verificar en servidor.
- Anotar fix: archivos, resumen, razÃ³n.
- No tocar migraciones salvo autorizaciÃ³n.
- Seeds manuales si es necesario.

## Entregables
- Informe de pruebas (Markdown):
  - Resumen general.
  - Lista de issues y estado.
  - Evidencias (capturas, logs).
  - Cambios realizados.
  - Recomendaciones finales.
- ConfirmaciÃ³n de:
  - Welcome, Login y Register renderizan OK.
  - Flujos crÃ­ticos completados sin errores.
  - 0 errores en consola.
  - Estilos y assets cargan en todas las rutas.

---

Este plan servirÃ¡ para guiar las pruebas exhaustivas y asegurar la calidad de la aplicaciÃ³n.

