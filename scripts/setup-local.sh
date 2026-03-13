#
#!/bin/bash
# Script de Setup para ComercioPlus (Linux & macOS)
# Este script es idempotente y puede ser ejecutado de forma segura múltiples veces.

# Función para verificar comandos y salir con error si no se encuentran
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# --- 1. Verificación de Dependencias ---
echo "1/7 - Verificando dependencias..."

# Verificar PHP
if ! command_exists php; then
    echo "Error: php no se encuentra en el PATH. Por favor, instala PHP (>=8.1) y asegúrate de que esté en tu PATH." >&2
    exit 1
fi
php -v

# Verificar Composer
if ! command_exists composer; then
    echo "Error: composer no se encuentra en el PATH. Por favor, instala Composer." >&2
    exit 1
fi
composer -V

# Verificar Node y npm
if ! command_exists node; then
    echo "Error: node no se encuentra en el PATH. Por favor, instala Node.js (>=18.x)." >&2
    exit 1
fi
node -v

if ! command_exists npm; then
    echo "Error: npm no se encuentra en el PATH. Por favor, instala npm (>=9.x)." >&2
    exit 1
fi
npm -v

echo "Dependencias verificadas con éxito."

# --- 2. Instalar Dependencias de Backend ---
echo "\n2/7 - Instalando dependencias de Composer..."
composer install --no-interaction --prefer-dist

# --- 3. Configuración del Entorno ---
echo "\n3/7 - Configurando archivo .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo ".env creado a partir de .env.example."
else
    echo ".env ya existe, saltando la copia."
fi

# --- 4. Base de Datos SQLite y Migraciones ---
echo "\n4/7 - Preparando base de datos SQLite..."
mkdir -p database
touch database/database.sqlite
DB_PATH=$(realpath "database/database.sqlite")

# Actualizar .env con la ruta absoluta a la DB de SQLite
# Esto evita problemas con la ubicación desde donde se ejecutan los comandos artisan
sed -i -e "s#^DB_DATABASE=.*#DB_DATABASE=${DB_PATH}#" .env

echo "Generando clave de aplicación..."
php artisan key:generate

echo "Ejecutando migraciones de la base de datos..."
php artisan migrate --seed

# --- 5. Enlaces y Archivos Generados ---
echo "\n5/7 - Creando enlace de almacenamiento y generando archivos Ziggy..."
php artisan storage:link || echo "Advertencia: No se pudo crear el enlace simbólico de almacenamiento. Puede que necesites ejecutarlo con privilegios elevados."

php artisan ziggy:generate || echo "Advertencia: php artisan ziggy:generate falló. La aplicación usará el stub de fallback. Re-ejecuta este comando cuando el backend esté completamente funcional."

# --- 6. Instalar Dependencias de Frontend ---
echo "\n6/7 - Instalando dependencias de npm..."
npm install

# --- 7. Compilar Assets de Frontend ---
echo "\n7/7 - Compilando assets del frontend (Vite)..."
npm run build

echo "\n¡Setup completado! ✨"
echo "Para iniciar el servidor de desarrollo, ejecuta: php artisan serve"
