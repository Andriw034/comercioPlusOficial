<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# INTEGRADO_ANALISIS_PLAN_QA

Fecha de consolidacion: 2026-02-25
Base: analisis, planes y reportes QA historicos de `docs/repo-md`.

## 1) Estado real consolidado
- Monorepo mixto: backend Laravel en raiz + frontend React/Vite en `comercio-plus-frontend` + legado en `resources`.
- Router frontend real en `src/app/App.tsx`.
- Auth frontend por token Bearer en `sessionStorage/localStorage` con interceptor Axios.
- Dashboard protegido por `RequireAuth` + `RequireRole`.
- Documentacion tecnica vigente consolidada en:
  - `ComercioPlus_Frontend_Contrato_Tecnico.md`
  - `ComercioPlus_Frontend_Auditoria_Estructural.md`

## 2) Hallazgos estructurales recurrentes
1. Deuda visual por hardcoded e inline styles (especialmente Navbar y partes del dashboard).
2. Duplicacion de capas UI (`src/components/*` legacy y `src/components/ui/*`).
3. Configuracion sensible y documentos historicos dispersos.
4. Mezcla de rutas/paginas entre `src/app/*` y `src/pages/*`.

## 3) Avances ya reportados como implementados
1. Flujo cliente sin friccion: gate de auth en checkout y no al entrar a tienda.
2. Mejoras de contraste/imagenes: utilidades de brillo, `LogoImage`, `CoverImage`, soporte cloudinary transforms.
3. Unificacion parcial de layout publico y logout backend/frontend.
4. Base de iconografia configurable (`fa|emoji`) sobre `Icon`.

## 4) Estado QA consolidado
- Reportes historicos marcan cobertura alta en API core (auth, tienda, productos, inventario, pedidos).
- Evidencia de ejecuciones exitosas en:
  - `php artisan test` (segun reportes de fecha 2026-02).
  - build frontend (`npm run build`) en contextos puntuales.
- Riesgo residual historico: regresiones visuales/responsive no uniformes en dashboard y navegacion.

## 5) Riesgos vigentes para produccion
1. Inconsistencia visual por coexistencia de patrones antiguos y nuevos.
2. Dependencia de variables de entorno correctas (API base, cloudinary, despliegue).
3. Documentacion historica extensa con instrucciones parcialmente obsoletas.

## 6) Prioridades operativas unificadas
1. Consolidar source of truth de UI (tokens + componentes canonicos).
2. Cerrar responsividad dashboard (drawer mobile/sidebar behavior).
3. Reducir hardcoded colors/inline styles en navegacion y vistas core.
4. Mantener flujo checkout con redirect seguro y carrito persistente.

## 7) Fuente canonica de este integrado
Documentos activos (vigentes):
- `ComercioPlus_Frontend_Contrato_Tecnico.md`
- `ComercioPlus_Frontend_Auditoria_Estructural.md`

Documentos historicos usados como fuente:
- `ANALISIS_COMPLETO.md`
- `TODO_COMPREHENSIVE_ANALYSIS.md`
- `EXECUTION_PLAN_SENIOR.md`
- `QA_REPORT_COMERCIOPLUS.md`
- `QA_E2E_REPORT.md`
- `QA_REPORT_AUTOMATICO_FULLFLOW.md`
- `INFORME_GENERAL_PROYECTO.md`
- `INFORME_COMPLETO_APLICACION.md`

