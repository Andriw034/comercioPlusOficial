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
  image_urls?: string[]
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
