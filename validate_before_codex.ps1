# validate_before_codex.ps1
# Run from repo root:
#   .\validate_before_codex.ps1
#   .\validate_before_codex.ps1 backend
#   .\validate_before_codex.ps1 frontend

param([string]$Mode = "all")

$global:ERRORS = 0
$global:WARNINGS = 0
$global:PASSES = 0

function pass { param([string]$msg) Write-Host "  [PASS] $msg" -ForegroundColor Green; $global:PASSES++ }
function warn { param([string]$msg) Write-Host "  [WARN] $msg" -ForegroundColor Yellow; $global:WARNINGS++ }
function fail { param([string]$msg) Write-Host "  [FAIL] $msg" -ForegroundColor Red; $global:ERRORS++ }
function info { param([string]$msg) Write-Host "  [INFO] $msg" -ForegroundColor Cyan }
function header { param([string]$msg) Write-Host "`n=== $msg ===" -ForegroundColor Blue }

function Has-Command {
    param([string]$Name)
    return [bool](Get-Command $Name -ErrorAction SilentlyContinue)
}

function Test-MigrationRan {
    param(
        [string]$StatusText,
        [string]$MigrationName,
        [string]$Label
    )

    $line = ($StatusText -split "`r?`n" | Where-Object { $_ -match [regex]::Escape($MigrationName) } | Select-Object -First 1)
    if (-not $line) {
        warn "Migracion no encontrada: $Label"
        return
    }

    if ($line -match "\]\s+Ran\s*$") {
        pass "Migracion aplicada: $Label"
    } else {
        fail "Migracion pendiente: $Label"
    }
}

function Check-RoutePattern {
    param([string]$ApiText, [string]$Pattern, [string]$Label)
    if ($ApiText -match $Pattern) { pass "Ruta: $Label" } else { fail "Ruta no encontrada: $Label" }
}

function Run-Backend {
    header "1. REPO"
    if (Test-Path "artisan") { pass "artisan existe" } else { fail "artisan no existe" }
    if (Test-Path "app\Models") { pass "app\Models existe" } else { fail "app\Models no existe" }
    if (Test-Path "database\migrations") { pass "database/migrations existe" } else { fail "database/migrations no existe" }
    if (Test-Path "routes\api.php") { pass "routes/api.php existe" } else { fail "routes/api.php no existe" }

    header "2. PHP / ARTISAN"
    if (Has-Command "php") {
        $phpVersion = (& php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>$null)
        pass "PHP disponible: $phpVersion"
    } else {
        fail "PHP no esta en PATH"
        return
    }

    $artisanVersion = (& php artisan --version 2>$null)
    if ($LASTEXITCODE -eq 0) { pass "Artisan OK: $artisanVersion" } else { fail "php artisan no responde" }

    header "3. MIGRACIONES"
    $ms = (& php artisan migrate:status --no-ansi 2>&1 | Out-String)
    if ($LASTEXITCODE -ne 0 -or $ms -match "ERROR|Could not|exception") {
        fail "migrate:status fallo"
    } else {
        pass "migrate:status OK"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_000001_add_channel_to_orders_table" -Label "Q1 channel orders"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_100001_create_credit_accounts_table" -Label "Q2 credit_accounts"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_100002_create_credit_transactions_table" -Label "Q2 credit_transactions"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_100003_add_is_verified_to_stores_table" -Label "Q2 stores.is_verified"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_100004_create_store_verifications_table" -Label "Q2 store_verifications"
        Test-MigrationRan -StatusText $ms -MigrationName "2026_02_27_100005_create_product_alerts_table" -Label "Q2 product_alerts"
    }

    header "4. MODELOS"
    foreach ($m in @("Store", "Order", "Product", "Customer", "CreditAccount", "CreditTransaction", "StoreVerification", "ProductAlert")) {
        if (Test-Path "app\Models\$m.php") { pass "Modelo: $m" } else { fail "Modelo faltante: $m" }
    }

    header "5. RUTAS API CLAVE"
    $api = Get-Content "routes\api.php" -Raw -ErrorAction SilentlyContinue
    if (-not $api) {
        fail "No se pudo leer routes/api.php"
    } else {
        Check-RoutePattern -ApiText $api -Pattern "Route::get\('/merchant/orders'" -Label "GET /merchant/orders"
        Check-RoutePattern -ApiText $api -Pattern "Route::get\('/merchant/credit'" -Label "GET /merchant/credit"
        Check-RoutePattern -ApiText $api -Pattern "Route::post\('/merchant/credit/\{creditAccount\}/charge'" -Label "POST /merchant/credit/{creditAccount}/charge"
        Check-RoutePattern -ApiText $api -Pattern "Route::post\('/merchant/credit/\{creditAccount\}/payment'" -Label "POST /merchant/credit/{creditAccount}/payment"
        Check-RoutePattern -ApiText $api -Pattern "Route::get\('/merchant/store/verification'" -Label "GET /merchant/store/verification"
        Check-RoutePattern -ApiText $api -Pattern "Route::post\('/merchant/store/verification'" -Label "POST /merchant/store/verification"
        Check-RoutePattern -ApiText $api -Pattern "Route::get\('/products/\{product\}/alerts/mine'" -Label "GET /products/{product}/alerts/mine"
        Check-RoutePattern -ApiText $api -Pattern "Route::post\('/products/\{product\}/alerts'" -Label "POST /products/{product}/alerts"
        Check-RoutePattern -ApiText $api -Pattern "Route::delete\('/products/\{product\}/alerts'" -Label "DELETE /products/{product}/alerts"
        Check-RoutePattern -ApiText $api -Pattern "tax-settings" -Label "tax-settings"
    }

    header "6. CAMPOS CLAVE"
    $invFile = Get-ChildItem "database\migrations" -Filter "*add_inventory_fields*" | Select-Object -First 1
    if ($invFile) {
        $inv = Get-Content $invFile.FullName -Raw
        if ($inv -match "reorder_point") { pass "reorder_point existe" } else { fail "reorder_point no encontrado" }
        if ($inv -match "allow_backorder") { pass "allow_backorder existe" } else { fail "allow_backorder no encontrado" }
        if ($inv -match "min_stock") { warn "min_stock encontrado; revisar colision de migraciones" } else { pass "min_stock no aparece" }
    } else {
        warn "No se encontro migracion add_inventory_fields"
    }

    $chFile = Get-ChildItem "database\migrations" -Filter "*channel*orders*" | Select-Object -First 1
    if (-not $chFile) { $chFile = Get-ChildItem "database\migrations" -Filter "*add_channel*" | Select-Object -First 1 }
    if ($chFile) {
        $ch = Get-Content $chFile.FullName -Raw
        if ($ch -match "'local'" -or $ch -match '"local"') { pass "Enum channel incluye 'local'" } else { warn "Enum channel sin 'local'" }
    } else {
        warn "Migracion de channel no encontrada"
    }

    $orderModel = Get-Content "app\Models\Order.php" -Raw -ErrorAction SilentlyContinue
    if ($orderModel) {
        if ($orderModel -match "order_products|orderProducts|ordenproducts") { pass "Relacion order_products detectada en Order.php" } else { warn "Relacion order_products no detectada" }
    }
}

function Get-ReactVersion {
    if (-not (Test-Path "package.json")) { return "not-found" }
    try {
        $pkg = Get-Content "package.json" -Raw | ConvertFrom-Json
        if ($pkg.dependencies -and $pkg.dependencies.PSObject.Properties.Name -contains "react") { return [string]$pkg.dependencies.react }
        if ($pkg.devDependencies -and $pkg.devDependencies.PSObject.Properties.Name -contains "react") { return [string]$pkg.devDependencies.react }
        return "not-found"
    } catch {
        return "parse-error"
    }
}

function Run-Frontend {
    header "7. FRONTEND"
    $frontDir = $null
    foreach ($d in @("comercio-plus-frontend", "frontend", "resources\js")) {
        if (Test-Path $d) { $frontDir = $d; break }
    }

    if (-not $frontDir) {
        fail "No se encontro frontend"
        return
    }
    pass "Frontend: $frontDir"

    Push-Location $frontDir
    try {
        if (Has-Command "node") {
            $nodeVersion = (& node --version 2>$null)
            pass "Node: $nodeVersion"
        } else {
            fail "Node no esta en PATH"
        }

        if (Test-Path "package.json") { pass "package.json existe" } else { fail "package.json no existe" }
        $reactVersion = Get-ReactVersion
        if ($reactVersion -match "^[\^~]?19") { pass "React 19: $reactVersion" } else { warn "React: $reactVersion" }
        if (Test-Path "node_modules") { pass "node_modules existe" } else { fail "node_modules no existe" }

        header "8. TSC"
        $tscOut = (& npx tsc --noEmit 2>&1 | Out-String)
        $tsErrors = ([regex]::Matches($tscOut, "error TS")).Count
        if ($tsErrors -eq 0) { pass "TypeScript sin errores" } else { fail "TypeScript reporta $tsErrors errores" }

        header "9. ESLINT"
        $lintOut = (& npm run lint -- --max-warnings=0 2>&1 | Out-String)
        if ($LASTEXITCODE -eq 0) {
            pass "ESLint sin errores ni warnings"
        } else {
            fail "ESLint fallo con max-warnings=0"
        }

        header "10. CHECKS TS/API"
        $erpIdx = "src\components\erp\index.ts"
        if (Test-Path $erpIdx) {
            pass "Barrel ERP index.ts existe"
            $idx = Get-Content $erpIdx -Raw
            foreach ($sym in @("ErpKpiCard", "ErpBadge", "ErpPageHeader", "ErpBtn", "ErpSearchBar", "ErpFilterSelect")) {
                if ($idx -match $sym) { pass "Exportado: $sym" } else { fail "No exportado: $sym" }
            }
            if ($idx -match "ErpTable") { fail "ErpTable aparece en exports (debe quitarse)" } else { pass "ErpTable no aparece en exports" }
        } elseif (Test-Path "src\components\erp\index.tsx") {
            fail "Existe index.tsx; debe ser index.ts"
        } else {
            warn "No existe src/components/erp/index.ts"
        }

        if ((Test-Path "src\lib\api.ts") -or (Test-Path "src\lib\api.tsx")) { pass "@/lib/api existe" } else { fail "@/lib/api no existe" }

        if (Test-Path "src\types\api.ts") {
            $apiTypes = Get-Content "src\types\api.ts" -Raw
            if ($apiTypes -match "CreditAccountRow") { pass "Tipo CreditAccountRow existe" } else { warn "Falta CreditAccountRow" }
            if ($apiTypes -match "CreditTransactionRow") { pass "Tipo CreditTransactionRow existe" } else { warn "Falta CreditTransactionRow" }
            if ($apiTypes -match "StoreVerification") { pass "Tipo StoreVerification existe" } else { warn "Falta StoreVerification" }
            if ($apiTypes -match "customer_name") { fail "customer_name detectado en tipos; debe usarse customer.user.name" } else { pass "No hay customer_name plano en tipos" }
        } else {
            fail "No existe src/types/api.ts"
        }

        $routerFile = @("src\app\App.tsx", "src\App.tsx", "src\router.tsx", "src\routes.tsx") | Where-Object { Test-Path $_ } | Select-Object -First 1
        if ($routerFile) {
            pass "Router: $routerFile"
            $router = Get-Content $routerFile -Raw
            foreach ($route in @("orders", "products", "customers", "inventory", "credit", "reports", "settings", "categories")) {
                if ($router -match $route) { pass "Ruta dashboard detectada: $route" } else { warn "Ruta dashboard no detectada: $route" }
            }
        } else {
            warn "No se encontro archivo de rutas principal"
        }
    } finally {
        Pop-Location
    }
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ComercioPlus Pre-Codex Validator" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Dir:  $(Get-Location)"
Write-Host "  Modo: $Mode"
Write-Host "  Hora: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

switch ($Mode) {
    "backend" { Run-Backend }
    "frontend" { Run-Frontend }
    default { Run-Backend; Run-Frontend }
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Blue
Write-Host "  RESUMEN FINAL" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Blue
Write-Host "  PASS: $global:PASSES" -ForegroundColor Green
Write-Host "  WARN: $global:WARNINGS" -ForegroundColor Yellow
Write-Host "  FAIL: $global:ERRORS" -ForegroundColor Red
Write-Host ""

if ($global:ERRORS -gt 0) {
    Write-Host "Hay errores criticos. No avanzar con prompts." -ForegroundColor Red
    exit 1
}

if ($global:WARNINGS -gt 0) {
    Write-Host "Hay advertencias. Revisar antes de continuar." -ForegroundColor Yellow
    exit 0
}

Write-Host "Todo en orden." -ForegroundColor Green
exit 0
