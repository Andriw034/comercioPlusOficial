# -------------------------------------------------------------
# Test completo ComercioPlus: Backend + Frontend + Endpoints
# -------------------------------------------------------------

# Configuración
$backendPath = "E:\htdocs\comercioPlusOficial"
$frontendPath = "E:\htdocs\comercio-plus-frontend"
$backendURL = "http://127.0.0.1:8000"
$frontendURL = "http://127.0.0.1:3000"
$apiCategories = "$backendURL/api/categories"
$apiProducts = "$backendURL/api/products"

# Función para comprobar HTTP
function Test-HTTP($url) {
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ $url OK" -ForegroundColor Green
        } else {
            Write-Host "❌ $url status: $($response.StatusCode)" -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ $url ERROR: $_" -ForegroundColor Red
    }
}

# -------------------------------------------------------------
# 1️⃣ Levantar Backend Laravel
# -------------------------------------------------------------
Write-Host "🔹 Iniciando Backend Laravel..." -ForegroundColor Cyan
Start-Process powershell -ArgumentList "-NoExit","-Command cd `"$backendPath`"; php artisan serve --host=127.0.0.1 --port=8000"

Start-Sleep -Seconds 5

# -------------------------------------------------------------
# 2️⃣ Levantar Frontend Vue
# -------------------------------------------------------------
Write-Host "🔹 Iniciando Frontend Vue 3..." -ForegroundColor Cyan
Start-Process powershell -ArgumentList "-NoExit","-Command cd `"$frontendPath`"; npm run dev"

Start-Sleep -Seconds 5

# -------------------------------------------------------------
# 3️⃣ Probar Endpoints API
# -------------------------------------------------------------
Write-Host "`n🔹 Probando Endpoints API..." -ForegroundColor Cyan
Test-HTTP $apiCategories
Test-HTTP $apiProducts

# -------------------------------------------------------------
# 4️⃣ Probar Vistas Blade
# -------------------------------------------------------------
Write-Host "`n🔹 Probando Vistas Blade..." -ForegroundColor Cyan
Test-HTTP $backendURL
Test-HTTP "$backendURL/dashboard"

Write-Host "`n🔹 Test completado. Revisa colores, vistas y endpoints." -ForegroundColor Yellow
Write-Host "Abre los navegadores: $backendURL y $frontendURL" -ForegroundColor Yellow
