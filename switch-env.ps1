param(
  [Parameter(Mandatory = $true)]
  [ValidateSet("local", "railway")]
  [string]$Target
)

$ErrorActionPreference = "Stop"

function Copy-IfExists {
  param(
    [string]$Source,
    [string]$Destination
  )

  if (-not (Test-Path $Source)) {
    throw "No existe el archivo requerido: $Source"
  }

  Copy-Item -Path $Source -Destination $Destination -Force
}

Write-Host "Cambiando entorno a: $Target" -ForegroundColor Cyan

$backendSource = ".env.$Target"
$backendDest = ".env"
Copy-IfExists -Source $backendSource -Destination $backendDest
Write-Host "Backend actualizado: $backendSource -> $backendDest" -ForegroundColor Green

$frontendSource = Join-Path "comercio-plus-frontend" ".env.$Target"
$frontendDest = Join-Path "comercio-plus-frontend" ".env"

if (Test-Path $frontendSource) {
  Copy-Item -Path $frontendSource -Destination $frontendDest -Force
  Write-Host "Frontend actualizado: $frontendSource -> $frontendDest" -ForegroundColor Green
}
else {
  Write-Host "Aviso: no existe $frontendSource (frontend no modificado)." -ForegroundColor Yellow
}

Write-Host "Limpiando cache de configuracion Laravel..." -ForegroundColor Cyan
php artisan config:clear | Out-Host

Write-Host "Entorno activo listo: $Target" -ForegroundColor Green

