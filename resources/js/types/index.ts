export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface Product {
  id: number
  name: string
  description: string
  price: number
  image?: string
  category_id: number
  store_id: number
  slug: string
  stock: number
  category?: Category
  store?: Store
  created_at: string
  updated_at: string
}

export interface Category {
  id: number
  name: string
  description?: string
  slug: string
  created_at: string
  updated_at: string
}

export interface Store {
  id: number
  name: string
  description?: string
  user_id: number
  slug: string
  logo?: string
  user?: User
  products?: Product[]
  created_at: string
  updated_at: string
}

export interface CartItem {
  id: number
  product_id: number
  quantity: number
  product: Product
}

export interface Cart {
  id: number
  user_id: number
  items: CartItem[]
  total: number
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  user_id: number
  store_id: number
  total: number
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'
  items: OrderItem[]
  created_at: string
  updated_at: string
}

export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  quantity: number
  price: number
  product: Product
}
