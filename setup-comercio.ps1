param(
  [string]$RepoUrl = "https://github.com/Andriw034/comercioPlusOficial.git",
  [string]$Branch = "tablar",
  [string]$FirebaseProjectId = ""
)

Set-StrictMode -Version Latest

$RepoName = $RepoUrl.Split('/')[-1].Replace('.git','')

Write-Host "Clonando repo..."
git clone $RepoUrl $RepoName
Set-Location $RepoName

Write-Host "Checkout a rama $Branch..."
git fetch origin
if (git rev-parse --verify "origin/$Branch" -ErrorAction SilentlyContinue) {
  git checkout $Branch
} else {
  Write-Warning "La rama $Branch no existe en el repositorio remoto. Se usará la rama por defecto."
}

if (-not (Test-Path .env)) {
  if (Test-Path .env.example) {
    Copy-Item .env.example .env
    Write-Host ".env creado desde .env.example"
  } else {
    Write-Host "No existe .env.example. Crea .env manualmente."
  }
} else {
  Write-Host ".env ya existe, se mantendrá."
}

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
  Write-Error "Composer no está instalado. Instálalo antes de continuar."
  exit 1
}
composer install --no-interaction --prefer-dist --optimize-autoloader

if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
  Write-Error "Node no está instalado. Instálalo antes de continuar."
  exit 1
}
if (Test-Path package-lock.json) {
  npm ci
} else {
  npm install
}

php artisan key:generate --ansi

$migrate = Read-Host "¿Ejecutar migraciones ahora? (y/N)"
if ($migrate -eq "y") {
  php artisan migrate --force
}

try {
    npm run build -ErrorAction Stop
} catch {
    try {
        npm run prod -ErrorAction Stop
    } catch {
        npm run dev
    }
}

if ($FirebaseProjectId -ne "") {
  if (-not (Get-Command firebase -ErrorAction SilentlyContinue)) {
    npm install -g firebase-tools
  }
  $deploy = Read-Host "¿Desplegar a Firebase Hosting ahora? (y/N)"
  if ($deploy -eq "y") {
    firebase deploy --only hosting --project $FirebaseProjectId
  }
}

Write-Host "¡Listo! Proyecto preparado en .\$RepoName"
