<!-- DOC_STATUS:START -->
> Estado documental: **ACTIVO**
> Fecha de verificacion: **2026-02-25**
> Fuente canonica frontend: `ComercioPlus_Frontend_Contrato_Tecnico.md`.
<!-- DOC_STATUS:END -->

# ComercioPlus Frontend - Contrato Tecnico

Fecha: 2026-02-25
Repositorio auditado: `comercio-plus-frontend` (React + Vite + Tailwind)
Metodo: evidencia directa de archivos (`tree`, `Get-Content`, `rg`).

## 0) Alcance y verificacion
- Este documento describe el estado real actual del frontend.
- No incluye rediseno ni refactor.
- Si algo no existe en codigo, se marca explicitamente como `no existe`.

## 1) Mapa completo de rutas

### 1.1 Router y libreria
- Router: `react-router-dom`.
- Definicion de rutas: `src/app/App.tsx`.
- Version declarada (`package.json`): `^7.2.0`.
- Version instalada (package-lock.json): `7.13.0`.

### 1.2 Tabla completa de rutas
| Path | Componente | Layout | Protegida | Rol | Archivo real |
|---|---|---|---|---|---|
| / | HomePage | PublicLayout | No | - | src/pages/Home.tsx |
| /stores | Stores | PublicLayout | No | - | src/app/stores/page.tsx |
| /store/create | CreateStore | PublicLayout | No | - | src/app/store/create/page.tsx |
| /store/:id | StoreDetail | PublicLayout | No | - | src/app/store/page.tsx |
| /stores/:storeSlug/products | StoreProducts | PublicLayout | No | - | src/pages/StoreProducts.tsx |
| /how-it-works | HowItWorks | PublicLayout | No | - | src/pages/HowItWorks.tsx |
| /products | Products | PublicLayout | No | - | src/pages/Products.tsx |
| /products/:id | ProductDetail | PublicLayout | No | - | src/pages/ProductDetail.tsx |
| /product/:id | ProductDetail | PublicLayout | No | - | src/pages/ProductDetail.tsx |
| /cart | Cart | PublicLayout | No | - | src/pages/Cart.tsx |
| /checkout | Checkout | PublicLayout | No (gating en componente) | - | src/pages/Checkout.tsx |
| /checkout/success | CheckoutSuccess | PublicLayout | No | - | src/pages/CheckoutSuccess.tsx |
| /payment/success | CheckoutSuccess | PublicLayout | No | - | src/pages/CheckoutSuccess.tsx |
| /orders/:id | CheckoutSuccess | PublicLayout | No | - | src/pages/CheckoutSuccess.tsx |
| /category/:id | Category | PublicLayout | No | - | src/app/category/page.tsx |
| /privacy | Privacy | PublicLayout | No | - | src/app/privacy/page.tsx |
| /terms | Terms | PublicLayout | No | - | src/app/terms/page.tsx |
| /about | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /team | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /careers | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /blog | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /press | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /help | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /contact | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /faq | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /status | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /report | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /cookies | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /returns | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /warranty | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /sitemap | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /accessibility | SimpleContentPage | PublicLayout | No | - | src/pages/SimpleContentPage.tsx |
| /crear-tienda | Navigate -> /store/create | PublicLayout | No | - | src/app/App.tsx |
| /login | Login | AuthLayout | No | - | src/app/login/page.tsx |
| /register | Register | AuthLayout | No | - | src/app/register/page.tsx |
| /registro | Navigate -> /register | AuthLayout | No | - | src/app/App.tsx |
| /forgot-password | ForgotPassword | AuthLayout | No | - | src/pages/ForgotPassword.tsx |
| /dashboard | Dashboard | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/page.tsx |
| /dashboard/customers | DashboardCustomers | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/customers/page.tsx |
| /dashboard/store | DashboardStore | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/pages/DashboardStore.tsx |
| /dashboard/settings | DashboardSettingsPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/settings/page.tsx |
| /dashboard/orders | DashboardOrdersPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/orders/page.tsx |
| /dashboard/orders/:id/picking | OrderPickingPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/orders/picking/page.tsx |
| /dashboard/inventory | DashboardInventoryPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/inventory/page.tsx |
| /dashboard/inventory/receive | InventoryReceivePage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/inventory/receive/page.tsx |
| /dashboard/reports | DashboardReportsPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/reports/page.tsx |
| /dashboard/categories | DashboardCategoriesPage | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/categories/page.tsx |
| /dashboard/products | ManageProducts | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/products/page.tsx |
| /dashboard/products/create | ManageProducts | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/products/page.tsx |
| /dashboard/products/:id/edit | ManageProducts | DashboardLayout + RequireAuth + RequireRole | Si | merchant | src/app/dashboard/products/page.tsx |

### 1.3 Snippet completo de rutas (`src/app/App.tsx`)
```tsx
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import RequireAuth from '@/components/auth/RequireAuth'
import RequireRole from '@/components/auth/RequireRole'
import AuthLayout from '@/components/layouts/AuthLayout'
import DashboardLayout from '@/components/layouts/DashboardLayout'
import PublicLayout from '@/components/layouts/PublicLayout'

import Category from './category/page'
import CreateStore from './store/create/page'
import Dashboard from './dashboard/page'
import DashboardCategoriesPage from './dashboard/categories/page'
import DashboardCustomers from './dashboard/customers/page'
import DashboardInventoryPage from './dashboard/inventory/page'
import InventoryReceivePage from './dashboard/inventory/receive/page'
import Login from './login/page'
import ManageProducts from './dashboard/products/page'
import DashboardReportsPage from './dashboard/reports/page'
import DashboardSettingsPage from './dashboard/settings/page'
import DashboardOrdersPage from './dashboard/orders/page'
import OrderPickingPage from './dashboard/orders/picking/page'
import Register from './register/page'
import StoreDetail from './store/page'
import Stores from './stores/page'
import Privacy from './privacy/page'
import Terms from './terms/page'
import Products from '@/pages/Products'
import ProductDetail from '@/pages/ProductDetail'
import HomePage from '@/pages/Home'
import DashboardStore from '@/pages/DashboardStore'
import HowItWorks from '@/pages/HowItWorks'
import StoreProducts from '@/pages/StoreProducts'
import Cart from '@/pages/Cart'
import Checkout from '@/pages/Checkout'
import CheckoutSuccess from '@/pages/CheckoutSuccess'
import SimpleContentPage from '@/pages/SimpleContentPage'
import ForgotPassword from '@/pages/ForgotPassword'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<PublicLayout />}>
          <Route path="/" element={<HomePage />} />
          <Route path="/stores" element={<Stores />} />

          {/* importante: "create" antes que ":id" */}
          <Route path="/store/create" element={<CreateStore />} />

          <Route path="/store/:id" element={<StoreDetail />} />
          <Route path="/stores/:storeSlug/products" element={<StoreProducts />} />
          <Route path="/how-it-works" element={<HowItWorks />} />
          <Route path="/products" element={<Products />} />
          <Route path="/products/:id" element={<ProductDetail />} />
          <Route path="/product/:id" element={<ProductDetail />} />
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/checkout/success" element={<CheckoutSuccess />} />
          <Route path="/payment/success" element={<CheckoutSuccess />} />
          <Route path="/orders/:id" element={<CheckoutSuccess />} />
          <Route path="/category/:id" element={<Category />} />
          <Route path="/privacy" element={<Privacy />} />
          <Route path="/terms" element={<Terms />} />
          <Route
            path="/about"
            element={<SimpleContentPage title="Quienes somos" description="Conoce el equipo y la vision de ComercioPlus para impulsar comercios digitales en Colombia." />}
          />
          <Route
            path="/team"
            element={<SimpleContentPage title="Nuestro equipo" description="Estas en la pagina del equipo. Aqui podras publicar perfiles, roles y experiencia de cada area." />}
          />
          <Route
            path="/careers"
            element={<SimpleContentPage title="Carreras" description="Encuentra oportunidades para trabajar con nosotros y construir producto de alto impacto." />}
          />
          <Route
            path="/blog"
            element={<SimpleContentPage title="Blog" description="Publicaciones, guias y novedades para vendedores y compradores dentro de la plataforma." />}
          />
          <Route
            path="/press"
            element={<SimpleContentPage title="Prensa" description="Recursos de marca y comunicados oficiales para medios y aliados." />}
          />
          <Route
            path="/help"
            element={<SimpleContentPage title="Centro de ayuda" description="Documentacion y respuestas rapidas para resolver dudas frecuentes de uso." />}
          />
          <Route
            path="/contact"
            element={<SimpleContentPage title="Contacto" description="Canales de contacto para soporte comercial y tecnico." />}
          />
          <Route
            path="/faq"
            element={<SimpleContentPage title="Preguntas frecuentes" description="Consultas comunes sobre cuentas, pagos, tiendas y pedidos." />}
          />
          <Route
            path="/status"
            element={<SimpleContentPage title="Estado del servicio" description="Consulta el estado operativo de la plataforma y sus componentes principales." />}
          />
          <Route
            path="/report"
            element={<SimpleContentPage title="Reportar problema" description="Registra incidentes para que el equipo tecnico pueda revisarlos y dar seguimiento." />}
          />
          <Route
            path="/cookies"
            element={<SimpleContentPage title="Politica de cookies" description="Explicacion sobre el uso de cookies y como gestionar tus preferencias." />}
          />
          <Route
            path="/returns"
            element={<SimpleContentPage title="Politica de devoluciones" description="Condiciones generales para devoluciones y solicitudes asociadas." />}
          />
          <Route
            path="/warranty"
            element={<SimpleContentPage title="Garantia" description="Terminos generales de garantia para productos y procesos relacionados." />}
          />
          <Route
            path="/sitemap"
            element={<SimpleContentPage title="Mapa del sitio" description="Indice de secciones principales para facilitar la navegacion." />}
          />
          <Route
            path="/accessibility"
            element={<SimpleContentPage title="Accesibilidad" description="Compromiso y mejoras continuas para una experiencia accesible para todos." />}
          />
          <Route path="/crear-tienda" element={<Navigate to="/store/create" replace />} />
        </Route>

        <Route element={<AuthLayout />}>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/registro" element={<Navigate to="/register" replace />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
        </Route>

        <Route
          element={
            <RequireAuth>
              <RequireRole role="merchant">
                <DashboardLayout />
              </RequireRole>
            </RequireAuth>
          }
        >
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/dashboard/customers" element={<DashboardCustomers />} />
          <Route path="/dashboard/store" element={<DashboardStore />} />
          <Route path="/dashboard/settings" element={<DashboardSettingsPage />} />
          <Route path="/dashboard/orders" element={<DashboardOrdersPage />} />
          <Route path="/dashboard/orders/:id/picking" element={<OrderPickingPage />} />
          <Route path="/dashboard/inventory" element={<DashboardInventoryPage />} />
          <Route path="/dashboard/inventory/receive" element={<InventoryReceivePage />} />
          <Route path="/dashboard/reports" element={<DashboardReportsPage />} />
          <Route path="/dashboard/categories" element={<DashboardCategoriesPage />} />
          <Route path="/dashboard/products" element={<ManageProducts />} />
          <Route path="/dashboard/products/create" element={<ManageProducts />} />
          <Route path="/dashboard/products/:id/edit" element={<ManageProducts />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
```

## 2) Entry y bootstrap

### 2.1 Entry real
- Entry real: `src/app/main.tsx`.
- El Router se monta dentro de `<App />` (que contiene `<BrowserRouter>`).
- `StrictMode`: si, activo.

### 2.2 `main.tsx` completo
```tsx
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './globals.css'
import App from './App'
import ThemeProvider from '@/providers/theme-provider'
import { CartProvider } from '@/context/CartContext'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ThemeProvider>
      <CartProvider>
        <App />
      </CartProvider>
    </ThemeProvider>
  </StrictMode>,
)
```

## 3) Layout contract

### 3.1 PublicLayout
- Archivo: `src/components/layouts/PublicLayout.tsx`
- Renderiza: `Navbar` + `Footer` + `Outlet` (via `AppShell`).
- Uso en router: wrapper de rutas publicas.
```tsx
import { Outlet, useLocation } from 'react-router-dom'
import Navbar from '@/components/Navbar'
import Footer from '@/components/Footer'
import AppShell from './AppShell'

export default function PublicLayout() {
  const location = useLocation()
  const isHomeRoute = location.pathname === '/'

  return (
    <AppShell
      header={<Navbar />}
      footer={<Footer />}
      containerClassName={isHomeRoute ? 'max-w-none' : ''}
      mainClassName={isHomeRoute ? '!px-0 !py-0' : ''}
    >
      <Outlet />
    </AppShell>
  )
}
```

### 3.2 AuthLayout
- Archivo: `src/components/layouts/AuthLayout.tsx`
- Renderiza: hero visual + `Outlet` (sin Navbar/Footer por variante `auth`).
- Uso en router: `/login`, `/register`, `/registro`, `/forgot-password`.
```tsx
import { useEffect, useState } from 'react'
import { Outlet } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import AppShell from './AppShell'

type HeroImage = {
  id: string
  urls: { regular: string }
  alt_description: string
}

const HERO_IMAGES: HeroImage[] = [
  {
    id: '1',
    urls: { regular: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1200&q=80' },
    alt_description: 'Motociclista en carretera',
  },
  {
    id: '2',
    urls: { regular: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1200&q=80' },
    alt_description: 'Moto deportiva',
  },
  {
    id: '3',
    urls: { regular: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1200&q=80' },
    alt_description: 'Motociclista en ruta',
  },
  {
    id: '4',
    urls: { regular: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1200&q=80' },
    alt_description: 'Moto custom',
  },
  {
    id: '5',
    urls: { regular: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1200&q=80' },
    alt_description: 'Moto clasica',
  },
]

export default function AuthLayout() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0)

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % HERO_IMAGES.length)
    }, 5000)

    return () => clearInterval(interval)
  }, [])

  return (
    <AppShell variant="auth" containerClassName="max-w-5xl" mainClassName="py-2 sm:py-3">
      <div className="max-h-[90vh] overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
        <div className="grid min-h-[530px] grid-cols-1 lg:grid-cols-2">
          <div className="relative hidden overflow-hidden bg-slate-900 lg:block">
            <AnimatePresence mode="sync" initial={false}>
              <motion.div
                key={currentImageIndex}
                initial={{ opacity: 0, scale: 1.1 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                transition={{ duration: 1.5, ease: 'easeInOut' }}
                className="absolute inset-0"
              >
                <img
                  src={HERO_IMAGES[currentImageIndex].urls.regular}
                  alt={HERO_IMAGES[currentImageIndex].alt_description}
                  className="h-full w-full object-cover"
                  loading="eager"
                  decoding="async"
                />
                <div className="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-slate-900/75 to-comercioplus-900/80" />
              </motion.div>
            </AnimatePresence>

            <div className="relative z-10 flex h-full flex-col justify-center p-8 xl:p-10">
              <div className="max-w-md">
                <h2 className="text-[44px] font-bold leading-tight text-white">Bienvenido a ComercioPlus</h2>
                <p className="mt-3 text-[16px] leading-[1.55] text-white/90">
                  Conecta tu negocio con miles de clientes. Gestiona tu tienda online de manera simple y efectiva.
                </p>

                <div className="mt-6 flex gap-2">
                  {HERO_IMAGES.map((_, index) => (
                    <button
                      key={index}
                      onClick={() => setCurrentImageIndex(index)}
                      className={`h-2 rounded-full transition-all duration-300 ${
                        index === currentImageIndex ? 'w-8 bg-comercioplus-500' : 'w-2 bg-white/30 hover:bg-white/50'
                      }`}
                      aria-label={`Ver imagen ${index + 1}`}
                    />
                  ))}
                </div>
              </div>
            </div>
          </div>

          <div className="flex items-center p-4 sm:p-5 lg:p-6">
            <div className="w-full">
              <Outlet />
            </div>
          </div>
        </div>
      </div>
    </AppShell>
  )
}
```

### 3.3 DashboardLayout
- Archivo: `src/components/layouts/DashboardLayout.tsx`
- Renderiza: `Sidebar` + `Outlet`.
- Uso en router: `/dashboard/*` bajo `RequireAuth` + `RequireRole(role="merchant")`.
```tsx
import { useEffect, useMemo, useState } from 'react'
import { Outlet } from 'react-router-dom'
import Sidebar from '@/components/dashboard/Sidebar'
import API from '@/lib/api'
import { resolveMediaUrl } from '@/lib/format'

interface Store {
  id: string | number
  name: string
  logo?: string
  cover?: string
}

const STORE_CACHE_KEY = 'store'

function safeParseStore(raw: string | null): any | null {
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

function mapStore(data: any): Store {
  return {
    id: data?.id || '',
    name: String(data?.name || '').trim() || 'Mi tienda',
    logo: resolveMediaUrl(data?.logo_url || data?.logo_path || data?.logo) || '',
    cover: resolveMediaUrl(data?.cover_url || data?.cover_path || data?.background_url || data?.cover) || '',
  }
}

export default function DashboardLayout() {
  const cachedRaw = useMemo(() => localStorage.getItem(STORE_CACHE_KEY), [])
  const cachedParsed = useMemo(() => safeParseStore(cachedRaw), [cachedRaw])

  const [store, setStore] = useState<Store | null>(() => (cachedParsed ? mapStore(cachedParsed) : null))
  const [isLoading, setIsLoading] = useState(() => !cachedParsed)

  useEffect(() => {
    let isMounted = true

    const loadStoreData = async () => {
      try {
        const { data } = await API.get('/my/store')
        const mapped = mapStore(data)
        if (!isMounted) return
        setStore(mapped)
        localStorage.setItem(STORE_CACHE_KEY, JSON.stringify(data || mapped))
      } catch (error: any) {
        if (!isMounted) return

        if (error?.response?.status === 404) {
          localStorage.removeItem(STORE_CACHE_KEY)
          setStore(null)
          return
        }

        if (!cachedParsed) {
          setStore(null)
        }
      } finally {
        if (isMounted) {
          setIsLoading(false)
        }
      }
    }

    loadStoreData()

    return () => {
      isMounted = false
    }
  }, [cachedParsed])

  useEffect(() => {
    const onStoreUpdated = (event: Event) => {
      const custom = event as CustomEvent<any>
      const detail = custom.detail
      if (!detail) return

      const mapped = mapStore(detail)
      setStore(mapped)
      localStorage.setItem(STORE_CACHE_KEY, JSON.stringify(detail))
    }

    window.addEventListener('store:updated', onStoreUpdated as EventListener)

    return () => {
      window.removeEventListener('store:updated', onStoreUpdated as EventListener)
    }
  }, [])

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center bg-slate-50 dark:bg-slate-950">
        <div className="text-center">
          <div className="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          <p className="text-body text-slate-600 dark:text-slate-300">Cargando...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="flex h-screen overflow-hidden bg-[#f0f2f7] text-slate-900 dark:bg-slate-950 dark:text-slate-100">
      <Sidebar store={store} />

      <main className="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 md:p-8">
        <Outlet context={{ store }} />
      </main>
    </div>
  )
}
```

### 3.4 AppShell (base comun)
- Archivo: `src/components/layouts/AppShell.tsx`
```tsx
import type { ReactNode } from 'react'

type Props = {
  children: ReactNode
  header?: ReactNode
  footer?: ReactNode
  variant?: 'public' | 'dashboard' | 'auth'
  containerClassName?: string
  mainClassName?: string
}

export default function AppShell({
  children,
  header,
  footer,
  variant = 'public',
  containerClassName = '',
  mainClassName = '',
}: Props) {
  const isAuth = variant === 'auth'
  const isDashboard = variant === 'dashboard'

  const baseBg = isDashboard ? 'bg-[#F3F4F6] dark:bg-slate-950' : 'bg-[#F9FAFB] dark:bg-slate-950'

  return (
    <div className={`relative flex min-h-screen flex-col overflow-x-hidden ${baseBg} text-slate-900 dark:text-slate-100`}>
      {header && !isAuth && <div className="relative z-20">{header}</div>}

      <main
        className={`relative z-10 ${
          isAuth ? 'flex-1 flex items-center justify-center px-4 py-8 sm:py-12' : 'flex-1 px-4 py-8'
        } ${mainClassName}`.trim()}
      >
        <div className={`mx-auto w-full ${isAuth ? 'max-w-6xl' : 'max-w-7xl'} ${containerClassName}`.trim()}>
          {children}
        </div>
      </main>

      {footer && !isAuth && <div className="relative z-10 mt-auto">{footer}</div>}
    </div>
  )
}
```

## 4) Autenticacion real

### 4.1 Archivo principal
- `src/services/auth-session.ts`.

### 4.2 `setToken` / `getToken` / `clearSession`
- `setToken`: no existe con ese nombre.
- `getToken`: no existe con ese nombre.
- Equivalentes reales: `hydrateSession(...)` y `getStoredToken()`.
- `clearSession()` si existe.

### 4.3 Storage y keys
- `TOKEN_KEY = 'token'`
- `USER_KEY = 'user'`
- Storage: `sessionStorage` + `localStorage`.

### 4.4 `auth-session.ts` completo
```ts
import API from '@/lib/api'

export type AuthUser = {
  id: number
  name: string
  email: string
  phone?: string | null
  role: 'merchant' | 'client' | 'admin' | string
  has_store?: boolean
  store_id?: number | null
}

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

function getSessionToken(): string | null {
  const token = sessionStorage.getItem(TOKEN_KEY)
  if (!token) return null
  return token.trim().length > 0 ? token : null
}

function getLocalToken(): string | null {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) return null
  return token.trim().length > 0 ? token : null
}

export function getStoredToken(): string | null {
  const sessionToken = getSessionToken()
  if (sessionToken) return sessionToken

  const localToken = getLocalToken()
  if (localToken) {
    sessionStorage.setItem(TOKEN_KEY, localToken)
    return localToken
  }

  return null
}

export function getStoredUserRaw(): string | null {
  const userRaw = sessionStorage.getItem(USER_KEY)
  if (userRaw) return userRaw

  const localUser = localStorage.getItem(USER_KEY)
  if (localUser) {
    sessionStorage.setItem(USER_KEY, localUser)
    return localUser
  }

  return null
}

export async function hydrateSession(token: string, persist = true): Promise<AuthUser> {
  sessionStorage.setItem(TOKEN_KEY, token)
  if (persist) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }
  API.defaults.headers.common.Authorization = `Bearer ${token}`

  const { data } = await API.get('/me')
  const user: AuthUser = data
  const serializedUser = JSON.stringify(user)
  sessionStorage.setItem(USER_KEY, serializedUser)
  if (persist) {
    localStorage.setItem(USER_KEY, serializedUser)
  }
  if (!user.has_store && !user.store_id) {
    localStorage.removeItem('store')
  }

  return user
}

export function clearSession(): void {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
  delete API.defaults.headers.common.Authorization
}

export function resolvePostAuthRoute(user: AuthUser): string {
  if (user.role === 'merchant') {
    if (!user.has_store && !user.store_id) {
      return '/dashboard/store'
    }
    return '/dashboard'
  }

  if (user.role === 'admin') {
    return '/dashboard'
  }

  return '/'
}
```

### 4.5 Axios instance + interceptor
- Archivo: `src/services/api.ts`.
- Agrega `Authorization: Bearer ...` en request interceptor.
- En `401` (fuera de `/login`, `/register`, `/me`) limpia sesion y redirige a `/login`.
```ts
import axios, { AxiosError, type AxiosRequestConfig, type AxiosResponse } from 'axios'
import { API_BASE_URL } from '@/lib/runtime'

if (!API_BASE_URL) {
  console.error('[api] Missing API base URL. Requests will fallback to /api.')
}

const TOKEN_KEY = 'token'
const USER_KEY = 'user'
const GET_CACHE_TTL_MS = 45_000

type CachedGetResponse = {
  expiresAt: number
  response: AxiosResponse
}

const getCache = new Map<string, CachedGetResponse>()

const clearStoredSession = () => {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
}

const clearGetCache = () => {
  getCache.clear()
}

const readToken = (): string | null => {
  const sessionToken = sessionStorage.getItem(TOKEN_KEY)
  if (sessionToken && sessionToken.trim().length > 0) return sessionToken

  const localToken = localStorage.getItem(TOKEN_KEY)
  if (localToken && localToken.trim().length > 0) {
    sessionStorage.setItem(TOKEN_KEY, localToken)
    return localToken
  }

  return null
}

const stableSerialize = (value: unknown): string => {
  if (value === null || value === undefined) return ''
  if (typeof value !== 'object') return String(value)
  if (Array.isArray(value)) return `[${value.map(stableSerialize).join(',')}]`

  const obj = value as Record<string, unknown>
  return `{${Object.keys(obj).sort().map((key) => `${key}:${stableSerialize(obj[key])}`).join('|')}}`
}

const buildGetCacheKey = (url: string, config?: AxiosRequestConfig): string => {
  const params = stableSerialize(config?.params)
  const token = readToken() || 'guest'
  return [String(API.defaults.baseURL || ''), url, params, token].join('|')
}

const API = axios.create({
  baseURL: API_BASE_URL || '/api',
  timeout: 30000,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: false,
})

if (import.meta.env.DEV) {
  console.info(`[api] baseURL: ${String(API.defaults.baseURL || '')}`)
}

API.interceptors.request.use(
  (config) => {
    config.withCredentials = false
    const method = (config.method || 'get').toLowerCase()

    const token = readToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    } else if (config.headers && 'Authorization' in config.headers) {
      delete config.headers.Authorization
    }

    if (method !== 'get') {
      clearGetCache()
    }

    return config
  },
  (error) => Promise.reject(error),
)

API.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    const status = error.response?.status
    const requestUrl = String(error.config?.url || '')
    const isAuthFlowRequest =
      requestUrl.includes('/login') ||
      requestUrl.includes('/register') ||
      requestUrl.includes('/me')

    if (status === 401 && !isAuthFlowRequest) {
      clearStoredSession()
      clearGetCache()
      delete API.defaults.headers.common.Authorization
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

const originalGet = API.get.bind(API)
API.get = (async function cachedGet<T = any, R = AxiosResponse<T>, D = any>(
  url: string,
  config?: AxiosRequestConfig<D>,
): Promise<R> {
  const key = buildGetCacheKey(url, config)
  const now = Date.now()
  const cached = getCache.get(key)

  if (cached && cached.expiresAt > now) {
    return cached.response as R
  }

  const response = await originalGet<T, AxiosResponse<T>, D>(url, config)

  if (response.status >= 200 && response.status < 300) {
    getCache.set(key, {
      expiresAt: now + GET_CACHE_TTL_MS,
      response: response as AxiosResponse,
    })
  }

  return response as R
}) as typeof API.get

export default API
```

### 4.6 Logout actual
- `/logout` se llama desde:
  - `src/components/Navbar.tsx`
  - `src/components/dashboard/Sidebar.tsx`
  - `src/app/dashboard/page.tsx`
- Logica duplicada de logout en varios puntos.

## 5) Tailwind source of truth

### 5.1 Configs detectados
- `tailwind.config.js` (raiz)
- `comercio-plus-frontend/tailwind.config.js` (frontend React)

### 5.2 Config usado por el frontend React
- El frontend usa `comercio-plus-frontend/tailwind.config.js` + su `postcss.config.js` local.

### 5.3 `comercio-plus-frontend/tailwind.config.js` completo
```js
/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        comercioplus: {
          DEFAULT: '#FF6A00',
          50: '#FFF5EC',
          100: '#FFEAD9',
          200: '#FFD5B8',
          300: '#FFC096',
          400: '#FFA169',
          500: '#FF823D',
          600: '#FF6A00',
          700: '#E65700',
          800: '#B34400',
          900: '#803100',
        },
        brand: {
          50: '#FFF3E0',
          100: '#FFE0B2',
          200: '#FFCC80',
          300: '#FFB74D',
          400: '#FFA726',
          500: '#FF9800',
          600: '#FB8C00',
          700: '#F57C00',
          800: '#EF6C00',
          900: '#E65100',
        },
        slate: {
          50: '#F8FAFC',
          100: '#F1F5F9',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
          800: '#1E293B',
          900: '#0F172A',
          950: '#020617',
        },
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        primary: {
          DEFAULT: '#FF9800',
          dark: '#F57C00',
          light: '#FFB74D',
        },
        secondary: {
          DEFAULT: '#1E293B',
          dark: '#0F172A',
          light: '#334155',
        },
        accent: {
          DEFAULT: '#FFA62B',
          dark: '#E69020',
          light: '#FFB84D',
        },
        dark: {
          DEFAULT: '#0F172A',
          50: '#F8FAFC',
          100: '#F1F5F9',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
          800: '#1E293B',
          900: '#0F172A',
        },
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
        display: ['Space Grotesk', 'Plus Jakarta Sans', 'sans-serif'],
      },
      fontSize: {
        'display-lg': ['72px', { lineHeight: '1', fontWeight: '700', letterSpacing: '-0.02em' }],
        'display-md': ['60px', { lineHeight: '1.1', fontWeight: '700', letterSpacing: '-0.02em' }],
        'display-sm': ['48px', { lineHeight: '1.1', fontWeight: '700', letterSpacing: '-0.01em' }],
        hero: ['56px', { lineHeight: '1.1', fontWeight: '700' }],
        h1: ['40px', { lineHeight: '1.2', fontWeight: '700' }],
        h2: ['32px', { lineHeight: '1.3', fontWeight: '600' }],
        h3: ['24px', { lineHeight: '1.4', fontWeight: '600' }],
        'body-lg': ['18px', { lineHeight: '1.6', fontWeight: '400' }],
        body: ['16px', { lineHeight: '1.6', fontWeight: '400' }],
        'body-sm': ['14px', { lineHeight: '1.5', fontWeight: '400' }],
        caption: ['13px', { lineHeight: '1.4', fontWeight: '500' }],
      },
      boxShadow: {
        premium: '0 4px 20px rgba(0, 0, 0, 0.08)',
        'premium-lg': '0 8px 32px rgba(0, 0, 0, 0.12)',
        'premium-xl': '0 12px 48px rgba(0, 0, 0, 0.16)',
        glow: '0 0 20px rgba(255, 152, 0, 0.4)',
        'glow-lg': '0 0 40px rgba(255, 152, 0, 0.6)',
        glass: '0 8px 32px 0 rgba(31, 38, 135, 0.15)',
        'inner-premium': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
        sm: '0 2px 8px rgba(0,0,0,0.05)',
        md: '0 4px 16px rgba(0,0,0,0.08)',
        lg: '0 8px 24px rgba(0,0,0,0.12)',
        xl: '0 12px 32px rgba(0,0,0,0.15)',
        primary: '0 8px 20px rgba(255, 152, 0, 0.3)',
      },
      backdropBlur: {
        xs: '2px',
      },
      animation: {
        'fade-in': 'fadeIn 0.6s ease-out forwards',
        'slide-up': 'slideUp 0.5s ease-out forwards',
        'scale-in': 'scaleIn 0.4s ease-out forwards',
        float: 'float 6s ease-in-out infinite',
        'glow-pulse': 'glowPulse 2s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(30px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        scaleIn: {
          '0%': { transform: 'scale(0.9)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-20px)' },
        },
        glowPulse: {
          '0%, 100%': { boxShadow: '0 0 20px rgba(255, 152, 0, 0.4)' },
          '50%': { boxShadow: '0 0 40px rgba(255, 152, 0, 0.8)' },
        },
      },
      transitionTimingFunction: {
        premium: 'cubic-bezier(0.4, 0, 0.2, 1)',
      },
      backgroundImage: {
        mesh: 'radial-gradient(at 40% 20%, hsla(28,100%,74%,0.3) 0px, transparent 50%), radial-gradient(at 80% 0%, hsla(189,100%,56%,0.2) 0px, transparent 50%), radial-gradient(at 0% 50%, hsla(355,100%,93%,0.2) 0px, transparent 50%)',
      },
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
}
```

### 5.4 `tailwind.config.js` raiz completo
```js
/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",

    // si este repo tambiÃ©n incluye Laravel Blade/Vue, lo dejamos:
    "./resources/**/*.blade.php",
    "./resources/**/*.{js,ts,vue}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },
      colors: {
        // COLORES OFICIALES COMERCIOPLUS (NARANJA PRINCIPAL)
        primary: '#FF6A00',

        // PALETA COMERCIOPLUS COMPLETA (para degradados suaves)
        comercioplus: {
          DEFAULT: '#FF6A00',
          50: '#FFF5EC',
          100: '#FFEAD9',
          200: '#FFD5B8',
          300: '#FFC096',
          400: '#FFA169',
          500: '#FF823D',
          600: '#FF6A00',
          700: '#E65700',
          800: '#B34400',
          900: '#803100',
        },

        // COLORES AUXILIARES DE SISTEMA
        'cp-bg': '#FDFDFD',
        'cp-surface': '#FFFFFF',
        'cp-text': '#1F2937',
        'cp-sub': '#6B7280',
      },
      borderRadius: {
        'lg-16': '16px',
        'xl-20': '20px',
        'xxl-24': '24px',
      },
      boxShadow: {
        'cp-card': '0px 4px 16px rgba(0, 0, 0, 0.05)',
      },
    },
  },
  plugins: [],
}
```

### 5.5 `postcss.config.js` frontend
```js
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

### 5.6 `globals.css` completo
```css
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap');

@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  * {
    @apply antialiased;
  }

  html {
    scroll-behavior: smooth;
  }

  html,
  body,
  #root {
    min-height: 100%;
  }

  body {
    @apply font-sans text-slate-900;
    background-color: #f8fafc;
    background-image:
      radial-gradient(circle at 0% 0%, rgba(255, 152, 0, 0.12) 0, transparent 40%),
      radial-gradient(circle at 100% 0%, rgba(14, 165, 233, 0.1) 0, transparent 45%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }

  html.dark {
    color-scheme: dark;
  }

  html.dark body {
    @apply text-slate-100;
    background-color: #020617;
    background-image:
      radial-gradient(circle at 10% 0%, rgba(249, 115, 22, 0.2) 0, transparent 36%),
      radial-gradient(circle at 100% 0%, rgba(14, 165, 233, 0.16) 0, transparent 42%),
      linear-gradient(180deg, #020617 0%, #0b1220 100%);
  }

  h1,
  h2,
  h3,
  h4 {
    @apply font-display text-slate-950;
  }

  html.dark h1,
  html.dark h2,
  html.dark h3,
  html.dark h4 {
    @apply text-slate-50;
  }

  ::-webkit-scrollbar {
    width: 10px;
  }

  ::-webkit-scrollbar-track {
    @apply bg-slate-100;
  }

  ::-webkit-scrollbar-thumb {
    @apply rounded-full bg-slate-300;
  }

  ::-webkit-scrollbar-thumb:hover {
    @apply bg-brand-500;
  }

  html.dark ::-webkit-scrollbar-track {
    @apply bg-slate-900;
  }

  html.dark ::-webkit-scrollbar-thumb {
    @apply bg-slate-700;
  }

  html.dark ::-webkit-scrollbar-thumb:hover {
    @apply bg-slate-600;
  }
}

@layer components {
  .input-dark {
    @apply w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-body text-slate-900 transition-all duration-300 ease-premium;
  }

  .input-dark:focus {
    @apply border-brand-500 ring-4 ring-brand-500/10;
    outline: none;
  }

  .select-dark {
    @apply input-dark;
  }

  .textarea-dark {
    @apply w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-body text-slate-900 transition-all duration-300 ease-premium;
  }

  .textarea-dark:focus {
    @apply border-brand-500 ring-4 ring-brand-500/10;
    outline: none;
  }

  html.dark .input-dark,
  html.dark .select-dark,
  html.dark .textarea-dark {
    @apply border-white/10 bg-slate-900 text-slate-100;
  }

  html.dark .input-dark::placeholder,
  html.dark .textarea-dark::placeholder {
    @apply text-slate-400;
  }

  .native-select {
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 0.85rem center;
    background-size: 1rem 1rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='m6 8 4 4 4-4' stroke='%2364748B' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    padding-right: 2.5rem;
  }

  .dashboard-section {
    @apply rounded-2xl border border-slate-200/80 bg-white/80 p-6 shadow-premium backdrop-blur-sm;
  }

  html.dark .dashboard-section {
    @apply border-white/10 bg-slate-900/50;
  }

  .dashboard-section-title {
    @apply text-h3;
  }

  .dashboard-section-subtitle {
    @apply text-body-sm text-slate-600;
  }

  .section-heading {
    @apply text-h2;
  }

  .section-subtitle {
    @apply text-body text-slate-600;
  }

  html.dark .dashboard-section-subtitle,
  html.dark .section-subtitle {
    @apply text-slate-300;
  }
}

@layer utilities {
  .text-balance {
    text-wrap: balance;
  }

  .glass {
    @apply border border-white/20 bg-white/60 backdrop-blur-xl;
  }

  .glass-dark {
    @apply border border-white/10 bg-slate-900/60 backdrop-blur-xl;
  }

  html.dark .glass {
    @apply border-white/10 bg-slate-900/60;
  }

  .line-clamp-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
  }

  .animate-delay-100 {
    animation-delay: 0.1s;
  }

  .animate-delay-200 {
    animation-delay: 0.2s;
  }

  .animate-delay-300 {
    animation-delay: 0.3s;
  }

  .animate-delay-400 {
    animation-delay: 0.4s;
  }

  .dashboard-reveal {
    animation: dashboard-fade-up 0.46s cubic-bezier(0.22, 1, 0.36, 1) both;
  }
}

@keyframes dashboard-fade-up {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .dashboard-reveal {
    animation: none !important;
  }

  .animate-scanner-line {
    animation: none !important;
  }
}

@keyframes scanner-line {
  0% {
    top: 18%;
    opacity: 1;
  }
  48% {
    top: 82%;
    opacity: 1;
  }
  50% {
    top: 82%;
    opacity: 0;
  }
  52% {
    top: 18%;
    opacity: 0;
  }
  54% {
    top: 18%;
    opacity: 1;
  }
  100% {
    top: 18%;
    opacity: 1;
  }
}

.animate-scanner-line {
  animation: scanner-line 2s ease-in-out infinite;
}

@keyframes sheet-up {
  from {
    transform: translateY(100%);
    opacity: 0.6;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
```

### 5.7 ThemeProvider real
- Archivo: `src/providers/theme-provider.tsx`
- Storage key: `cp-theme`
- Mecanica: clase `dark` en `html`
```tsx
import { createContext, useCallback, useEffect, useLayoutEffect, useMemo, useState, type ReactNode } from 'react'

type Theme = 'light' | 'dark'

type ThemeContextValue = {
  theme: Theme
  isDark: boolean
  setTheme: (theme: Theme) => void
  toggleTheme: () => void
}

const THEME_STORAGE_KEY = 'cp-theme'
const FORCED_THEME = (() => {
  const raw = String(import.meta.env.VITE_FORCE_THEME || '').toLowerCase().trim()
  if (raw === 'light' || raw === 'dark') return raw as Theme
  return null
})()

export const ThemeContext = createContext<ThemeContextValue | null>(null)

const getSystemTheme = (): Theme => {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const getStoredTheme = (): Theme | null => {
  if (FORCED_THEME) return FORCED_THEME
  if (typeof window === 'undefined') return null
  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY)
  return savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : null
}

const applyTheme = (theme: Theme) => {
  document.documentElement.classList.toggle('dark', theme === 'dark')
}

export default function ThemeProvider({ children }: { children: ReactNode }) {
  const [theme, setThemeState] = useState<Theme>(() => FORCED_THEME ?? getStoredTheme() ?? getSystemTheme())

  useLayoutEffect(() => {
    applyTheme(theme)
    if (!FORCED_THEME) {
      localStorage.setItem(THEME_STORAGE_KEY, theme)
    }
  }, [theme])

  useEffect(() => {
    if (FORCED_THEME) return
    const media = window.matchMedia('(prefers-color-scheme: dark)')
    const onChange = () => {
      const stored = getStoredTheme()
      if (!stored) {
        setThemeState(getSystemTheme())
      }
    }

    media.addEventListener('change', onChange)
    return () => media.removeEventListener('change', onChange)
  }, [])

  const setTheme = useCallback((nextTheme: Theme) => {
    if (FORCED_THEME) return
    setThemeState(nextTheme)
  }, [])

  const toggleTheme = useCallback(() => {
    if (FORCED_THEME) return
    setThemeState((prev) => (prev === 'dark' ? 'light' : 'dark'))
  }, [])

  const value = useMemo(
    () => ({
      theme,
      isDark: theme === 'dark',
      setTheme,
      toggleTheme,
    }),
    [theme, setTheme, toggleTheme],
  )

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}
```

### 5.8 Variables CSS custom
- Busqueda `:root`, `--var`, `var(--...)` en `src`: no se encontraron.

### 5.9 Hex hardcoded fuera de tokens (top)
| Cantidad | Archivo |
|---|---|
| 70 | comercio-plus-frontend/src/app/dashboard/products/page.tsx |
| 57 | comercio-plus-frontend/src/components/Navbar.tsx |
| 25 | comercio-plus-frontend/src/app/dashboard/customers/page.tsx |
| 19 | comercio-plus-frontend/src/app/store/page.tsx |
| 14 | comercio-plus-frontend/src/components/ui/button.tsx |
| 10 | comercio-plus-frontend/src/components/ProductCard.tsx |
| 9 | comercio-plus-frontend/src/app/register/page.tsx |
| 9 | comercio-plus-frontend/src/app/dashboard/page.tsx |
| 9 | comercio-plus-frontend/src/components/dashboard/Sidebar.tsx |
| 6 | comercio-plus-frontend/src/app/globals.css |
| 5 | comercio-plus-frontend/src/app/login/page.tsx |
| 4 | comercio-plus-frontend/src/components/ui/GlassCard.tsx |
| 4 | comercio-plus-frontend/src/components/products/create/ProductMethodSelector.tsx |
| 3 | comercio-plus-frontend/src/app/dashboard/inventory/receive/page.tsx |
| 3 | comercio-plus-frontend/src/app/dashboard/inventory/page.tsx |

## 6) Inventario de UI primitives
| Componente | Archivo | Props principales | Usa tokens | Usa inline styles | Se reutiliza |
|---|---|---|---|---|---|
| Button (ui) | src/components/ui/button.tsx | variant, loading, className + ButtonHTMLAttributes | Parcial (muchos hex hardcoded) | No | Si (12 imports) |
| Input (ui) | src/components/ui/Input.tsx | label, hint, error, leftIcon, rightIcon, rightIconButton, onRightIconClick, containerClassName | Parcial (`input-dark` + style inline en label) | Si (1 inline) | Si (5 imports) |
| GlassCard (ui) | src/components/ui/GlassCard.tsx | className, children + HTMLAttributes | Parcial (gradientes/hex hardcoded) | No | Si (11 imports) |
| Badge (ui) | src/components/ui/Badge.tsx | variant, className, children | Si (tailwind) | No | Si (5 imports) |
| Select (ui) | src/components/ui/Select.tsx | label, hint, error, options + select attrs | Parcial (style inline label) | Si | Si (2 imports) |
| Textarea (ui) | src/components/ui/Textarea.tsx | label, hint, error + textarea attrs | Parcial (style inline label) | Si | Si (2 imports) |
| Button (legacy) | src/components/Button.tsx | variant, size, fullWidth, icon, iconPosition, loading | Si | No | Si (7 imports) |
| Input (legacy) | src/components/Input.tsx | label, error, success, hint, fullWidth, icon, iconPosition | Si | No | Si (2 imports) |
| Card (legacy) | src/components/Card.tsx | variant, hoverable, padding, gradient, onClick | Si | No | Si (4 imports) |
| Badge (legacy) | src/components/Badge.tsx | variant, className, children | Si | No | Si (3 imports) |
| Sidebar | src/components/dashboard/Sidebar.tsx | store? | Parcial (tokens + hex + inline) | Si | Si |
| Navbar | src/components/Navbar.tsx | sin props | No (predomina inline/hardcoded) | Si | Si |
| Footer | src/components/Footer.tsx | sin props | Si mayormente tailwind | No | Si |
| Modal global | no existe | - | - | - | No |
| Toast global | no existe | - | - | - | No |
| Table reusable | no existe | - | - | - | No |

## 7) Responsividad
- Drawer mobile global de dashboard: no existe en `DashboardLayout`.
- Sidebar fijo: `src/components/dashboard/Sidebar.tsx` usa `w-[205px]` y `h-screen`.
- Menu mobile existe solo en navbar publico (`cp-hamburger` en `Navbar.tsx`).
- Hay drawers por feature (`AdjustDrawer`, `KardexDrawer` en inventory), no primitive global.
- Anchos fijos detectados (ejemplos):
  - `src/app/dashboard/customers/page.tsx`: `table min-w-[980px]`
  - `src/app/dashboard/products/page.tsx`: `lg:w-[380px]`, `h-[108px]`
  - `src/app/store/page.tsx`: `h-[280px]`

## 8) Duplicacion y legacy
- Duplicacion de primitives:
  - Legacy: `src/components/{Button,Input,Card,Badge}.tsx`
  - UI: `src/components/ui/{button,Input,GlassCard,Badge,Select,Textarea}.tsx`
- Duplicacion de capa de iconos: `src/components/Icon.tsx` + `src/ui/Icon.tsx` + `src/ui/icons.ts`.
- Estructura hibrida de pantallas: rutas desde `src/app/*` y `src/pages/*`.
- Directorios vacios detectados:
  - `src/components/theme`
  - `src/app/how-it-works`
  - `src/app/product`
  - `src/app/products`
  - `src/app/dashboard/store`
- Posible archivo huerfano: `src/components/Header.tsx` (sin imports detectados).
- Archivo temporal en source: `src/app/.tmp_delete_test.txt`.

## 9) Deuda tecnica visual con evidencia
### 9.1 Inline styles (top)
| Cantidad | Archivo |
|---|---|
| 35 | comercio-plus-frontend/src/components/Navbar.tsx |
| 9 | comercio-plus-frontend/src/app/dashboard/page.tsx |
| 4 | comercio-plus-frontend/src/components/Icon.tsx |
| 2 | comercio-plus-frontend/src/app/dashboard/reports/page.tsx |
| 2 | comercio-plus-frontend/src/components/dashboard/Sidebar.tsx |
| 1 | comercio-plus-frontend/src/app/dashboard/orders/picking/page.tsx |
| 1 | comercio-plus-frontend/src/app/dashboard/categories/page.tsx |
| 1 | comercio-plus-frontend/src/app/dashboard/inventory/page.tsx |
| 1 | comercio-plus-frontend/src/app/store/page.tsx |
| 1 | comercio-plus-frontend/src/components/ui/Select.tsx |
| 1 | comercio-plus-frontend/src/components/ui/Textarea.tsx |
| 1 | comercio-plus-frontend/src/components/ui/Input.tsx |
| 1 | comercio-plus-frontend/src/pages/DashboardStore.tsx |
| 1 | comercio-plus-frontend/src/components/ProductCard.tsx |

### 9.2 Hex hardcoded (top)
| Cantidad | Archivo |
|---|---|
| 70 | comercio-plus-frontend/src/app/dashboard/products/page.tsx |
| 57 | comercio-plus-frontend/src/components/Navbar.tsx |
| 25 | comercio-plus-frontend/src/app/dashboard/customers/page.tsx |
| 19 | comercio-plus-frontend/src/app/store/page.tsx |
| 14 | comercio-plus-frontend/src/components/ui/button.tsx |
| 10 | comercio-plus-frontend/src/components/ProductCard.tsx |
| 9 | comercio-plus-frontend/src/app/register/page.tsx |
| 9 | comercio-plus-frontend/src/app/dashboard/page.tsx |
| 9 | comercio-plus-frontend/src/components/dashboard/Sidebar.tsx |
| 6 | comercio-plus-frontend/src/app/globals.css |
| 5 | comercio-plus-frontend/src/app/login/page.tsx |
| 4 | comercio-plus-frontend/src/components/ui/GlassCard.tsx |
| 4 | comercio-plus-frontend/src/components/products/create/ProductMethodSelector.tsx |
| 3 | comercio-plus-frontend/src/app/dashboard/inventory/receive/page.tsx |
| 3 | comercio-plus-frontend/src/app/dashboard/inventory/page.tsx |

### 9.3 Layouts y consistencia
- Contrato actual existe (Public/Auth/Dashboard), pero el lenguaje visual no es uniforme por hardcoded/inline.
- `Navbar.tsx` concentra la mayor deuda visual.

### 9.4 Componentes no usados / no estandarizados
- `src/components/Header.tsx`: sin uso detectado.
- No existe primitive global de `Modal`, `Toast`, `Table`.
- Modales y toasts son implementaciones puntuales por pagina.

## 10) Reglas obligatorias para rediseño
1. No romper rutas ni wrappers de auth (`RequireAuth`, `RequireRole`).
2. No cambiar contrato de auth (`token`, `user`, Bearer) sin plan de migracion.
3. No agregar una tercera capa de UI; consolidar legacy/ui en una sola capa canonica.
4. Unificar tokens y reducir hardcoded/inline progresivamente.
5. Implementar primitives faltantes (`Modal`, `Toast`, `Table`) antes de escalar rediseno.
6. Mantener layout contract: PublicLayout, AuthLayout, DashboardLayout.
7. Limpiar deuda estructural minima previa (huerfanos, temporales, dirs vacios).

## Anexo A) Arbol real de carpetas
### A.1 `src/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 0000007C 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC
+---app
|   |   .tmp_delete_test.txt
|   |   App.tsx
|   |   globals.css
|   |   main.tsx
|   |   
|   +---category
|   |       page.tsx
|   |       
|   +---dashboard
|   |   |   page.tsx
|   |   |   
|   |   +---categories
|   |   |       page.tsx
|   |   |       
|   |   +---customers
|   |   |       page.tsx
|   |   |       
|   |   +---inventory
|   |   |   |   page.tsx
|   |   |   |   
|   |   |   \---receive
|   |   |           page.tsx
|   |   |           
|   |   +---orders
|   |   |   |   page.tsx
|   |   |   |   
|   |   |   \---picking
|   |   |           page.tsx
|   |   |           
|   |   +---products
|   |   |       page.tsx
|   |   |       
|   |   +---reports
|   |   |       page.tsx
|   |   |       
|   |   +---settings
|   |   |       page.tsx
|   |   |       
|   |   \---store
|   +---how-it-works
|   +---login
|   |       page.tsx
|   |       
|   +---privacy
|   |       page.tsx
|   |       
|   +---product
|   +---products
|   +---register
|   |       page.tsx
|   |       
|   +---store
|   |   |   page.tsx
|   |   |   
|   |   \---create
|   |           page.tsx
|   |           
|   +---stores
|   |       page.tsx
|   |       
|   \---terms
|           page.tsx
|           
+---components
|   |   Badge.tsx
|   |   Button.tsx
|   |   Card.tsx
|   |   Footer.tsx
|   |   Header.tsx
|   |   Icon.tsx
|   |   Input.tsx
|   |   Navbar.tsx
|   |   ProductCard.tsx
|   |   
|   +---auth
|   |       RequireAuth.tsx
|   |       RequireRole.tsx
|   |       
|   +---dashboard
|   |       Sidebar.tsx
|   |       
|   +---inventory
|   |       QuickCreateProductModal.tsx
|   |       ReceiveHistoryList.tsx
|   |       ReceiveScannerPanel.tsx
|   |       
|   +---layouts
|   |       AppShell.tsx
|   |       AuthLayout.tsx
|   |       DashboardLayout.tsx
|   |       PublicLayout.tsx
|   |       
|   +---products
|   |   |   ProductScannerCameraModal.tsx
|   |   |   
|   |   \---create
|   |           ProductMethodSelector.tsx
|   |           ProductScannerKeyboardPanel.tsx
|   |           
|   +---theme
|   \---ui
|           Badge.tsx
|           button.tsx
|           GlassCard.tsx
|           Input.tsx
|           Select.tsx
|           Textarea.tsx
|           
+---context
|       CartContext.tsx
|       
+---hooks
|       useDebouncedValue.ts
|       useInventoryReceive.ts
|       
+---lib
|       api-response.ts
|       api.ts
|       apiPayload.ts
|       format.ts
|       routes.ts
|       runtime.ts
|       
+---pages
|       Cart.tsx
|       Checkout.tsx
|       CheckoutSuccess.tsx
|       DashboardStore.tsx
|       ForgotPassword.tsx
|       Home.tsx
|       HowItWorks.tsx
|       ProductDetail.tsx
|       Products.tsx
|       SimpleContentPage.tsx
|       StoreProducts.tsx
|       
+---providers
|       theme-provider.tsx
|       
+---services
|       api.ts
|       auth-session.ts
|       inventoryReceive.ts
|       picking.ts
|       productCodeLookup.ts
|       uploads.ts
|       
+---types
|       api.ts
|       index.ts
|       
+---ui
|   |   icon-config.ts
|   |   Icon.tsx
|   |   icons.ts
|   |   
|   \---images
|           CoverImage.tsx
|           LogoImage.tsx
|           
\---utils
        cloudinary.ts
        imageTheme.ts
        imageUtils.ts
        
```

### A.2 `src/components/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 00000092 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC\COMPONENTS
|   Badge.tsx
|   Button.tsx
|   Card.tsx
|   Footer.tsx
|   Header.tsx
|   Icon.tsx
|   Input.tsx
|   Navbar.tsx
|   ProductCard.tsx
|   
+---auth
|       RequireAuth.tsx
|       RequireRole.tsx
|       
+---dashboard
|       Sidebar.tsx
|       
+---inventory
|       QuickCreateProductModal.tsx
|       ReceiveHistoryList.tsx
|       ReceiveScannerPanel.tsx
|       
+---layouts
|       AppShell.tsx
|       AuthLayout.tsx
|       DashboardLayout.tsx
|       PublicLayout.tsx
|       
+---products
|   |   ProductScannerCameraModal.tsx
|   |   
|   \---create
|           ProductMethodSelector.tsx
|           ProductScannerKeyboardPanel.tsx
|           
+---theme
\---ui
        Badge.tsx
        button.tsx
        GlassCard.tsx
        Input.tsx
        Select.tsx
        Textarea.tsx
        
```

### A.3 `src/components/layouts/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 00000023 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC\COMPONENTS\LAYOUTS
    AppShell.tsx
    AuthLayout.tsx
    DashboardLayout.tsx
    PublicLayout.tsx
    
No existe ninguna subcarpeta 

```


### A.4 `src/components/ui/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 0000003F 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC\COMPONENTS\UI
    Badge.tsx
    button.tsx
    GlassCard.tsx
    Input.tsx
    Select.tsx
    Textarea.tsx
    
No existe ninguna subcarpeta 

```

### A.5 `src/app/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 0000004B 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC\APP
|   .tmp_delete_test.txt
|   App.tsx
|   globals.css
|   main.tsx
|   
+---category
|       page.tsx
|       
+---dashboard
|   |   page.tsx
|   |   
|   +---categories
|   |       page.tsx
|   |       
|   +---customers
|   |       page.tsx
|   |       
|   +---inventory
|   |   |   page.tsx
|   |   |   
|   |   \---receive
|   |           page.tsx
|   |           
|   +---orders
|   |   |   page.tsx
|   |   |   
|   |   \---picking
|   |           page.tsx
|   |           
|   +---products
|   |       page.tsx
|   |       
|   +---reports
|   |       page.tsx
|   |       
|   +---settings
|   |       page.tsx
|   |       
|   \---store
+---how-it-works
+---login
|       page.tsx
|       
+---privacy
|       page.tsx
|       
+---product
+---products
+---register
|       page.tsx
|       
+---store
|   |   page.tsx
|   |   
|   \---create
|           page.tsx
|           
+---stores
|       page.tsx
|       
\---terms
        page.tsx
        
```

### A.6 `src/pages/`
```txt
Listado de rutas de carpetas para el volumen SO
El n�mero de serie del volumen es 000000F7 967A:889E
C:\XAMPP\HTDOCS\COMERCIOPLUSOFICIAL\COMERCIO-PLUS-FRONTEND\SRC\PAGES
    Cart.tsx
    Checkout.tsx
    CheckoutSuccess.tsx
    DashboardStore.tsx
    ForgotPassword.tsx
    Home.tsx
    HowItWorks.tsx
    ProductDetail.tsx
    Products.tsx
    SimpleContentPage.tsx
    StoreProducts.tsx
    
No existe ninguna subcarpeta 

```

## Anexo B) Decisiones obligatorias (Anti-Deuda)
1) API canónico: `@/services/api`
- Cliente oficial: `src/services/api.ts`.
- Evidencia técnica confirmada:
  - Bearer token en interceptor request (`Authorization: Bearer ...`).
  - Manejo global `401`: limpia sesion (`token/user` en `localStorage` y `sessionStorage`) y redirige a `/login`.
  - Cache GET activa (`GET_CACHE_TTL_MS = 45_000`) con invalidacion en mutaciones.
- Compatibilidad mantenida:
  - `src/lib/api.ts` queda como alias de compatibilidad:
    ```ts
    export { default } from '@/services/api'
    ```
  - Estado: permitido para imports legacy, deprecado para codigo nuevo.

2) UI canónica: `src/components/ui/*`
- Base establecida:
  - `src/components/ui/index.ts` agrega re-exports canónicos (`Button`, `Input`, `Select`, `Textarea`, `Badge`, `GlassCard`).
  - `src/components/index.ts` agrega capa de compatibilidad para primitives legacy (`Button`, `Input`, `Card`, `Badge`).
- Regla obligatoria:
  - Componentes nuevos SOLO en `src/components/ui/*`.
  - `src/components/*` se mantiene como legacy (sin borrado en esta fase).

3) `/product/:id` alias de `/products/:id`
- Vista canónica documentada: `/products/:id`.
- Alias legacy preservado: `/product/:id`.
- Ambas rutas renderizan el mismo componente real: `src/pages/ProductDetail.tsx` (sin cambio de paths).

4) Checkout con `next=/checkout` y retorno post-login
- Flujo definido:
  - Usuario no autenticado en `/checkout` -> redirect a `/login?next=/checkout`.
  - Tras login:
    - si existe `next` valido interno, navegar a `next`;
    - si no existe, resolver con `resolvePostAuthRoute(user)`.
  - Compatibilidad legacy: `?redirect=` sigue soportado en login.
- Implementacion aplicada:
  - `src/pages/Checkout.tsx` usa `next`.
  - `src/app/login/page.tsx` prioriza `next`, luego `redirect`, luego fallback por rol.

5) Cover/Logo con overlay + primitives `CoverImage`/`LogoImage`
- Patrón único de cabecera con portada:
  - Overlay (scrim) obligatorio sobre cover para contraste de texto.
  - Evitar texto directo sobre imagen sin scrim.
  - Uso de primitives `CoverImage` y `LogoImage` en la vista de tienda.
- Implementacion aplicada:
  - `src/ui/images/CoverImage.tsx`: nuevo `overlayMode` con modo `header` para scrim reforzado.
  - `src/app/store/page.tsx`: cabecera usa `CoverImage` con `overlay` + `overlayMode="header"`.

6) Dashboard responsive: drawer `< md`, sidebar fija `>= md`
- Base técnica previa al rediseño implementada:
  - `src/components/dashboard/DashboardTopbar.tsx` (placeholder funcional mobile).
  - `src/components/dashboard/SidebarDrawer.tsx` (estructura basica drawer mobile).
  - Integracion en `src/components/layouts/DashboardLayout.tsx`:
    - `>= md`: sidebar normal visible.
    - `< md`: topbar con boton menu + drawer lateral.
    - cierre automatico de drawer al cambiar de ruta.

### Archivos modificados/creados en este anexo y motivo
| Archivo | Tipo | Motivo |
|---|---|---|
| `src/components/ui/index.ts` | Creado | Re-export canónico de primitives UI (evita tercera capa). |
| `src/components/index.ts` | Creado | Compatibilidad de primitives legacy sin ruptura. |
| `src/pages/Checkout.tsx` | Modificado | Gating a login con `next=/checkout`. |
| `src/app/login/page.tsx` | Modificado | Retorno post-login: `next` -> `redirect` legacy -> `resolvePostAuthRoute`. |
| `src/components/dashboard/DashboardTopbar.tsx` | Creado | Placeholder topbar mobile para dashboard. |
| `src/components/dashboard/SidebarDrawer.tsx` | Creado | Placeholder drawer mobile del sidebar. |
| `src/components/layouts/DashboardLayout.tsx` | Modificado | Integracion responsive drawer/sidebar sin cambiar wrappers auth. |
| `src/ui/images/CoverImage.tsx` | Modificado | Scrim estandarizado para cabeceras (`overlayMode="header"`). |
| `src/app/store/page.tsx` | Modificado | Aplicacion del patron de contraste en portada de tienda. |
| `src/lib/api.ts` | Confirmado (sin cambio) | Alias de compatibilidad al API canónico `@/services/api`. |

## Anexo C) Flujo Oficial del Cliente (Guest-First)

### 1) Principio de experiencia (CONTRATO)
ComercioPlus adopta un modelo **Guest-First Commerce**.

- El usuario **MAY** explorar tiendas públicas y productos sin autenticación.
- El usuario **MAY** agregar productos al carrito sin autenticación.
- La autenticación **MUST NOT** exigirse hasta el momento del pago (Checkout).

Objetivo: reducir fricción y maximizar conversión sin romper seguridad del pago.

### 2) Carrito para invitados (Storage contract)
Implementación canónica: `src/context/CartContext.tsx`  
Persistencia: `localStorage` usando `CART_STORAGE_KEY = 'cart'`.

Reglas obligatorias:
- El carrito **MUST** funcionar con o sin token (guest por defecto).
- El carrito **MUST** persistir entre recargas (localStorage).
- El carrito **MUST NOT** depender del backend en esta fase (hasta creación de orden).

Nota de autenticación (keys reales):
- Token: `TOKEN_KEY = 'token'`
- User: `USER_KEY = 'user'`
(Definidos en `src/services/auth-session.ts` y `src/services/api.ts`)

### 3) Gating obligatorio en Checkout (sin romper rutas)
Ruta: `/checkout` (componente `src/pages/Checkout.tsx`)

Comportamiento obligatorio:
- Si `items.length === 0` → **redirect** a `/cart`.
- Si no existe token (`getStoredToken()` retorna null) → **redirect** a:
  `/login?next=/checkout`

Post-login:
- Si existe `next` válido interno → navegar a `next` (prioridad).
- Si no existe → usar `resolvePostAuthRoute(user)`.
Compatibilidad legacy:
- `?redirect=` se soporta como fallback secundario (no prioritario).

### 4) Registro solo si va a pagar (Flujo oficial)
Flujo **MUST** ser:
Explorar → Agregar al carrito → Ir a Checkout → Login/Registro → Pago

NO permitido (MUST NOT):
- Forzar login para ver productos.
- Forzar login para agregar al carrito.
- Forzar login para ver tiendas públicas.

### 5) StoreDetail → Conexión real al carrito (API de addToCart)
Archivo: `src/app/store/page.tsx`

Requisito obligatorio:
- El botón “Agregar al Carrito” **MUST** llamar `addToCart(...)` con shape real:

```ts
addToCart({
  id: product.id,
  name: product.name,
  price: product.price,
  image: resolveMediaUrl(product.image_url || product.image) || '',
  seller: store.name || 'Tienda ComercioPlus',
  storeId: store.id,
})Además:

Debe funcionar para invitados.

Debe tener fallbacks seguros (image/seller/storeId) para no romper UI.

6) No rediseño, no cambios de ruta, no librerías

Este flujo se implementa sin:

Cambiar estructura de rutas.

Cambiar wrappers de autenticación.

Crear nuevos clientes API.

Introducir nuevas librerías.

7) Alcance

Este anexo aplica al flujo público/cliente (guest-first).
No aplica al dashboard merchant (/dashboard/*).
