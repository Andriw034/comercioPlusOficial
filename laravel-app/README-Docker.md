# Configuración del entorno local para Laravel con Docker

Este documento describe cómo preparar un entorno local para ejecutar la aplicación Laravel usando Docker, facilitando la instalación de PHP, servidor web, base de datos y otros servicios necesarios.

## Requisitos previos

- Tener instalado Docker y Docker Compose en tu máquina local.

## Pasos para levantar el entorno

1. Clonar el repositorio y posicionarse en la carpeta del proyecto.

2. Verificar que el archivo `docker-compose.yml` y `Dockerfile` estén presentes en la carpeta `laravel-app/`.

3. Construir y levantar los contenedores:

```bash
cd laravel-app
docker-compose up -d --build
```

4. Acceder al contenedor de la aplicación para ejecutar comandos artisan:

```bash
docker-compose exec app bash
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

5. Acceder a la aplicación en el navegador:

```
http://localhost:8000
```

## Archivos importantes

- `docker-compose.yml`: Define los servicios (app PHP, base de datos, servidor web).
- `Dockerfile`: Configura la imagen PHP con extensiones necesarias.
- `.env`: Configuración de entorno Laravel (base de datos, APP_KEY, etc).

## Notas

- El contenedor `app` tiene PHP instalado y configurado.
- La base de datos puede ser MySQL o PostgreSQL según configuración.
- Para detener el entorno:

```bash
docker-compose down
```

---

Si necesitas ayuda para configurar o ejecutar el entorno, indícalo para guiarte paso a paso.
