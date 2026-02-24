# Handoff para Claude - Diseno de vistas de Picking

## Contexto
Este proyecto ya tiene la logica funcional de picking implementada (backend + frontend).
La tarea de Claude en esta fase es exclusivamente visual/UX sobre vistas de dashboard, sin cambiar reglas de negocio ni contratos API.

## Stack y estructura actual
- Frontend: React + Vite + TypeScript + Tailwind.
- Rutas relevantes:
  - `comercio-plus-frontend/src/app/App.tsx`
  - `/dashboard/orders`
  - `/dashboard/orders/:id/picking`
- Paginas clave:
  - `comercio-plus-frontend/src/app/dashboard/orders/page.tsx`
  - `comercio-plus-frontend/src/app/dashboard/orders/picking/page.tsx`
- Servicio API de picking:
  - `comercio-plus-frontend/src/services/picking.ts`

## Restricciones estrictas para Claude
1. No cambiar endpoints ni payloads del API.
2. No mover logica de negocio al backend ni inventar nuevos estados.
3. No renombrar campos esperados por backend.
4. No eliminar fallback de 3 fallos.
5. No romper comportamiento de accesos desde `/dashboard/orders`.
6. Mantener compatibilidad responsive escritorio/movil.

## Contrato funcional que no se debe romper
- Contexto:
  - `GET /api/merchant/orders/{order}/picking`
- Escaneo:
  - `POST /api/merchant/orders/{order}/picking/scan`
- Manual:
  - `POST /api/merchant/orders/{order}/picking/manual`
- Fallback:
  - `POST /api/merchant/orders/{order}/picking/fallback`
- Completar:
  - `POST /api/merchant/orders/{order}/picking/complete`
- Reset:
  - `POST /api/merchant/orders/{order}/picking/reset`

Codigos de error importantes para UX:
- `CODE_NOT_FOUND`
- `CODE_NOT_IN_ORDER`
- `ITEM_ALREADY_COMPLETE`
- `QTY_EXCEEDED`
- `SCAN_INVALID_STATE`
- `FALLBACK_REQUIRED`
- `PICKING_INCOMPLETE`

## Estados UX obligatorios en la vista de picking
1. `loading` de contexto.
2. `error` de carga con accion de reintento.
3. vista normal con progreso y lineas.
4. feedback de exito/error de acciones.
5. fallback visible al tercer fallo (bloqueante de flujo scanner).
6. vacio de lineas cuando no hay items.
7. botones de `complete` y `reset` visibles.

## Controles funcionales que deben mantenerse
1. Escaner:
  - input de codigo
  - cantidad
  - envio por Enter y boton
2. Manual por codigo:
  - code + qty
3. Manual por linea:
  - accion `+1` o equivalente
4. Marcar faltante:
  - linea + qty + reason
5. Nota:
  - linea + note
6. Acciones globales:
  - activar fallback manual
  - completar
  - reiniciar
  - volver a pedidos

## Integracion visual con el proyecto existente
- Mantener lenguaje visual del dashboard actual:
  - cards con borde suave
  - tonos neutrales con acentos de estado
  - tipografia y espaciado consistente con otras paginas de `dashboard`.
- No introducir librerias nuevas de UI sin aprobacion.
- Reusar estilos y componentes existentes cuando sea posible.

## Reglas de accesibilidad UX
1. Contraste minimo legible en textos de estado.
2. Estados de foco visibles para inputs/botones.
3. Mensajes de error claros y accionables.
4. Botones deshabilitados cuando hay `busy`.
5. Evitar interacciones ambiguas en movil.

## Riesgos que Claude debe evitar
1. Ocultar o minimizar mensajes de error criticos (`FALLBACK_REQUIRED`).
2. Diseños que oculten acciones manuales en pantallas pequenas.
3. Cambios en nombres de props que rompan TypeScript.
4. Introducir animaciones que bloqueen rapidez operativa.

## Checklist de aceptacion para el diseno
1. Compila: `npm run build` en `comercio-plus-frontend`.
2. Navegacion:
  - desde pedidos se llega a picking.
  - se puede volver a pedidos.
3. Escaner:
  - input enfocado y rapido.
  - errores visibles.
4. Fallback:
  - al tercer fallo se muestra flujo manual.
5. Manual:
  - por codigo, por linea, faltante y nota operan visualmente bien.
6. Responsive:
  - usable en 360px de ancho.
7. Sin cambios de contrato API.

## Prompt listo para Claude
```txt
Trabaja como disenador/desarrollador frontend senior sobre este proyecto React + Tailwind.
Tu objetivo es mejorar el diseno UX de la vista de picking sin tocar la logica de negocio ni contratos API.

Archivos foco:
- src/app/dashboard/orders/picking/page.tsx
- src/app/dashboard/orders/page.tsx
- src/app/App.tsx (solo si necesitas ajustar wiring visual menor)

No puedes cambiar:
- endpoints API
- payloads
- estados de negocio
- comportamiento fallback a 3 fallos

Debes entregar:
1) UI pulida para picking (scanner + manual + fallback)
2) consistencia visual con dashboard existente
3) responsive mobile/desktop
4) estados claros: loading/error/empty/busy/success
5) build pasando (`npm run build`)

Al finalizar reporta:
- archivos tocados
- decisiones visuales
- comprobacion de que no cambiaste contratos API
```

