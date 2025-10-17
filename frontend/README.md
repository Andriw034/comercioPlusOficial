# ComercioPlus Frontend (Next.js)

Esta es la aplicación frontend de ComercioPlus, construida con Next.js, TypeScript, Tailwind CSS y shadcn/ui.

## Características

- **Next.js 14** con App Router
- **TypeScript** para tipado fuerte
- **Tailwind CSS** para estilos
- **shadcn/ui** para componentes UI
- **Firebase** para autenticación y base de datos
- **React Hook Form** para formularios
- **Zod** para validación
- **Playwright** para pruebas E2E

## Cómo ejecutar

1. Instalar dependencias:

```bash
npm install
```

2. Configurar variables de entorno:

Copia `.env.example` a `.env.local` y configura las variables necesarias (Firebase, API backend, etc.).

3. Ejecutar servidor de desarrollo:

```bash
npm run dev
```

4. Abrir en navegador: `http://localhost:3000`

## Scripts disponibles

- `npm run dev` - Servidor de desarrollo
- `npm run build` - Construir para producción
- `npm run start` - Ejecutar en producción
- `npm run lint` - Ejecutar ESLint
- `npm run test` - Ejecutar pruebas
- `npm run test:e2e` - Ejecutar pruebas E2E con Playwright

## Estructura del proyecto

```
frontend/
├── src/
│   ├── app/                 # Páginas y layouts (App Router)
│   ├── components/          # Componentes React
│   │   ├── ui/              # Componentes shadcn/ui
│   │   └── dashboard/       # Componentes del dashboard
│   ├── lib/                 # Utilidades y servicios
│   │   ├── services/        # Servicios API
│   │   ├── schemas/         # Esquemas Zod
│   │   └── contexts/        # Contextos React
│   └── hooks/               # Hooks personalizados
├── tests/                   # Pruebas E2E
├── next.config.mjs          # Configuración Next.js
├── tailwind.config.js       # Configuración Tailwind
└── package.json
```

## Integración con Laravel

Esta aplicación frontend se comunica con el backend Laravel a través de APIs REST. Asegúrate de que el backend esté ejecutándose en `http://localhost:8000` (o configura la URL en las variables de entorno).

## Notas

- La aplicación usa Firebase para autenticación, pero también puede integrarse con el sistema de auth de Laravel.
- Los estilos están basados en Tailwind CSS con un tema oscuro por defecto.
- Las pruebas E2E usan Playwright y están configuradas para ejecutar en modo headless.
