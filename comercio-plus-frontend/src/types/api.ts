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
  is_verified?: boolean
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

export interface ProductCode {
  id?: number
  type: 'barcode' | 'qr' | 'sku'
  value: string
  is_primary?: boolean
}

export interface Product {
  id: number
  name: string
  sku?: string
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
  product_codes?: ProductCode[]
  productCodes?: ProductCode[]
  codes?: ProductCode[]
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

export interface CreditAccountRow {
  id: number
  store_id: number
  customer_id: number
  balance: number | string
  credit_limit: number | string
  status: 'active' | 'suspended'
  customer: {
    id: number
    user: {
      id: number
      name: string
      email: string
      phone?: string | null
    }
  }
}

export interface CreditTransactionRow {
  id: number
  credit_account_id: number
  type: 'charge' | 'payment' | 'adjustment'
  amount: number | string
  balance_after: number | string
  note?: string | null
  created_at: string
}

// ─── Facturación Electrónica DIAN ───

export interface ElectronicDocumentItem {
  id: number
  electronic_document_id: number
  product_id?: number | null
  line_number: number
  code?: string | null
  description: string
  unit_measure: string
  quantity: number
  unit_price: number
  discount: number
  tax_amount: number
  line_total: number
  tax_type: string
  tax_rate: number
}

export interface ElectronicDocumentTax {
  id: number
  electronic_document_id: number
  tax_type: string
  tax_rate: number
  taxable_amount: number
  tax_amount: number
}

export interface ElectronicDocumentLog {
  id: number
  electronic_document_id: number
  user_id?: number | null
  action: string
  status_from?: string | null
  status_to?: string | null
  message?: string | null
  payload?: Record<string, unknown> | null
  ip_address?: string | null
  created_at: string
  user?: { id: number; name: string } | null
}

export type DianStatus = 'draft' | 'pending' | 'approved' | 'rejected' | 'cancelled'
export type DocumentType = 'invoice' | 'credit_note' | 'debit_note'

export interface ElectronicDocument {
  id: number
  store_id: number
  order_id?: number | null
  document_type: DocumentType
  prefix: string
  number: number
  full_number?: string
  cufe?: string | null
  cude?: string | null
  dian_status: DianStatus
  dian_track_id?: string | null
  dian_approved_at?: string | null
  dian_response_message?: string | null
  issuer_nit: string
  issuer_name: string
  issuer_email?: string | null
  issuer_phone?: string | null
  issuer_address?: string | null
  issuer_city?: string | null
  issuer_department?: string | null
  customer_identification_type: string
  customer_identification: string
  customer_name: string
  customer_email?: string | null
  customer_phone?: string | null
  customer_address?: string | null
  customer_city?: string | null
  customer_department?: string | null
  subtotal: number
  tax_total: number
  discount_total: number
  total: number
  currency: string
  payment_method?: string | null
  payment_means?: string | null
  payment_due_date?: string | null
  reference_document_id?: number | null
  notes?: string | null
  metadata?: Record<string, unknown> | null
  created_at: string
  updated_at: string
  items?: ElectronicDocumentItem[]
  taxes?: ElectronicDocumentTax[]
  logs?: ElectronicDocumentLog[]
  reference_document?: ElectronicDocument | null
  referenced_by?: ElectronicDocument[]
}

export interface InvoicingStats {
  total_documents: number
  by_status: Record<DianStatus, number>
  total_invoiced_amount: number
  currency: string
}

export interface StoreVerification {
  id: number
  store_id: number
  status: 'pending' | 'approved' | 'rejected'
  document_url?: string | null
  notes?: string | null
  reviewed_at?: string | null
}
