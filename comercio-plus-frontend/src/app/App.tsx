import { Suspense, lazy } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import RequireAuth from '@/components/auth/RequireAuth'
import RequireRole from '@/components/auth/RequireRole'
import AuthLayout from '@/components/layouts/AuthLayout'
import DashboardLayout from '@/components/layouts/DashboardLayout'
import PublicLayout from '@/components/layouts/PublicLayout'

function PageLoader() {
  return (
    <div className="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div className="space-y-4">
        <div className="h-6 w-40 animate-pulse rounded bg-slate-200/70 dark:bg-slate-800/70" />
        <div className="h-24 w-full animate-pulse rounded-2xl bg-slate-200/70 dark:bg-slate-800/70" />
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div className="h-20 animate-pulse rounded-2xl bg-slate-200/70 dark:bg-slate-800/70" />
          <div className="h-20 animate-pulse rounded-2xl bg-slate-200/70 dark:bg-slate-800/70" />
          <div className="h-20 animate-pulse rounded-2xl bg-slate-200/70 dark:bg-slate-800/70" />
        </div>
      </div>
    </div>
  )
}

const Category = lazy(() => import('./category/page'))
const CreateStore = lazy(() => import('./store/create/page'))
const StoreDetail = lazy(() => import('./store/page'))
const Stores = lazy(() => import('./stores/page'))
const Privacy = lazy(() => import('./privacy/page'))
const Terms = lazy(() => import('./terms/page'))

const Login = lazy(() => import('./login/page'))
const Register = lazy(() => import('./register/page'))
const ForgotPassword = lazy(() => import('@/pages/ForgotPassword'))

const Products = lazy(() => import('@/pages/Products'))
const ProductDetail = lazy(() => import('@/pages/ProductDetail'))
const HomePage = lazy(() => import('@/pages/Home'))
const HowItWorks = lazy(() => import('@/pages/HowItWorks'))
const StoreProducts = lazy(() => import('@/pages/StoreProducts'))
const Cart = lazy(() => import('@/pages/Cart'))
const Checkout = lazy(() => import('@/pages/Checkout'))
const CheckoutSuccess = lazy(() => import('@/pages/CheckoutSuccess'))
const SimpleContentPage = lazy(() => import('@/pages/SimpleContentPage'))

const Dashboard = lazy(() => import('./dashboard/page'))
const DashboardCategoriesPage = lazy(() => import('./dashboard/categories/page'))
const DashboardCustomers = lazy(() => import('./dashboard/customers/page'))
const DashboardCreditPage = lazy(() => import('./dashboard/credit/page'))
const DashboardInventoryPage = lazy(() => import('./dashboard/inventory/page'))
const InventoryReceivePage = lazy(() => import('./dashboard/inventory/receive/page'))
const InventoryImportPage = lazy(() => import('./dashboard/inventory/import/page'))
const ManageProducts = lazy(() => import('./dashboard/products/page'))
const DashboardReportsPage = lazy(() => import('./dashboard/reports/page'))
const DashboardSettingsPage = lazy(() => import('./dashboard/settings/page'))
const DashboardOrdersPage = lazy(() => import('./dashboard/orders/page'))
const OrderPickingPage = lazy(() => import('./dashboard/orders/picking/page'))
const DashboardStore = lazy(() => import('./dashboard/store/page'))

export default function App() {
  return (
    <BrowserRouter>
      <Suspense fallback={<PageLoader />}>
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
            <Route path="/dashboard/credit" element={<DashboardCreditPage />} />
            <Route path="/dashboard/store" element={<DashboardStore />} />
            <Route path="/dashboard/settings" element={<DashboardSettingsPage />} />
            <Route path="/dashboard/orders" element={<DashboardOrdersPage />} />
            <Route path="/dashboard/orders/:id/picking" element={<OrderPickingPage />} />
            <Route path="/dashboard/inventory" element={<DashboardInventoryPage />} />
            <Route path="/dashboard/inventory/receive" element={<InventoryReceivePage />} />
            <Route path="/dashboard/inventory/import" element={<InventoryImportPage />} />
            <Route path="/dashboard/reports" element={<DashboardReportsPage />} />
            <Route path="/dashboard/categories" element={<DashboardCategoriesPage />} />
            <Route path="/dashboard/products" element={<ManageProducts />} />
            <Route path="/dashboard/products/create" element={<ManageProducts />} />
            <Route path="/dashboard/products/:id/edit" element={<ManageProducts />} />
          </Route>
        </Routes>
      </Suspense>
    </BrowserRouter>
  )
}
