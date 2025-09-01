import { createRouter, createWebHistory } from 'vue-router'
import { fetchMe, isComerciante, session } from './stores/session'

// vistas
import Welcome from './views/Welcome.vue'
import Login from './views/AuthLogin.vue'
import Register from './views/AuthRegister.vue'
import StoreCreate from './views/StoreCreate.vue'
import ThemeSettings from './views/ThemeSettings.vue'
import ProductsList from './views/ProductsList.vue'
import ProductsCreate from './views/ProductsCreate.vue'
import NotFound from './views/NotFound.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: Welcome },
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },
    { path: '/register', name: 'register', component: Register, meta: { guest: true } },

    { path: '/store/create', name: 'store.create', component: StoreCreate, meta: { auth: true, comerciante: true } },
    { path: '/settings/theme', name: 'settings.theme', component: ThemeSettings, meta: { auth: true, comerciante: true } },

    { path: '/products', name: 'products.index', component: ProductsList, meta: { auth: true, comerciante: true } },
    { path: '/products/create', name: 'products.create', component: ProductsCreate, meta: { auth: true, comerciante: true } },

    { path: '/:pathMatch(.*)*', name: '404', component: NotFound }
  ]
})

// Guard
let firstLoad = true
router.beforeEach(async (to, from, next) => {
  if (firstLoad) { firstLoad = false; await fetchMe().catch(()=>{}) }

  const authed = !!session.user
  if (to.meta.guest && authed) return next({ name: 'products.index' })
  if (to.meta.auth && !authed)  return next({ name: 'login' })

  if (to.meta.comerciante) {
    if (!isComerciante()) return next({ name: 'home' })
    const hasStore = !!session.store
    if (!hasStore && to.name !== 'store.create') return next({ name: 'store.create' })
    if (hasStore && to.name === 'store.create') return next({ name: 'products.create' })
  }
  next()
})

export default router
