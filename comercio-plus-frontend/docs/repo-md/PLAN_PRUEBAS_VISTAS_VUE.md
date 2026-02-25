<!-- DOC_STATUS:START -->
> Estado documental: **HISTORICO (archivo legado)**
> Fecha de verificacion: **2026-02-25**
> Nota: este archivo puede contener stack/paths antiguos (incluyendo Vue/Inertia o rutas previas).
> Referencia vigente: `../ComercioPlus_Frontend_Contrato_Tecnico.md` y `../ComercioPlus_Frontend_Auditoria_Estructural.md`.
<!-- DOC_STATUS:END -->

# Plan de Pruebas Exhaustivas - Vistas Vue.js

## ConfiguraciÃ³n del Entorno de Pruebas

### Dependencias Identificadas
- **Vue 3** con Composition API
- **Inertia.js** para integraciÃ³n Laravel-Vue
- **Pinia** para manejo de estado
- **Vue Router** para navegaciÃ³n
- **Tailwind CSS** para estilos
- **Playwright** para pruebas E2E
- **Vitest** para pruebas unitarias de componentes

## Vistas Identificadas para Probar

### 1. Vista Welcome (`resources/js/Pages/Welcome.vue`)
**Funcionalidades a probar:**
- [ ] Renderizado correcto del tÃ­tulo y descripciÃ³n
- [ ] Enlaces de navegaciÃ³n funcionan correctamente
- [ ] DiseÃ±o responsive en diferentes tamaÃ±os de pantalla
- [ ] Iconos SVG se muestran correctamente
- [ ] Tarjetas de caracterÃ­sticas se renderizan
- [ ] Gradiente de fondo se aplica correctamente

### 2. Vista Dashboard (`resources/js/Pages/Dashboard/Index.vue`)
**Funcionalidades a probar:**
- [ ] Carga de estadÃ­sticas desde el store
- [ ] Renderizado de tarjetas de estadÃ­sticas
- [ ] Formateo correcto de nÃºmeros y moneda
- [ ] Enlaces de acciones rÃ¡pidas funcionan
- [ ] IntegraciÃ³n con useAuthStore
- [ ] ActualizaciÃ³n reactiva de datos

### 3. Vista Stores (`resources/js/Pages/Stores/Index.vue`)
**Funcionalidades a probar:**
- [ ] Listado de tiendas
- [ ] Filtros y bÃºsqueda
- [ ] PaginaciÃ³n
- [ ] NavegaciÃ³n a tienda individual

## Stores de Pinia a Probar

### 1. Auth Store (`resources/js/stores/auth.ts`)
**Funcionalidades a probar:**
- [ ] Estado inicial correcto
- [ ] FunciÃ³n login con credenciales vÃ¡lidas
- [ ] FunciÃ³n login con credenciales invÃ¡lidas
- [ ] FunciÃ³n logout
- [ ] FunciÃ³n register
- [ ] Computed isAuthenticated
- [ ] Persistencia de estado

### 2. Products Store (`resources/js/stores/products.ts`)
**Funcionalidades a probar:**
- [ ] Carga de productos desde API
- [ ] Filtrado de productos
- [ ] BÃºsqueda de productos
- [ ] GestiÃ³n de estado de carga
- [ ] Manejo de errores

### 3. Cart Store (`resources/js/stores/cart.ts`)
**Funcionalidades a probar:**
- [ ] Agregar productos al carrito
- [ ] Actualizar cantidades
- [ ] Eliminar productos
- [ ] CÃ¡lculo de totales
- [ ] Persistencia local

## Componentes UI a Probar

### 1. Button Component (`resources/js/components/ui/Button.vue`)
**Funcionalidades a probar:**
- [ ] Renderizado con diferentes variantes
- [ ] Manejo de eventos click
- [ ] Estados disabled
- [ ] Slots de contenido

## Pruebas de IntegraciÃ³n

### 1. Flujo de AutenticaciÃ³n
- [ ] Registro de usuario nuevo
- [ ] Login con usuario existente
- [ ] NavegaciÃ³n despuÃ©s del login
- [ ] Logout y redirecciÃ³n

### 2. Flujo de Compra
- [ ] NavegaciÃ³n desde Welcome a productos
- [ ] Agregar productos al carrito
- [ ] Proceso de checkout
- [ ] ConfirmaciÃ³n de orden

### 3. Flujo de Dashboard
- [ ] Acceso al dashboard autenticado
- [ ] NavegaciÃ³n entre secciones
- [ ] GestiÃ³n de productos
- [ ] GestiÃ³n de tiendas

## ConfiguraciÃ³n de Pruebas

### Pruebas Unitarias con Vitest
```javascript
// vitest.config.ts
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    globals: true
  }
})
```

### Pruebas E2E con Playwright
```javascript
// playwright.config.ts
import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './tests-e2e',
  use: {
    baseURL: 'http://localhost:8000',
    headless: true
  }
})
```

## Archivos de Prueba a Crear

### Pruebas Unitarias de Componentes
- [ ] `tests/unit/components/Welcome.test.js`
- [ ] `tests/unit/components/Dashboard.test.js`
- [ ] `tests/unit/components/Stores.test.js`
- [ ] `tests/unit/components/ui/Button.test.js`

### Pruebas de Stores
- [ ] `tests/unit/stores/auth.test.js`
- [ ] `tests/unit/stores/products.test.js`
- [ ] `tests/unit/stores/cart.test.js`

### Pruebas E2E
- [ ] `tests-e2e/auth-flow.spec.ts`
- [ ] `tests-e2e/shopping-flow.spec.ts`
- [ ] `tests-e2e/dashboard-flow.spec.ts`
- [ ] `tests-e2e/responsive-design.spec.ts`

## Casos de Prueba EspecÃ­ficos

### Pruebas de Responsividad
- [ ] Vista mÃ³vil (320px - 768px)
- [ ] Vista tablet (768px - 1024px)
- [ ] Vista desktop (1024px+)
- [ ] NavegaciÃ³n mÃ³vil
- [ ] MenÃºs colapsables

### Pruebas de Accesibilidad
- [ ] NavegaciÃ³n por teclado
- [ ] Lectores de pantalla
- [ ] Contraste de colores
- [ ] Etiquetas ARIA
- [ ] Foco visible

### Pruebas de Performance
- [ ] Tiempo de carga inicial
- [ ] Lazy loading de componentes
- [ ] OptimizaciÃ³n de imÃ¡genes
- [ ] Bundle size

## Comandos de EjecuciÃ³n

### Desarrollo
```bash
# Instalar dependencias
npm install

# Ejecutar servidor de desarrollo
npm run dev

# Ejecutar Laravel
php artisan serve
```

### Pruebas
```bash
# Pruebas unitarias
npm run test

# Pruebas E2E
npm run test:e2e

# Linting
npm run lint
```

## MÃ©tricas de Ã‰xito

### Cobertura de CÃ³digo
- [ ] >90% cobertura en componentes crÃ­ticos
- [ ] >80% cobertura en stores
- [ ] 100% cobertura en utilidades

### Performance
- [ ] First Contentful Paint < 2s
- [ ] Largest Contentful Paint < 3s
- [ ] Cumulative Layout Shift < 0.1

### Accesibilidad
- [ ] Score WCAG AA
- [ ] Lighthouse Accessibility > 95

## Herramientas de Testing

### Configuradas
- [x] Vitest para pruebas unitarias
- [x] Playwright para pruebas E2E
- [x] Vue Test Utils para testing de componentes
- [x] Testing Library para interacciones

### Por Configurar
- [ ] Storybook para documentaciÃ³n de componentes
- [ ] Chromatic para pruebas visuales
- [ ] Axe para pruebas de accesibilidad automatizadas

## Cronograma de ImplementaciÃ³n

### Fase 1: ConfiguraciÃ³n (1 dÃ­a)
- [ ] Configurar entorno de pruebas
- [ ] Crear estructura de archivos
- [ ] Configurar CI/CD

### Fase 2: Pruebas Unitarias (2 dÃ­as)
- [ ] Pruebas de componentes
- [ ] Pruebas de stores
- [ ] Pruebas de utilidades

### Fase 3: Pruebas de IntegraciÃ³n (2 dÃ­as)
- [ ] Flujos de usuario
- [ ] IntegraciÃ³n con API
- [ ] Estados de error

### Fase 4: Pruebas E2E (2 dÃ­as)
- [ ] Flujos completos
- [ ] Pruebas cross-browser
- [ ] Pruebas de performance

### Fase 5: OptimizaciÃ³n (1 dÃ­a)
- [ ] Refactoring de pruebas
- [ ] DocumentaciÃ³n
- [ ] AutomatizaciÃ³n

