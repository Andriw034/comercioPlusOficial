
# Validador Pre-Codex (ComercioPlus)

Validador rapido para confirmar estado real del repo antes de correr prompts.

## Uso

PowerShell:

```powershell
.\validate_before_codex.ps1
.\validate_before_codex.ps1 backend
.\validate_before_codex.ps1 frontend
```

Bash:

```bash
bash validate_before_codex.sh
bash validate_before_codex.sh backend
bash validate_before_codex.sh frontend
```

## Que valida

- Estructura basica de repo (`artisan`, `routes/api.php`, `database/migrations`).
- PHP/artisan y estado de migraciones clave (Q1/Q2).
- Rutas API criticas (`merchant/credit`, `store/verification`, `product alerts`, `merchant/orders`).
- Campos de migraciones clave (`reorder_point`, `allow_backorder`, enum `channel` con `local`).
- Frontend: Node, React, `tsc --noEmit`, ESLint con `--max-warnings=0`.
- Integridad minima de tipos y exports ERP (`src/components/erp/index.ts`, `src/types/api.ts`, `@/lib/api`).

## Interpretacion

- `FAIL`: bloqueante, corregir antes de continuar.
- `WARN`: no bloqueante, revisar.
- `PASS`: correcto.
