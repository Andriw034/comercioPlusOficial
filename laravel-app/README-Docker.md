# Ejecutar Pruebas de Laravel con Docker

Este documento explica cómo ejecutar las pruebas de autenticación de Laravel usando Docker.

## Requisitos Previos

- Docker instalado en tu sistema
- Docker Compose instalado

## Archivos Creados

- `Dockerfile`: Configuración de la imagen Docker para Laravel
- `docker-compose.yml`: Configuración de servicios (app, webserver, base de datos)
- `nginx.conf`: Configuración del servidor web
- `.env`: Variables de entorno para el entorno de pruebas
- `run-tests.sh`: Script para ejecutar las pruebas automáticamente

## Cómo Ejecutar las Pruebas

### Opción 1: Usar el script automático (recomendado)

```bash
cd laravel-app
./run-tests.sh
```

Este script:
1. Construye y inicia los contenedores
2. Espera a que estén listos
3. Genera la clave de la aplicación
4. Ejecuta las migraciones de la base de datos
5. Ejecuta todas las pruebas
6. Detiene los contenedores

### Opción 2: Ejecutar manualmente

1. Construir e iniciar los contenedores:
```bash
docker-compose up -d --build
```

2. Esperar a que los contenedores estén listos (aprox. 10-15 segundos)

3. Generar la clave de la aplicación:
```bash
docker-compose exec app php artisan key:generate
```

4. Ejecutar migraciones:
```bash
docker-compose exec app php artisan migrate --force
```

5. Ejecutar las pruebas:
```bash
docker-compose exec app php artisan test
```

6. Detener los contenedores:
```bash
docker-compose down
```

## Acceder a la Aplicación

Una vez que los contenedores estén ejecutándose, puedes acceder a la aplicación en:
- http://localhost:8080

## Servicios Incluidos

- **app**: Aplicación Laravel con PHP 8.1
- **webserver**: Servidor Nginx
- **db**: Base de datos MySQL 8.0

## Pruebas Incluidas

Las pruebas cubren:
- Registro de usuarios (cliente y comerciante)
- Inicio de sesión
- Validaciones de formularios
- Manejo de errores
- Redirecciones basadas en roles
- Cierre de sesión

## Solución de Problemas

Si encuentras errores:

1. Asegúrate de que Docker esté ejecutándose
2. Verifica que los puertos 8080 y 3306 estén disponibles
3. Si hay errores de permisos, ejecuta los comandos con `sudo`
4. Para ver los logs: `docker-compose logs`

## Limpiar Contenedores

Para eliminar todos los contenedores y volúmenes:
```bash
docker-compose down -v
docker system prune -f
