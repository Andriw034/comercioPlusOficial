# ComercioPlus Frontend - Auditoria Comprensiva

## Resumen Ejecutivo
- Se detecto una causa raiz del fallo dark/light: Tailwind no tenia `darkMode: 'class'` en `tailwind.config.js`.
- El tema se gestionaba desde cada `ThemeToggle`, creando estados locales duplicados y desincronizados entre layouts.
- Habia reglas globales con `!important` en `globals.css` que forzaban colores y aumentaban riesgo de contraste inconsistente.
- El sistema de botones estaba fragmentado entre clases globales (`.btn-*`) y uso directo de `<button>` con clases sueltas.
- El dropdown de categorias no tenia estado visible de carga/error, lo que en runtime parecia "sin opciones" sin explicacion para el usuario.
- El filtro de categorias vivia en un contenedor sticky con z-index bajo para overlays complejos.
- Se encontro un riesgo real de Mixed Content: URLs absolutas `http://127.0.0.1` se devolvian tal cual y se intentaban renderizar en Vercel HTTPS.
- La base de componentes UI existe (`Button`, `Input`, `Select`, `Card`) pero necesitaba unificar tokens y manejo de estados.

## Hallazgos

| Severidad | Hallazgo | Evidencia | Por que pasa | Recomendacion |
|---|---|---|---|---|
| Critico | Modo oscuro no fiable por config Tailwind incompleta | `tailwind.config.js` sin `darkMode` (estado inicial) | Sin `darkMode: 'class'`, las clases `dark:*` dependen del media query y no del toggle manual | Configurar `darkMode: 'class'` y usar una sola fuente de verdad del tema |
| Alto | Tema duplicado por componente (`ThemeToggle`) | `src/components/theme/ThemeToggle.tsx` (estado local + `localStorage` + `classList.toggle`) | Cada toggle montado mantiene su propio estado; iconos y UI pueden quedar desincronizados | Mover logica a `ThemeProvider` + `useTheme` y dejar `ThemeToggle` solo como vista |
| Alto | Contraste inconsistente por reglas globales agresivas | `src/app/globals.css:174`, `src/app/globals.css:187`, `src/app/globals.css:196` | Reglas `!important` fuerzan colores y pisan estilos de componentes en escenarios no previstos | Reducir overrides globales y centralizar contraste en componentes base |
| Medio | Botoneria fragmentada | `src/components/ui/button.tsx` + uso de `btn-*` en vistas (estado inicial) | Mezcla de clases globales y clases directas dificulta consistencia visual dark/light | Definir variantes completas en `Button` (`primary/secondary/outline/ghost/danger`) y reutilizar `buttonVariants` |
| Medio | Dropdown de categorias sin feedback de carga/error | `src/app/products/page.tsx:98`, `src/app/dashboard/products/page.tsx:50` | Si falla `/categories`, solo hay `console.error`; usuario ve dropdown vacio | Agregar `categoriesLoading` y `categoriesError` con opciones fallback visibles |
| Bajo | Riesgo de apilado visual en bloque de filtros | `src/app/products/page.tsx:180` (sticky + z-index) | El bloque sticky podia competir con otros overlays/composiciones del layout | Subir z-index del filtro y z-index del `Select` |
| Alto | Mixed Content por URLs locales absolutas | `src/lib/format.ts:19`, `src/lib/format.ts:24` | Si backend devuelve `http://127.0.0.1/...`, navegador HTTPS lo bloquea | Normalizar URLs localhost hacia `API_ORIGIN` cuando exista |

## Evidencia Detallada

- Tema:
  - `src/components/theme/ThemeToggle.tsx` tenia lectura/escritura de `localStorage` y aplicacion de clase `dark` local al componente.
  - `src/components/layouts/PublicLayout.tsx:109` y `src/components/layouts/PublicLayout.tsx:133` montaban toggles independientes.
  - `src/components/layouts/DashboardLayout.tsx:110` y auth pages tambien montaban `ThemeToggle`.
- Contraste global:
  - `src/app/globals.css:174`, `src/app/globals.css:187`, `src/app/globals.css:196` usan `!important` en reglas de color.
- Dropdown categorias:
  - `src/app/products/page.tsx:98` y `src/app/dashboard/products/page.tsx:50` hacian fetch sin estado visual de error/carga.
- Media URLs:
  - `src/lib/format.ts` trataba URLs absolutas HTTP como validas y las devolvia sin normalizacion.

## Recomendaciones Priorizadas
1. Mantener `ThemeProvider` como unica fuente de verdad para dark/light.
2. Evitar reglas globales con `!important` para botones/enlaces; preferir variantes tipadas en `Button`.
3. Mostrar estados de carga/error en dropdowns conectados a API.
4. Normalizar URLs de media para bloquear de raiz Mixed Content en produccion.
5. Mantener z-index consistente en filtros y elementos desplegables para evitar conflictos visuales.
