export const ROUTES = {
  login: '/login',
  register: '/register',
  dashboard: '/dashboard',
  store: '/dashboard/store',
  products: '/dashboard/products',
  productsNew: '/dashboard/products/create',
  productsEdit: (id: number | string) => `/dashboard/products/${id}/edit`,
  orders: '/dashboard/orders',
  orderPicking: (id: number | string) => `/dashboard/orders/${id}/picking`,
  customers: '/dashboard/customers',
  inventory: '/dashboard/inventory',
  inventoryReceive: '/dashboard/inventory/receive',
  categories: '/dashboard/categories',
  reports: '/dashboard/reports',
  settings: '/dashboard/settings',
} as const

