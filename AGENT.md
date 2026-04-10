# AGENT.md — ComercioPlus
# Plataforma e-commerce de repuestos de motos (Colombia)

## Proyecto
- Nombre: ComercioPlus
- Dominio: E-commerce B2C para repuestos de motos
- Usuarios: Merchant (comerciante) y Client (comprador)
- País: Colombia
- Producción: https://comercio-plus-oficial.vercel.app

## Fuente de verdad
- Documento canónico: `docs/UNIVERSAL_COMERCIOPLUS.md`
- Gobierno documental: `docs/DOC_GOVERNANCE.md`
- Si hay conflicto entre docs y código, MANDA EL CÓDIGO.
- Leer el universal ANTES de proponer cambios arquitectónicos.

## Stack tecnológico

### Backend (directorio raíz)
- Laravel 11.47.0 + PHP
- Auth: Sanctum (token bearer, NO cookies/stateful)
- Base de datos: MySQL
- ORM: Eloquent
- Media: Cloudinary (cloudinary_php ^3.1) + fallback local
- Pagos: MercadoPago (dx-php ^3.8 backend, @mercadopago/sdk-react frontend)
- Testing: PHPUnit / Laravel test runner
- Rutas API: 143 endpoints, 50 controladores, 40 modelos

### Frontend activo (comercio-plus-frontend/)
- React 19.2.4 + TypeScript 5.9.3
- Vite 7.2.4
- Tailwind CSS 3.4.17
- Paleta principal: naranja #FF6A00 (comercioplus-600)
- Estilo: minimalista profesional, cards blancas, bordes suaves, CTA naranja

### Frontend legacy (resources/js/)
- Vue + Laravel Vite (conservado por compatibilidad, NO tocar sin consultar)

### Deploy
- Frontend: Vercel (comercio-plus-oficial)
- Backend: Railway (comercioplusoficial-production-d61e)
- Rewrites Vercel: /api, /sanctum, /storage → Railway

### Testing y QA
- Backend: `php artisan test` (123 tests, 407 assertions)
- Frontend lint: ESLint con max-warnings=0
- Frontend build: Vite build
- E2E: Playwright (chromium + mobile-chrome) en tests-e2e/
- Validador pre-codex: validate_before_codex.ps1 / .sh

## Estructura del proyecto
```
comercioPlusOficial/
├── app/                        # Backend Laravel
│   ├── Http/Controllers/       # 50 controladores API
│   ├── Models/                 # 40 modelos Eloquent
│   └── Services/               # Lógica de negocio
├── routes/
│   ├── api.php                 # 143 endpoints
│   ├── web.php
│   └── auth.php
├── config/                     # cors.php, sanctum.php, services.php
├── database/migrations/        # Migraciones MySQL
├── tests/                      # PHPUnit (Feature, Unit)
├── tests-e2e/                  # Playwright smoke
├── comercio-plus-frontend/     # Frontend React (ACTIVO)
│   ├── src/
│   │   ├── app/                # Páginas y App.tsx (router)
│   │   ├── components/         # Componentes React
│   │   │   └── erp/            # Componentes ERP (ErpBtn, ErpBadge, ErpKpiCard)
│   │   ├── hooks/              # Custom hooks
│   │   ├── lib/                # API client, utils
│   │   ├── types/              # Tipos TypeScript (api.ts)
│   │   └── contexts/           # CartContext y otros
│   ├── vercel.json             # Rewrites a Railway
│   └── vite.config.ts
├── resources/js/               # Frontend Vue LEGACY (no tocar)
├── docs/                       # Documentación canónica
│   ├── UNIVERSAL_COMERCIOPLUS.md  # Fuente única de verdad
│   ├── DOC_GOVERNANCE.md
│   └── QA_RELEASE_REPORT.md
├── playwright.config.ts
├── vite.config.js              # Root apunta a React
└── vite.legacy.config.js       # Build legacy Vue
```

## Roles y rutas protegidas

### Merchant (RequireAuth + RequireRole('merchant'))
Todas las rutas /dashboard/*:
- /dashboard (KPIs)
- /dashboard/store (config tienda)
- /dashboard/products (CRUD productos)
- /dashboard/categories (CRUD categorías)
- /dashboard/inventory (resumen, ajustes)
- /dashboard/inventory/import (CSV/XLSX)
- /dashboard/inventory/receive (scanner)
- /dashboard/inventory/restock (auto-restock)
- /dashboard/orders (pedidos)
- /dashboard/orders/:id/picking (alistamiento)
- /dashboard/credit (fiado digital)
- /dashboard/reports (ventas, impuestos, top, inventario)
- /dashboard/settings (configuración)

### Client (público + auth para checkout)
- / (landing)
- /stores (listado tiendas)
- /store/:id (detalle tienda)
- /cart, /checkout, /checkout/success
- /orders/history (historial pedidos)

## Convenciones de código

### Backend Laravel
- Controladores: PascalCase singular → ProductController
- Modelos: PascalCase singular → Product, Order, Store
- Migraciones: snake_case → create_products_table
- Endpoints API: kebab-case → /api/merchant/credit-accounts
- Responses JSON: { success: bool, data: T, message?: string }
- Auth: siempre middleware auth:sanctum en rutas protegidas
- Validación: Form Requests para POST/PUT

### Frontend React
- Componentes: PascalCase → ProductCard.tsx
- Hooks: camelCase con prefijo use → useCart()
- Tipos: PascalCase en types/api.ts
- API client: usar @/lib/api (nunca fetch directo)
- Estilos: SOLO Tailwind CSS, paleta comercioplus-*
- Componentes ERP: usar ErpBtn, ErpBadge, ErpKpiCard del sistema
- Layouts: PublicLayout, AuthLayout, DashboardLayout

### TypeScript
- strict: true (no any, no @ts-ignore)
- Tipos de retorno explícitos en funciones de API
- Interfaces para props de componentes

## Prohibiciones
- ❌ No tocar el frontend Vue legacy sin consultar.
- ❌ No cambiar config de CORS sin verificar en producción.
- ❌ No documentar como implementado algo que es solo plan.
- ❌ No crear archivos .md duplicados (ver DOC_GOVERNANCE).
- ❌ No usar any en TypeScript.
- ❌ No instalar dependencias sin justificación.
- ❌ No mezclar lógica de Merchant y Client en mismos componentes.
- ❌ No usar console.log en código de producción.
- ❌ No hacer push directo a master sin tests pasando.

## Limitaciones conocidas (NO intentar arreglar sin consultar)
- /products y /products/:id usan mocks (no API real).
- Config IVA detallada solo tiene toggle general en UI.
- Búsqueda pública de barcode sin UI (endpoint existe).
- barcode/search retorna 404 en producción (drift conocido).

## Flujo de trabajo
1. git fetch origin --prune
2. git pull --ff-only origin master
3. Hacer cambios
4. php artisan test (backend)
5. npm run lint --prefix comercio-plus-frontend
6. npm run build --prefix comercio-plus-frontend
7. git push origin master
8. Verificar Vercel + Railway post-deploy

## Estilo de comunicación
- Explica el POR QUÉ de cada decisión.
- Separa siempre IMPLEMENTADO vs PLAN/PROPUESTA.
- Si detectas un bug no relacionado, repórtalo sin arreglarlo.
- Antes de refactorizar, pregunta.
- Cuando actualices funcionalidad, actualiza también UNIVERSAL_COMERCIOPLUS.md.
