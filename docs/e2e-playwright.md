# E2E Playwright - Guia rapida

## Prerrequisitos
- Node.js 18+
- Dependencias de proyecto instaladas (`npm install`)
- Playwright disponible (si no existe, instalar en root):
  - `npm install -D @playwright/test`
  - `npx playwright install chromium`

## Comandos
- Headless:
  - `npm run test:e2e`
- Con navegador visible:
  - `npm run test:e2e:headed`

## Variables opcionales
- `E2E_FRONTEND_URL` (default `http://127.0.0.1:5173`)
- `E2E_API_BASE_URL` (default `http://127.0.0.1:8000/api`)
- `E2E_SKIP_WEBSERVER=1` para usar servidores ya levantados.

Ejemplo:
```bash
E2E_FRONTEND_URL=http://127.0.0.1:5173 E2E_API_BASE_URL=http://127.0.0.1:8000/api npm run test:e2e
```

## Alcance del smoke
Archivo: `tests-e2e/smoke.spec.ts`
- Registro/login merchant
- Creacion de tienda/categoria/producto (API)
- Registro/login cliente
- Navegacion a tienda publica y agregar al carrito
- Checkout (se mockea solo `/api/payments/wompi/create` para evitar salida a pasarela)
- Verificacion de pedido visible para merchant

## Nota sobre entorno offline
Si npm no tiene acceso al registro (ej. `ENOTCACHED`), la ejecucion de Playwright no podra instalar binarios/dependencias. En ese caso correr en CI o en entorno con acceso a internet.
