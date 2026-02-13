export interface Category {
  id: number
  name: string
  description?: string
}

export interface Store {
  id: number
  name: string
  slug?: string
  description?: string
  whatsapp?: string
  support_email?: string
  facebook?: string
  instagram?: string
  address?: string
  phone?: string
  location?: { city?: string }
  rating?: number
  reviews_count?: number
  logo?: string
  cover?: string
  logo_path?: string
  cover_path?: string
  background_url?: string
  background_path?: string
  logo_url?: string
  cover_url?: string
  is_visible?: boolean
}

export interface Product {
  id: number
  name: string
  slug?: string
  description?: string
  price: number
  stock: number
  status?: string
  category_id?: number
  store_id?: number
  image_url?: string
  image?: string
  rating?: number
  average_rating?: number
  reviews_count?: number
  category?: Category
  store?: Store
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page?: number
  last_page?: number
  per_page?: number
  total?: number
}

export interface CustomerRow {
  id: number
  store_id: number
  user_id: number
  first_visited_at: string
  last_visited_at?: string | null
  last_order_at?: string | null
  total_orders: number
  total_spent: number | string
  user: {
    id: number
    name: string
    email: string
    phone?: string | null
  }
}

export interface MerchantCustomersStats {
  total_customers: number
  new_this_month: number
  with_orders: number
  total_revenue: number
}
