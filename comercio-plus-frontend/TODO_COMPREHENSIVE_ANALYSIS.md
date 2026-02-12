# TODO Comprehensive Analysis - ComercioPlus Frontend

## Resumen Ejecutivo
- El sistema de tema ya usa `darkMode: 'class'` y persistencia en `localStorage`.
- Habia inconsistencias de contraste en labels/hints de formularios y selects.
- El select de categorias dependia de estilos mezclados (`select-dark` + clases inline), causando diferencias visuales.
- Se centralizaron tokens de seccion para homogeneizar Dashboard Productos y Dashboard Store.
- Se reforzaron estilos de opciones de `select` en dark/light para evitar opciones invisibles.
- No se modifico logica de negocio, rutas ni llamadas API.

## Hallazgos (con evidencia)

| Severidad | Hallazgo | Evidencia | Recomendacion |
|---|---|---|---|
| Alto | Riesgo de baja legibilidad en labels/hints entre modos por tonos no unificados | `src/components/ui/Input.tsx:45`, `src/components/ui/Select.tsx:26` | Unificar labels/hints a `text-slate-800` en claro y `text-slate-200/300` en oscuro. |
| Alto | Select con estilos duplicados (token + overrides inline) podia producir contraste irregular | `src/components/ui/Select.tsx:15` | Mantener una sola fuente de estilo (`select-dark native-select`). |
| Alto | Dropdown nativo podia perder contraste de opciones en dark en algunos navegadores | `src/app/globals.css:204`, `src/app/globals.css:212` | Forzar `option/optgroup` con colores por modo. |
| Medio | Inconsistencia de jerarquia visual entre Productos y Store en dashboard | `src/app/dashboard/products/page.tsx:293`, `src/app/dashboard/store/page.tsx:166` | Introducir tokens compartidos de seccion (`dashboard-section`, title, subtitle). |
| Bajo | Tema global correcto pero se debia confirmar fuente unica de verdad | `tailwind.config.js:3`, `src/providers/theme-provider.tsx:28`, `src/providers/theme-provider.tsx:36` | Mantener `darkMode: class` + toggle en `<html>` + persistencia localStorage. |

## Cambios Aplicados
- `src/app/globals.css`
  - Se reforzo `select-dark` en dark.
  - Se agregaron tokens: `dashboard-section`, `dashboard-section-title`, `dashboard-section-subtitle`.
  - Se reforzo contraste de `option/optgroup` en dark/light.
- `src/components/ui/Input.tsx`
  - Labels/hints actualizados para contraste consistente en ambos modos.
- `src/components/ui/Select.tsx`
  - Se eliminaron overrides inline redundantes, usando estilos base centralizados.
  - Labels/hints actualizados para contraste consistente.
- `src/app/dashboard/products/page.tsx`
  - Secciones visuales con jerarquia consistente (`Catalogo actual` + `Editor`).
- `src/app/dashboard/store/page.tsx`
  - Secciones migradas al token compartido `dashboard-section`.

## Validacion
- `npm run lint`: OK
- `npm run build`: OK

## Checklist visual
- [x] Modo claro: texto principal oscuro legible en toda la app.
- [x] Modo oscuro: texto principal claro legible en toda la app.
- [x] Inputs y placeholders visibles en ambos modos.
- [x] Select de categorias visible, legible y clickeable en ambos modos.
- [x] Dashboard Productos y Dashboard Store con espaciado y jerarquia coherentes.
