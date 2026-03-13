#!/usr/bin/env bash
# validate_before_codex.sh
# Run from repo root:
#   bash validate_before_codex.sh
#   bash validate_before_codex.sh backend
#   bash validate_before_codex.sh frontend

set -u -o pipefail

MODE="${1:-all}"
ERRORS=0
WARNINGS=0
PASSES=0

pass() { echo "  [PASS] $1"; PASSES=$((PASSES + 1)); }
warn() { echo "  [WARN] $1"; WARNINGS=$((WARNINGS + 1)); }
fail() { echo "  [FAIL] $1"; ERRORS=$((ERRORS + 1)); }
info() { echo "  [INFO] $1"; }
header() { echo ""; echo "=== $1 ==="; }

has_command() { command -v "$1" >/dev/null 2>&1; }

test_migration_ran() {
  local status_text="$1"
  local migration="$2"
  local label="$3"
  local line
  line="$(printf "%s\n" "$status_text" | grep -E "$migration" | head -n 1 || true)"
  if [[ -z "$line" ]]; then
    warn "Migracion no encontrada: $label"
    return
  fi
  if printf "%s\n" "$line" | grep -Eq "\] Ran[[:space:]]*$"; then
    pass "Migracion aplicada: $label"
  else
    fail "Migracion pendiente: $label"
  fi
}

run_backend() {
  header "1. REPO"
  [[ -f artisan ]] && pass "artisan existe" || fail "artisan no existe"
  [[ -d app/Models ]] && pass "app/Models existe" || fail "app/Models no existe"
  [[ -d database/migrations ]] && pass "database/migrations existe" || fail "database/migrations no existe"
  [[ -f routes/api.php ]] && pass "routes/api.php existe" || fail "routes/api.php no existe"

  header "2. PHP / ARTISAN"
  if has_command php; then
    php_ver="$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || true)"
    pass "PHP disponible: ${php_ver:-unknown}"
  else
    fail "PHP no esta en PATH"
    return
  fi

  if php artisan --version >/dev/null 2>&1; then
    pass "Artisan responde"
  else
    fail "php artisan no responde"
  fi

  header "3. MIGRACIONES"
  ms="$(php artisan migrate:status 2>&1 || true)"
  if [[ -z "$ms" ]] || printf "%s\n" "$ms" | grep -Eiq "ERROR|Could not|exception"; then
    fail "migrate:status fallo"
  else
    pass "migrate:status OK"
    test_migration_ran "$ms" "2026_02_27_000001_add_channel_to_orders_table" "Q1 channel orders"
    test_migration_ran "$ms" "2026_02_27_100001_create_credit_accounts_table" "Q2 credit_accounts"
    test_migration_ran "$ms" "2026_02_27_100002_create_credit_transactions_table" "Q2 credit_transactions"
    test_migration_ran "$ms" "2026_02_27_100003_add_is_verified_to_stores_table" "Q2 stores.is_verified"
    test_migration_ran "$ms" "2026_02_27_100004_create_store_verifications_table" "Q2 store_verifications"
    test_migration_ran "$ms" "2026_02_27_100005_create_product_alerts_table" "Q2 product_alerts"
  fi

  header "4. MODELOS"
  for m in Store Order Product Customer CreditAccount CreditTransaction StoreVerification ProductAlert; do
    [[ -f "app/Models/${m}.php" ]] && pass "Modelo: $m" || fail "Modelo faltante: $m"
  done

  header "5. RUTAS API CLAVE"
  if [[ -f routes/api.php ]]; then
    api_text="$(cat routes/api.php)"
    printf "%s\n" "$api_text" | grep -q "Route::get('/merchant/orders'" && pass "GET /merchant/orders" || fail "Falta GET /merchant/orders"
    printf "%s\n" "$api_text" | grep -q "Route::get('/merchant/credit'" && pass "GET /merchant/credit" || fail "Falta GET /merchant/credit"
    printf "%s\n" "$api_text" | grep -q "Route::post('/merchant/credit/{creditAccount}/charge'" && pass "POST /merchant/credit/{creditAccount}/charge" || fail "Falta charge"
    printf "%s\n" "$api_text" | grep -q "Route::post('/merchant/credit/{creditAccount}/payment'" && pass "POST /merchant/credit/{creditAccount}/payment" || fail "Falta payment"
    printf "%s\n" "$api_text" | grep -q "Route::get('/merchant/store/verification'" && pass "GET /merchant/store/verification" || fail "Falta GET verification"
    printf "%s\n" "$api_text" | grep -q "Route::post('/merchant/store/verification'" && pass "POST /merchant/store/verification" || fail "Falta POST verification"
    printf "%s\n" "$api_text" | grep -q "Route::get('/products/{product}/alerts/mine'" && pass "GET /products/{product}/alerts/mine" || fail "Falta mine alert"
    printf "%s\n" "$api_text" | grep -q "Route::post('/products/{product}/alerts'" && pass "POST /products/{product}/alerts" || fail "Falta store alert"
    printf "%s\n" "$api_text" | grep -q "Route::delete('/products/{product}/alerts'" && pass "DELETE /products/{product}/alerts" || fail "Falta delete alert"
    printf "%s\n" "$api_text" | grep -q "tax-settings" && pass "tax-settings detectado" || fail "Falta tax-settings"
  else
    fail "No se pudo leer routes/api.php"
  fi

  header "6. CAMPOS CLAVE"
  inv_file="$(find database/migrations -type f -name "*add_inventory_fields*" | head -n 1 || true)"
  if [[ -n "$inv_file" ]]; then
    grep -q "reorder_point" "$inv_file" && pass "reorder_point existe" || fail "reorder_point no encontrado"
    grep -q "allow_backorder" "$inv_file" && pass "allow_backorder existe" || fail "allow_backorder no encontrado"
    grep -q "min_stock" "$inv_file" && warn "min_stock encontrado; revisar colision de migraciones" || pass "min_stock no aparece"
  else
    warn "No se encontro migracion add_inventory_fields"
  fi

  channel_file="$(find database/migrations -type f \( -name "*channel*orders*" -o -name "*add_channel*" \) | head -n 1 || true)"
  if [[ -n "$channel_file" ]]; then
    if grep -q "'local'" "$channel_file" || grep -q "\"local\"" "$channel_file"; then
      pass "Enum channel incluye 'local'"
    else
      warn "Enum channel sin 'local'"
    fi
  else
    warn "Migracion de channel no encontrada"
  fi

  if [[ -f app/Models/Order.php ]]; then
    grep -Eq "order_products|orderProducts|ordenproducts" app/Models/Order.php && pass "Relacion order_products detectada en Order.php" || warn "Relacion order_products no detectada"
  fi
}

run_frontend() {
  header "7. FRONTEND"
  front_dir=""
  for d in comercio-plus-frontend frontend resources/js; do
    [[ -d "$d" ]] && front_dir="$d" && break
  done
  if [[ -z "$front_dir" ]]; then
    fail "No se encontro frontend"
    return
  fi
  pass "Frontend: $front_dir"

  pushd "$front_dir" >/dev/null || return

  if has_command node; then
    pass "Node: $(node --version 2>/dev/null || echo unknown)"
  else
    fail "Node no esta en PATH"
  fi

  [[ -f package.json ]] && pass "package.json existe" || fail "package.json no existe"
  react_ver="$(node -e "const p=require('./package.json'); console.log((p.dependencies&&p.dependencies.react)||(p.devDependencies&&p.devDependencies.react)||'not-found')" 2>/dev/null || echo not-found)"
  [[ "$react_ver" =~ ^[\^~]?19 ]] && pass "React 19: $react_ver" || warn "React: $react_ver"
  [[ -d node_modules ]] && pass "node_modules existe" || fail "node_modules no existe"

  header "8. TSC"
  tsc_out="$(npx tsc --noEmit 2>&1 || true)"
  tsc_errs="$(printf "%s\n" "$tsc_out" | grep -c "error TS" || true)"
  [[ "$tsc_errs" == "0" ]] && pass "TypeScript sin errores" || fail "TypeScript reporta $tsc_errs errores"

  header "9. ESLINT"
  if npm run lint -- --max-warnings=0 >/tmp/validate_before_codex_lint.log 2>&1; then
    pass "ESLint sin errores ni warnings"
  else
    fail "ESLint fallo con max-warnings=0"
  fi

  header "10. CHECKS TS/API"
  if [[ -f src/components/erp/index.ts ]]; then
    pass "Barrel ERP index.ts existe"
    idx="$(cat src/components/erp/index.ts)"
    for sym in ErpKpiCard ErpBadge ErpPageHeader ErpBtn ErpSearchBar ErpFilterSelect; do
      printf "%s\n" "$idx" | grep -q "$sym" && pass "Exportado: $sym" || fail "No exportado: $sym"
    done
    printf "%s\n" "$idx" | grep -q "ErpTable" && fail "ErpTable aparece en exports (debe quitarse)" || pass "ErpTable no aparece en exports"
  elif [[ -f src/components/erp/index.tsx ]]; then
    fail "Existe index.tsx; debe ser index.ts"
  else
    warn "No existe src/components/erp/index.ts"
  fi

  if [[ -f src/lib/api.ts || -f src/lib/api.tsx ]]; then
    pass "@/lib/api existe"
  else
    fail "@/lib/api no existe"
  fi

  if [[ -f src/types/api.ts ]]; then
    api_types="$(cat src/types/api.ts)"
    printf "%s\n" "$api_types" | grep -q "CreditAccountRow" && pass "Tipo CreditAccountRow existe" || warn "Falta CreditAccountRow"
    printf "%s\n" "$api_types" | grep -q "CreditTransactionRow" && pass "Tipo CreditTransactionRow existe" || warn "Falta CreditTransactionRow"
    printf "%s\n" "$api_types" | grep -q "StoreVerification" && pass "Tipo StoreVerification existe" || warn "Falta StoreVerification"
    printf "%s\n" "$api_types" | grep -q "customer_name" && fail "customer_name detectado en tipos; debe usarse customer.user.name" || pass "No hay customer_name plano en tipos"
  else
    fail "No existe src/types/api.ts"
  fi

  router_file=""
  for rf in src/app/App.tsx src/App.tsx src/router.tsx src/routes.tsx; do
    [[ -f "$rf" ]] && router_file="$rf" && break
  done
  if [[ -n "$router_file" ]]; then
    pass "Router: $router_file"
    router="$(cat "$router_file")"
    for rt in orders products customers inventory credit reports settings categories; do
      printf "%s\n" "$router" | grep -qi "$rt" && pass "Ruta dashboard detectada: $rt" || warn "Ruta dashboard no detectada: $rt"
    done
  else
    warn "No se encontro archivo de rutas principal"
  fi

  popd >/dev/null || true
}

echo "============================================"
echo "  ComercioPlus Pre-Codex Validator"
echo "============================================"
echo "Dir:  $(pwd)"
echo "Modo: $MODE"
echo "Hora: $(date '+%Y-%m-%d %H:%M:%S')"

case "$MODE" in
  backend) run_backend ;;
  frontend) run_frontend ;;
  all) run_backend; run_frontend ;;
  *) fail "Modo invalido: $MODE (usa all|backend|frontend)" ;;
esac

echo ""
echo "============================================"
echo "RESUMEN FINAL"
echo "============================================"
echo "PASS: $PASSES"
echo "WARN: $WARNINGS"
echo "FAIL: $ERRORS"

if [[ "$ERRORS" -gt 0 ]]; then
  echo "Hay errores criticos. No avanzar con prompts."
  exit 1
fi

if [[ "$WARNINGS" -gt 0 ]]; then
  echo "Hay advertencias. Revisar antes de continuar."
  exit 0
fi

echo "Todo en orden."
exit 0
