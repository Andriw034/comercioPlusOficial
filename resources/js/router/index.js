import { createRouter, createWebHistory } from 'vue-router'

// Páginas
import Welcome from '@/pages/Welcome.vue'
import StoresList from '@/pages/StoresList.vue'
import StoreCreate from '@/pages/StoreCreate.vue'
import Products from '@/pages/Products.vue'
import ProductDetail from '@/pages/ProductDetail.vue'
import Cart from '@/pages/Cart.vue'
import Checkout from '@/pages/Checkout.vue'
import Orders from '@/pages/Orders.vue'
import Login from '@/pages/Login.vue'
import Register from '@/pages/Register.vue'
import Profile from '@/pages/Profile.vue'
import Settings from '@/pages/Settings.vue'
import NotFound from '@/pages/NotFound.vue'

const routes = [
  { path: '/', name: 'home', component: Welcome },
  { path: '/stores', name: 'stores', component: StoresList },
  { path: '/stores/create', name: 'store-create', component: StoreCreate },
  { path: '/products', name: 'products', component: Products },
  { path: '/product/:slug', name: 'product-detail', component: ProductDetail, props: true },
  { path: '/cart', name: 'cart', component: Cart },
  { path: '/checkout', name: 'checkout', component: Checkout },
  { path: '/orders', name: 'orders', component: Orders },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/profile', name: 'profile', component: Profile },
  { path: '/settings', name: 'settings', component: Settings },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
]

const router = createRouter({ history: createWebHistory(), routes })
export default router
