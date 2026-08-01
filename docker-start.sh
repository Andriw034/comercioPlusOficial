#!/bin/sh
# Arranque del backend en produccion (Render).
#
# Regla de oro: el servidor web SIEMPRE tiene que levantar, aunque la base de
# datos no responda. Antes las migraciones corrian encadenadas con && al
# servidor, asi que una base caida dejaba el contenedor sin arrancar y las
# peticiones se quedaban esperando para siempre, sin error ni pista alguna.
# Diagnosticar eso desde afuera es casi imposible.

set -u

# 1. Certificado TLS para MySQL gestionado (Aiven). No hace nada si no aplica,
#    asi los proveedores que no piden TLS quedan igual que antes.
if [ -n "${AIVEN_CA_CERT:-}" ]; then
  mkdir -p storage
  printf '%s' "$AIVEN_CA_CERT" > "${MYSQL_ATTR_SSL_CA:-/app/storage/aiven-ca.pem}"
fi

# 2. Enlace publico de storage. Sin esto, los archivos que caen al disco local
#    (cuando Cloudinary no esta configurado) quedan inalcanzables: la subida
#    responde "ok" y la URL devuelve 404 para siempre.
#
#    El repo versiona public/storage como carpeta real (solo contiene un
#    .gitignore), y Laravel se niega a reemplazar una carpeta por un enlace: sin
#    quitarla primero, storage:link falla siempre. Solo se borra si esta vacia,
#    para no destruir archivos de nadie en un despliegue con disco persistente.
if [ -d public/storage ] && [ ! -L public/storage ]; then
  if [ -z "$(ls -A public/storage 2>/dev/null | grep -v '^\.gitignore$')" ]; then
    rm -rf public/storage
  else
    echo "[arranque] aviso: public/storage tiene archivos, no se convierte en enlace"
  fi
fi

php artisan storage:link --force \
  || echo "[arranque] aviso: no se pudo crear el enlace de storage"

# 3. Migraciones. Si fallan NO se aborta el arranque: se deja el error en el log
#    y el servidor levanta igual, para que /api/health/integrations pueda
#    reportar que la base esta caida en vez de no contestar nada.
if ! php artisan migrate --force; then
  echo "[arranque] ERROR: fallaron las migraciones. La base de datos puede estar"
  echo "[arranque] apagada o inaccesible. El servidor arranca igual;"
  echo "[arranque] consulta /api/health/integrations para ver el detalle."
fi

php artisan optimize:clear || true

exec php -S 0.0.0.0:"${PORT:-8080}" -t public public/index.php
