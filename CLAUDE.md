# CLAUDE.md — ComercioPlus
# Plataforma e-commerce de repuestos de motos (Colombia)

## Visión del Producto
- **70% usuarios son comerciantes INFORMALES** (talleres familiares, sin DIAN)
- **30% son comerciantes FORMALES** (con NIT, necesitan facturación electrónica)
- **Principio #1:** Simplicidad primero — vender en 5 minutos
- **Principio #2:** DIAN es OPCIONAL — OFF por defecto, nunca obligar
- **Principio #3:** Enfoque motos — búsqueda por marca/modelo/año es CORE

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
- Laravel 11.47.0 + PHP 8.3
- Auth: Sanctum (token bearer, NO cookies/stateful)
- Base de datos: MySQL (61 tablas)
- ORM: Eloquent (46 modelos)
- Media: Cloudinary (cloudinary_php ^3.1) + fallback local
- Pagos: MercadoPago (dx-php ^3.8 backend, @mercadopago/sdk-react frontend)
- PDF: barryvdh/laravel-dompdf (recibos simples)
- Testing: PHPUnit (222 tests, 670 assertions)
- Rutas API: 150+ endpoints, 50+ controladores

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
- Backend: `php artisan test` (222 tests, 670 assertions — ALL PASSING)
- Frontend lint: ESLint con max-warnings=0
- Frontend build: Vite build (31 páginas)
- E2E: Playwright (chromium + mobile-chrome) en tests-e2e/

## Módulos Implementados (19)

### Core (siempre activos)
1. Auth (Sanctum bearer tokens)
2. Stores (CRUD, verificación, settings, dian_enabled toggle)
3. Products (CRUD, códigos, alertas stock, compatibilidad motos)
4. Categories (CRUD por tienda)
5. Orders (CRUD, estados, historial)
6. Cart (add/remove/clear, conteo)
7. Customers (registro, visitas)
8. Payments (MercadoPago)
9. Inventory (movimientos, ajustes, import CSV/XLSX)
10. Auto-Restock (predicción, sugerencias)
11. Credit/Fiado (cuentas, cargos, pagos)
12. Reports (ventas, impuestos, top productos, inventario, tendencias)
13. Picking (escaneo, alistamiento pedidos)
14. Barcode (generación, búsqueda)
15. Settings (perfil, tienda, impuestos)
16. Live Metrics (dashboard KPIs)

### Nuevos (2026-03-29)
17. **Recibos Simples** — PDF "COMPROBANTE DE VENTA" para informales (NO es factura)
18. **Búsqueda por Moto** — 60 motos colombianas, filtros brand/model/year
19. **Facturación DIAN** — OPCIONAL, protegido por middleware RequiresDianEnabled

## Arquitectura DIAN (Opcional)
- `stores.dian_enabled` (boolean, default false) controla acceso
- Middleware `RequiresDianEnabled` protege `/merchant/invoicing/*`
- `DianConfigController` permite enable/disable sin tocar código
- Servicios: `app/Services/ElectronicInvoicing/` (XmlGenerator, CufeCalculator, MatiasApiClient)
- Tipos: FEV (factura), NCE (nota crédito), NDE (nota débito)
- Estados: draft → pending → approved/rejected, cancelled
- Mock server: `php -S localhost:8080 mock-dian-server/server.php`

## Búsqueda por Moto
- Tabla `motorcycle_models` (60 motos: Yamaha, Honda, Suzuki, Bajaj, Kawasaki, AKT, TVS, Hero, KTM)
- Pivot `product_motorcycle_compatibility` vincula productos con motos
- Endpoints públicos: `/motorcycles`, `/motorcycles/brands`, `/motorcycles/models`
- Filtros en PublicProductController: `?motorcycle_brand=&motorcycle_model=&motorcycle_year=`

## Estructura del proyecto
```
comercioPlusOficial/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── Merchant/              # DianConfigController, SimpleReceiptController, etc.
│   │   ├── MotorcycleController   # Catálogo motos (público)
│   │   └── PublicProductController # Productos + filtros moto
│   ├── Models/                    # 46 modelos (Store, Product, MotorcycleModel, SimpleReceipt, etc.)
│   ├── Services/
│   │   ├── ElectronicInvoicing/   # XmlGenerator, CufeCalculator, MatiasApiClient
│   │   └── SimpleReceiptPdfService.php
│   └── Http/Middleware/
│       └── RequiresDianEnabled.php
├── routes/api.php                 # 150+ endpoints
├── database/
│   ├── migrations/                # 61 tablas
│   └── seeders/MotorcycleModelSeeder.php
├── resources/views/pdf/           # simple-receipt.blade.php
├── mock-dian-server/              # Mock DIAN para testing local
├── comercio-plus-frontend/        # Frontend React (ACTIVO)
│   └── src/app/dashboard/settings/dian/page.tsx
├── tests/                         # 222 tests PHPUnit
└── docs/                          # Documentación canónica
```

## Roles y rutas protegidas

### Merchant (RequireAuth + RequireRole('merchant'))
- /dashboard (KPIs)
- /dashboard/store, /products, /categories
- /dashboard/inventory (resumen, ajustes, import, receive, restock)
- /dashboard/orders, /orders/:id/picking
- /dashboard/credit (fiado digital)
- /dashboard/reports
- /dashboard/settings
- /dashboard/settings/dian (toggle DIAN ON/OFF)

### Client (público + auth para checkout)
- /, /stores, /store/:id
- /cart, /checkout, /checkout/success
- /orders/history

## Convenciones de código

### Backend Laravel
- Controladores: PascalCase singular → ProductController
- Modelos: PascalCase singular → Product, Order, Store
- Migraciones: snake_case → create_products_table
- Endpoints API: kebab-case → /api/merchant/credit-accounts
- Responses JSON: { success: bool, data: T, message?: string }
- Auth: siempre middleware auth:sanctum en rutas protegidas
- User→Store: relación hasOne (`$user->store`, NO `$user->stores()`)

### Frontend React
- Componentes: PascalCase → ProductCard.tsx
- Hooks: camelCase con prefijo use → useCart()
- Tipos: PascalCase en types/api.ts
- API client: usar @/lib/api (nunca fetch directo), getApiPayload() para extraer datos
- Estilos: SOLO Tailwind CSS, paleta comercioplus-*
- Componentes ERP: ErpBtn, ErpBadge, ErpKpiCard, ErpPageHeader, ErpFilterSelect
- Layouts: PublicLayout, AuthLayout, DashboardLayout

### TypeScript
- strict: true (no any, no @ts-ignore)
- Tipos de retorno explícitos en funciones de API
- Interfaces para props de componentes

## Prohibiciones
- No tocar el frontend Vue legacy sin consultar
- No cambiar config de CORS sin verificar en producción
- No documentar como implementado algo que es solo plan
- No crear archivos .md duplicados (ver DOC_GOVERNANCE)
- No usar any en TypeScript
- No instalar dependencias sin justificación
- No mezclar lógica de Merchant y Client en mismos componentes
- No usar console.log en código de producción
- No hacer push directo a master sin tests pasando
- No llamar "factura" a un comprobante simple (implicaciones legales)
- No obligar DIAN a comerciantes informales

## Limitaciones conocidas (NO intentar arreglar sin consultar)
- /products y /products/:id usan mocks (no API real)
- Config IVA detallada solo tiene toggle general en UI
- barcode/search retorna 404 en producción (drift conocido)
- MatiasApiClient apunta a mock server local (no hay proveedor DIAN real aún)
- Frontend búsqueda por moto no tiene UI aún (solo API)

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
- Explica el POR QUÉ de cada decisión
- Separa siempre IMPLEMENTADO vs PLAN/PROPUESTA
- Si detectas un bug no relacionado, repórtalo sin arreglarlo
- Antes de refactorizar, pregunta
- Cuando actualices funcionalidad, actualiza también UNIVERSAL_COMERCIOPLUS.md
- Lenguaje simple — el usuario principal es un mecánico, no un ingeniero
