#
# Script de Setup para ComercioPlus (Windows - PowerShell)
# Este script es idempotente y puede ser ejecutado de forma segura múltiples veces.
# Ejecutar desde la raíz del proyecto: .\scripts\setup-local.ps1

# --- 1. Verificación de Dependencias ---
Write-Host "1/7 - Verificando dependencias..."

$php_exists = (Get-Command php -ErrorAction SilentlyContinue)
$composer_exists = (Get-Command composer -ErrorAction SilentlyContinue)
$node_exists = (Get-Command node -ErrorAction SilentlyContinue)
$npm_exists = (Get-Command npm -ErrorAction SilentlyContinue)

if (-not $php_exists) {
    Write-Error "Error: php no se encuentra en el PATH. Por favor, instala PHP (>=8.1) y agrégalo a tu PATH."
    exit 1
}
php -v

if (-not $composer_exists) {
    Write-Error "Error: composer no se encuentra en el PATH. Por favor, instala Composer."
    exit 1
}
composer -V

if (-not $node_exists) {
    Write-Error "Error: node no se encuentra en el PATH. Por favor, instala Node.js (>=18.x)."
    exit 1
}
node -v

if (-not $npm_exists) {
    Write-Error "Error: npm no se encuentra en el PATH. Por favor, instala npm (>=9.x)."
    exit 1
}
npm -v

Write-Host "Dependencias verificadas con éxito."

# --- 2. Instalar Dependencias de Backend ---
Write-Host "`n2/7 - Instalando dependencias de Composer..."
composer install --no-interaction --prefer-dist

# --- 3. Configuración del Entorno ---
Write-Host "`n3/7 - Configurando archivo .env..."
if (-not (Test-Path ".env")) {
    Copy-Item .env.example .env
    Write-Host ".env creado a partir de .env.example."
} else {
    Write-Host ".env ya existe, saltando la copia."
}

# --- 4. Base de Datos SQLite y Migraciones ---
Write-Host "`n4/7 - Preparando base de datos SQLite..."
if (-not (Test-Path "database")) { New-Item -ItemType Directory -Force -Path "database" | Out-Null }
if (-not (Test-Path "database/database.sqlite")) { New-Item -ItemType File -Force -Path "database/database.sqlite" | Out-Null }

$dbPath = (Resolve-Path "database/database.sqlite").Path

# Actualizar .env con la ruta absoluta a la DB de SQLite
(Get-Content .env) | ForEach-Object {
    $_ -replace '(^DB_DATABASE=).*', "`$1${dbPath}"
} | Set-Content .env

Write-Host "Generando clave de aplicación..."
php artisan key:generate

Write-Host "Ejecutando migraciones de la base de datos..."
php artisan migrate --seed

# --- 5. Enlaces y Archivos Generados ---
Write-Host "`n5/7 - Creando enlace de almacenamiento y generando archivos Ziggy..."
try {
    php artisan storage:link
} catch {
    Write-Warning "No se pudo crear el enlace simbólico de almacenamiento. Puede que necesites ejecutarlo como Administrador."
}

try {
    php artisan ziggy:generate
} catch {
    Write-Warning "php artisan ziggy:generate falló. La aplicación usará el stub de fallback. Re-ejecuta este comando cuando el backend esté completamente funcional."
}

# --- 6. Instalar Dependencias de Frontend ---
Write-Host "`n6/7 - Instalando dependencias de npm..."
npm install

# --- 7. Compilar Assets de Frontend ---
Write-Host "`n7/7 - Compilando assets del frontend (Vite)..."
npm run build

Write-Host "`n¡Setup completado! ✨"
Write-Host "Para iniciar el servidor de desarrollo, ejecuta: php artisan serve"
