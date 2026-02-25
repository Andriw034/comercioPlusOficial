<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# TODO_DESIGN_FRONTEND_ANALYSIS

Fecha: 2026-02-25  
Alcance: Auditoria real de diseno frontend (React + Vite + Tailwind), basada en codigo del repo.

---

## 1) Estado actual del sistema visual (real)

### 1.1 Base de estilos y tema
- `comercio-plus-frontend/src/app/globals.css:1` importa tipografias (`Plus Jakarta Sans`, `Space Grotesk`).
- `comercio-plus-frontend/src/app/globals.css:22-42` define fondo global con gradientes en light/dark.
- `comercio-plus-frontend/src/app/globals.css:87-157` define utilidades de UI base (`input-dark`, `dashboard-section`, etc.).
- `comercio-plus-frontend/src/providers/theme-provider.tsx:12-45` maneja tema con `localStorage` (`cp-theme`) y clase `dark` en `html`.

### 1.2 Tokens de Tailwind
- `comercio-plus-frontend/tailwind.config.js:7-149` tiene paleta principal, tipografias, sombras, animaciones y plugins.
- Existe otro config en raiz `tailwind.config.js:1-53`. Riesgo de divergencia si no se documenta cual es el oficial para el frontend React.

### 1.3 Componentes base
- Boton base: `comercio-plus-frontend/src/components/ui/button.tsx:3-29`.
- Input base: `comercio-plus-frontend/src/components/ui/Input.tsx:39-77`.
- Card base: `comercio-plus-frontend/src/components/ui/GlassCard.tsx:10-17`.

Conclusion: hay una base visual util, pero hay mezcla de patrones (tokens + hardcoded hex + inline style) que impide consistencia completa.

---

## 2) Hallazgos criticos de diseno/UX (con evidencia)

## 2.1 Inconsistencia de layout publico
- `PublicLayout` solo renderiza `Navbar` y `Footer` en home:
  - `comercio-plus-frontend/src/components/layouts/PublicLayout.tsx:8-15`
- Impacto: el usuario pierde navegacion principal en `/stores`, `/products`, `/cart`, `/checkout`.

## 2.2 Navbar fuera de sistema de diseno
- `comercio-plus-frontend/src/components/Navbar.tsx:94-556` usa estilo inline masivo y colores hardcoded.
- `handleLogout` no llama backend, solo limpia frontend:
  - `comercio-plus-frontend/src/components/Navbar.tsx:88-92`
- Impacto: inconsistencia visual, deuda tecnica y logout no unificado.

## 2.3 Responsividad dashboard limitada en mobile
- Sidebar fijo de 205px:
  - `comercio-plus-frontend/src/components/dashboard/Sidebar.tsx:132`
- Layout desktop fijo sin colapso/sidebar drawer:
  - `comercio-plus-frontend/src/components/layouts/DashboardLayout.tsx:107-113`
- Impacto: en pantallas pequenas queda poco ancho util y puede verse "apretado".

## 2.4 Tipografia y escalas no uniformes
- Dashboard home usa escala relativamente controlada:
  - `comercio-plus-frontend/src/app/dashboard/page.tsx:437`, `:501`
- Productos usa titulos muy grandes en mobile (`42px`) sin breakpoint de reduccion:
  - `comercio-plus-frontend/src/app/dashboard/products/page.tsx:772`
- Impacto: riesgo de cortes visuales y sensacion no armonica entre modulos.

## 2.5 Estilos hardcoded dentro de componentes base
- `Input` mezcla clase utilitaria con color inline:
  - `comercio-plus-frontend/src/components/ui/Input.tsx:71`
- `button` usa multiples hex directos:
  - `comercio-plus-frontend/src/components/ui/button.tsx:5-13`
- `GlassCard` fija gradiente y color en el componente:
  - `comercio-plus-frontend/src/components/ui/GlassCard.tsx:12-15`
- Impacto: dificil cambiar tema o branding global sin tocar muchos archivos.

## 2.6 Feedback UX no profesional en puntos publicos
- Footer newsletter sigue con `alert()`:
  - `comercio-plus-frontend/src/components/Footer.tsx:16`
- Login muestra "Olvidaste contrasena?" como texto sin link:
  - `comercio-plus-frontend/src/app/login/page.tsx:118`
- Impacto: sensacion de demo en flujos visibles.

## 2.7 Accesibilidad y focus
- Hay buen inicio en botones con `focus-visible`:
  - `comercio-plus-frontend/src/components/ui/button.tsx:19`
- Pero en `Navbar` al usar inline styles no hay patron consistente de focus ring accesible.

---

## 3) Diagnostico de coherencia visual (senior frontend + producto)

Fortalezas:
- Paleta naranja definida y utilidades dark/light.
- Varias vistas recientes del dashboard ya muestran lenguaje visual premium (gradientes, cards, sombras).
- Existe estructura reutilizable (`AppShell`, `GlassCard`, `button`, `Input`).

Debilidades:
- Sistema visual partido en 3 capas:
1. tokens Tailwind
2. clases utilitarias locales
3. estilo inline hardcoded (principalmente `Navbar`)
- No hay contrato visual unico para navegacion publica/dashboard.
- Mobile de dashboard necesita arquitectura (drawer + topbar), no solo retoques de clases.

---

## 4) Propuesta TO-BE de diseno (drop-in, compatible)

## 4.1 Arquitectura visual recomendada
1. Definir una sola fuente de tokens:
- Colores de marca, neutros, estados, radius, sombras y espaciados en Tailwind + variables CSS.
2. Convertir `Navbar` a Tailwind + componentes UI:
- Sin inline styles para layout/colores.
3. Mantener `PublicLayout` con header/footer consistentes en todas las rutas publicas (excepto casos puntuales como checkout si se decide).
4. Implementar `DashboardTopbar` + `SidebarDrawer` en mobile:
- Sidebar fijo en desktop, drawer colapsable en mobile.

## 4.2 Contrato de componentes (frontend design system)
- `ui/Button`: variantes semanticas (`primary`, `secondary`, `ghost`, `danger`) sin hex directos.
- `ui/Input`: label/hint/error alineados al tema, sin `style={{...}}`.
- `ui/Card`: `Card`, `CardHeader`, `CardBody`, `CardFooter` para evitar repetir gradientes manuales.
- `navigation/AppNavbar`: links, buscador, menu usuario, logout unificado.
- `navigation/Breadcrumbs` y `navigation/BackButton` para vistas profundas.

## 4.3 Regla de contenido y tipografia
- Escala global:
1. H1: 28-32 desktop / 24-28 mobile
2. H2: 22-26 desktop / 20-22 mobile
3. Body: 14-16
- En modulos dashboard evitar `text-[42px]` en mobile sin variante `sm/md`.

---

## 5) Plan de implementacion visual por PR (sin romper backend)

| PR | Objetivo | Impacto | Riesgo | Archivos principales |
|---|---|---|---|---|
| PR-D1 | Unificar layout publico (Navbar/Footer en rutas publicas) | Alto | Bajo | `src/components/layouts/PublicLayout.tsx` |
| PR-D2 | Refactor `Navbar` a Tailwind (sin inline styles) + focus accesible | Alto | Medio | `src/components/Navbar.tsx` |
| PR-D3 | Dashboard responsive real (drawer mobile + topbar) | Alto | Medio | `src/components/layouts/DashboardLayout.tsx`, `src/components/dashboard/Sidebar.tsx` |
| PR-D4 | Normalizar componentes base (`Button`, `Input`, `GlassCard`) con tokens | Alto | Bajo | `src/components/ui/button.tsx`, `src/components/ui/Input.tsx`, `src/components/ui/GlassCard.tsx` |
| PR-D5 | UX polish publico: `ForgotPassword` link, reemplazar `alert()` por toast | Medio | Bajo | `src/app/login/page.tsx`, `src/components/Footer.tsx` |
| PR-D6 | Ajuste de escala tipografica y overflow en dashboard productos | Medio | Bajo | `src/app/dashboard/products/page.tsx` |

Orden recomendado: PR-D1 -> PR-D2 -> PR-D5 -> PR-D3 -> PR-D4 -> PR-D6

---

## 6) Checklist de QA visual para cierre

1. Public pages mantienen navbar/footer consistente.
2. No hay texto que se salga de cards en 360px y 390px de ancho.
3. Sidebar no reduce el contenido en mobile; usa drawer.
4. Todos los botones y links tienen estado focus visible.
5. Logout visualmente consistente y disponible en desktop/mobile.
6. No quedan `alert()` en flujo publico.
7. Dark/light con contraste correcto (texto sobre fondo >= AA).
8. No hay colores hex repetidos fuera de tokens (o quedan documentados).
9. Build pasa sin warnings criticos de estilos.
10. Vercel y local renderizan igual (mismo commit + mismas env frontend).

---

## 7) Resumen ejecutivo de diseno

El frontend ya tiene base premium en dashboard e identidad naranja clara, pero aun no opera como sistema de diseno unificado por tres causas: `PublicLayout` inconsistente, `Navbar` inline-heavy y responsividad incompleta del dashboard.  
Con los PR-D1 a PR-D6 se puede cerrar el gap visual sin refactor masivo ni cambios de contrato backend.


