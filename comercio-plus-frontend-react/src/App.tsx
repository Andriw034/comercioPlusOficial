import { BrowserRouter, Route, Routes } from 'react-router-dom'
import RequireAuth from './components/RequireAuth'
import AuthLayout from './layouts/AuthLayout'
import DashboardLayout from './layouts/DashboardLayout'
import PublicLayout from './layouts/PublicLayout'
import Category from './pages/Category'
import CreateStore from './pages/CreateStore'
import Dashboard from './pages/Dashboard'
import Home from './pages/Home'
import HowItWorks from './pages/HowItWorks'
import Login from './pages/Login'
import ManageProducts from './pages/ManageProducts'
import ManageStore from './pages/ManageStore'
import ProductDetail from './pages/ProductDetail'
import Products from './pages/Products'
import Register from './pages/Register'
import StoreDetail from './pages/StoreDetail'
import Stores from './pages/Stores'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<PublicLayout />}>
          <Route path="/" element={<Home />} />
          <Route path="/stores" element={<Stores />} />
          <Route path="/store/:id" element={<StoreDetail />} />
          <Route path="/how-it-works" element={<HowItWorks />} />
          <Route path="/products" element={<Products />} />
          <Route path="/product/:id" element={<ProductDetail />} />
          <Route path="/category/:id" element={<Category />} />
        </Route>

        <Route element={<AuthLayout />}>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
        </Route>

        <Route
          element={
            <RequireAuth>
              <DashboardLayout />
            </RequireAuth>
          }
        >
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/dashboard/store" element={<ManageStore />} />
          <Route path="/dashboard/products" element={<ManageProducts />} />
        </Route>

        <Route
          path="/store/create"
          element={
            <RequireAuth>
              <CreateStore />
            </RequireAuth>
          }
        />
      </Routes>
    </BrowserRouter>
  )
}
