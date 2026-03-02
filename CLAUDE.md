# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ComercioPlus is a Colombian e-commerce/ERP platform. The repository is a **monorepo** with:
- **Backend:** Laravel 11 (PHP 8.2+) at the repo root
- **Frontend:** React 19 + Vite + TypeScript in `comercio-plus-frontend/`
- **Legacy frontend:** Vue 3 in `resources/js/` (do not modify)

**Deployment:** Frontend → Vercel, Backend → Railway (MySQL). Production backend: `https://comercioplusoficial-production.up.railway.app`.

---

## Commands

### Frontend (`cd comercio-plus-frontend` first)

```bash
npm run dev          # Vite dev server (proxies /api to VITE_DEV_PROXY_TARGET)
npm run build        # tsc --noEmit + vite build
npm run lint         # ESLint strict (--max-warnings=0)
npm run preview      # Preview production build
```

### Backend (repo root)

```bash
php artisan serve              # Dev server on :8000
php artisan migrate            # Run pending migrations
php artisan migrate:status     # Check migration state
php artisan test               # PHPUnit tests
```

### Validation script (repo root)

```bash
.\validate_before_codex.ps1 frontend   # TypeScript + ESLint + ERP exports + routes
.\validate_before_codex.ps1 backend    # Migrations + models + API routes
.\validate_before_codex.ps1            # Both
```

**Always run the frontend validator before committing frontend changes.** It checks TSC errors, ESLint, required ERP barrel exports, and router completeness.

---

## Architecture

### Frontend routing & layouts

- `src/app/App.tsx` — central router (React Router v6, lazy-loaded pages with Suspense)
- Three layout wrappers: `AuthLayout`, `DashboardLayout`, `PublicLayout`
- Protected routes use `RequireAuth` + `RequireRole` HOCs
- Page files live in `src/app/dashboard/<section>/page.tsx`

### API client

Single Axios instance: `src/services/api.ts` (re-exported as `@/lib/api`).

```typescript
import API from '@/lib/api'
```

Features: Bearer token auth (Sanctum), 45-second GET cache, auto-logout on 401. Base URL from `VITE_API_BASE_URL`; dev proxy to `VITE_DEV_PROXY_TARGET` (`http://127.0.0.1:8000`).

### ERP component system

Dashboard pages use a shared barrel at `src/components/erp/index.ts`. **Never import ERP components directly — always use the barrel:**

```typescript
import { ErpBadge, ErpBtn, ErpKpiCard, ErpPageHeader, ErpSearchBar, ErpFilterSelect } from '@/components/erp'
```

`ErpTable` is **not exported** and must not be added to the barrel.

### TypeScript types

All API response shapes live in `src/types/api.ts`. Use `customer.user.name` pattern — never `customer_name` as a flat field.

### Backend authentication

Laravel Sanctum (token-based, stateless). `POST /api/login` and `POST /api/register` return `{ token, user }`. All protected routes use `auth:sanctum` middleware. Roles managed via Spatie Permission: `merchant`, `client`, `admin`.

### Backend structure

```
app/
├── Http/Controllers/Api/    # REST controllers (flat + Merchant/ subdirectory)
├── Http/Middleware/         # Auth, CORS, role checks, store guards
├── Http/Requests/           # Form validation (CreatePurchaseRequestRequest, etc.)
├── Models/                  # 35+ Eloquent models (Store, Order, Product, Customer, …)
└── Services/                # Business logic (CloudinaryService, ReportService, OrderBillingService)
routes/api.php               # All API routes
```

Key model relationships: `User → Store → Products → Orders → OrderProducts`. `CreditAccount` tracks fiado (store credit). `InventoryMovement` tracks stock in/out. `ProductAlert` handles price/stock alert subscriptions.

### API response conventions

- Paginated: `{ data: [], current_page, last_page, total }`
- Single resource: `{ data: { … } }`
- Errors: standard HTTP status codes with JSON body

### Payments

Wompi (Colombian gateway). Flow: `POST /api/payments/wompi/create` → webhook at `POST /api/payments/wompi/webhook` updates order status. PSE banks via `GET /api/payments/wompi/pse-banks`.

### Media uploads

`CloudinaryService` for production. Falls back to `storage/app/public` if Cloudinary is not configured. Upload endpoints: `POST /api/uploads/products`, `/api/uploads/stores/logo`, etc.

---

## Key conventions

### Frontend

- Path alias `@/` → `src/`
- Tailwind utility classes only — no CSS modules. Dark mode via `class` strategy.
- Custom color palette `comercioplus` (orange-based) defined in `tailwind.config.js`.
- State via React Context + custom hooks (`src/hooks/`). No Redux/Zustand.
- All `VITE_*` env vars. Runtime config in `src/lib/runtime.ts`.
- No BOM character at the top of `.tsx` files.

### Backend

- Migration filenames: `YYYY_MM_DD_NNNNNN_description.php`. Q2 features use `2026_02_27_1xxxxx_*`.
- Financial amounts: `decimal:2` casts, stored as MySQL DECIMAL.
- Order `channel` enum includes: `local`, `online` (and others). Order `fulfillment_status` enum: `pending_pick → picking → picked → packed → ready → delivered | cancelled`.
- Merchant-specific controllers go in `app/Http/Controllers/Api/Merchant/`.
