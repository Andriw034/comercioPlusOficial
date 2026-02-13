import { BrowserRouter, Route, Routes } from 'react-router-dom'
import RequireAuth from '@/components/auth/RequireAuth'
import RequireRole from '@/components/auth/RequireRole'
import AuthLayout from '@/components/layouts/AuthLayout'
import DashboardLayout from '@/components/layouts/DashboardLayout'
import PublicLayout from '@/components/layouts/PublicLayout'

import Category from './category/page'
import CreateStore from './store/create/page'
import Dashboard from './dashboard/page'
import DashboardCustomers from './dashboard/customers/page'
import Home from './page'
import Login from './login/page'
import ManageProducts from './dashboard/products/page'
import Register from './register/page'
import StoreDetail from './store/page'
import Stores from './stores/page'
import Privacy from './privacy/page'
import Terms from './terms/page'
import Products from '@/pages/Products'
import ProductDetail from '@/pages/ProductDetail'
import DashboardStore from '@/pages/DashboardStore'
import HowItWorks from '@/pages/HowItWorks'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<PublicLayout />}>
          <Route path="/" element={<Home />} />
          <Route path="/stores" element={<Stores />} />

          {/* importante: "create" antes que ":id" */}
          <Route path="/store/create" element={<CreateStore />} />

          <Route path="/store/:id" element={<StoreDetail />} />
          <Route path="/how-it-works" element={<HowItWorks />} />
          <Route path="/products" element={<Products />} />
          <Route path="/products/:id" element={<ProductDetail />} />
          <Route path="/product/:id" element={<ProductDetail />} />
          <Route path="/category/:id" element={<Category />} />
          <Route path="/privacy" element={<Privacy />} />
          <Route path="/terms" element={<Terms />} />
        </Route>

        <Route element={<AuthLayout />}>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
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
          <Route path="/dashboard/products" element={<ManageProducts />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
