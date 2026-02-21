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
import Login from './login/page'
import ManageProducts from './dashboard/products/page'
import DashboardReportsPage from './dashboard/reports/page'
import DashboardSettingsPage from './dashboard/settings/page'
import DashboardOrdersPage from './dashboard/orders/page'
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
          <Route path="/dashboard/inventory" element={<DashboardInventoryPage />} />
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
