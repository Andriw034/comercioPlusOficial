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

// Lazy-load (carga bajo demanda)
const StoreDetail = () => import('../views/StoreDetail.vue');
const ProductDetail = () => import('../views/ProductDetail.vue');

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/stores', name: 'stores', component: Stores },
  { path: '/store/create', name: 'store-create', component: CreateStore },
  { path: '/store/:slug', name: 'store-detail', component: StoreDetail, props: true },
  { path: '/how-it-works', name: 'how-it-works', component: HowItWorks },
  { path: '/products', name: 'products', component: Products },
  { path: '/product/:slug', name: 'product-detail', component: ProductDetail, props: true },
  { path: '/category/:slug', name: 'category', component: Category, props: true },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/dashboard', name: 'dashboard', component: Dashboard },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;
