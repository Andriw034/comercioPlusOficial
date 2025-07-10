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

Este reporte cubre la auditoría completa del proyecto backend, con detalles de estructura, dependencias, pruebas y pasos para ejecución.
