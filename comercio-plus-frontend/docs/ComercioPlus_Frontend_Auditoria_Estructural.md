<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# ComercioPlus Frontend - Auditoria Estructural

Fecha: 2026-02-25
Alcance: Auditoria tecnica real del frontend React + Vite + Tailwind basada en el repositorio actual.

## Resumen
- Entry real: `src/app/main.tsx`
- Router real: `src/app/App.tsx` con `react-router-dom`
- Layouts: `PublicLayout`, `AuthLayout`, `DashboardLayout`
- Auth: token en `sessionStorage/localStorage`, Bearer en Axios interceptor
- UI: coexistencia de componentes legacy (`src/components/*`) y UI nueva (`src/components/ui/*`)
- Deuda visual principal: colores hardcoded e inline styles (especialmente `Navbar.tsx`)

## Hallazgos de arquitectura
1. Estructura hibrida `src/app/*` + `src/pages/*`.
2. Sistema de auth sin `AuthContext`, gestionado por utilidades (`services/auth-session.ts`).
3. Dashboard protegido por `RequireAuth` + `RequireRole`.
4. Dos archivos `tailwind.config.js` en el monorepo.
5. No existe componente global reutilizable para Toast/Modal/Table.

## Riesgos tecnicos
- Inconsistencia visual por duplicacion de capas UI.
- Mayor costo de mantenimiento por ruteo y componentes en dos capas.
- Regresiones de UX si se rediseña sin consolidar tokens y primitives.

## Recomendacion previa al rediseño
- Definir una unica base UI canónica.
- Unificar contrato de rutas y layouts.
- Reducir hardcoded/inlines en navegacion.
- Consolidar primitives globales (Modal/Toast/Table).

## Ubicacion de documentacion vigente
- Contrato tecnico vigente: `ComercioPlus_Frontend_Contrato_Tecnico.md`
- Resumen de auditoria vigente: este documento
- Historial documental (referencia, no canonico): `docs/repo-md/`

