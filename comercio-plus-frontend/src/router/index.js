// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// Vistas principales
import Home from '../views/Home.vue';
import Stores from '../views/Stores.vue';
import CreateStore from '../views/CreateStore.vue';
import HowItWorks from '../views/HowItWorks.vue';
import Products from '../views/Products.vue';
import Category from '../views/Category.vue';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Dashboard from '../views/Dashboard.vue';
import ManageStore from '../views/ManageStore.vue';
import ManageProducts from '../views/ManageProducts.vue';

// Lazy-load (carga bajo demanda)
const StoreDetail = () => import('../views/StoreDetail.vue');
const ProductDetail = () => import('../views/ProductDetail.vue');

const routes = [
  { path: '/', name: 'home', component: Home, meta: { layout: 'public' } },
  { path: '/stores', name: 'stores', component: Stores, meta: { layout: 'public' } },
  { path: '/store/create', name: 'store-create', component: CreateStore, meta: { layout: 'dashboard', requiresAuth: true } },
  { path: '/store/:id', name: 'store-detail', component: StoreDetail, props: true, meta: { layout: 'public' } },
  { path: '/how-it-works', name: 'how-it-works', component: HowItWorks, meta: { layout: 'public' } },
  { path: '/products', name: 'products', component: Products, meta: { layout: 'public' } },
  { path: '/product/:id', name: 'product-detail', component: ProductDetail, props: true, meta: { layout: 'public' } },
  { path: '/category/:id', name: 'category', component: Category, props: true, meta: { layout: 'public' } },
  { path: '/login', name: 'login', component: Login, meta: { layout: 'auth' } },
  { path: '/register', name: 'register', component: Register, meta: { layout: 'auth' } },
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { layout: 'dashboard', requiresAuth: true } },
  { path: '/dashboard/store', name: 'dashboard-store', component: ManageStore, meta: { layout: 'dashboard', requiresAuth: true } },
  { path: '/dashboard/products', name: 'dashboard-products', component: ManageProducts, meta: { layout: 'dashboard', requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('token');
  if (to.meta.requiresAuth && !token) {
    next({ name: 'login', query: { redirect: to.fullPath } });
  } else {
    next();
  }
});

export default router;
