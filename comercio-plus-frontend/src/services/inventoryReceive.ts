import API from '@/lib/api'

export type InventoryMovementItem = {
  id: number
  type: string
  reason: string
  reference?: string | null
  request_id?: string | null
  quantity: number
  stock_after: number
  product_id: number
  product_name?: string | null
  created_by?: string | null
  created_at?: string | null
}

export type InventoryProductItem = {
  id: number
  name: string
  stock: number
  price: number
  slug: string
}

export type ScanInPayload = {
  code: string
  qty: number
  reason?: string
  reference?: string
  request_id?: string
}

export type ScanInErrorResponse = {
  message: string
  error_code?: string
  suggested_action?: string
  data?: {
    code?: string
  }
}

export type CreateFromScanPayload = {
  code: string
  code_type: 'barcode' | 'sku' | 'qr'
  name: string
  category_id?: number
  price?: number
  initial_qty: number
  reason?: string
  reference?: string
  request_id?: string
}

export async function scanInInventory(payload: ScanInPayload) {
  const { data } = await API.post('/merchant/inventory/scan-in', payload)
  return data as {
    message: string
    data: {
      product: InventoryProductItem
      movement: InventoryMovementItem
      idempotent: boolean
    }
    error_code?: string
    suggested_action?: string
  }
}

export async function createFromScan(payload: CreateFromScanPayload) {
  const { data } = await API.post('/merchant/inventory/create-from-scan', payload)
  return data as {
    message: string
    data: {
      product: InventoryProductItem
      movement: InventoryMovementItem
      idempotent: boolean
    }
  }
}

export async function getInventoryMovements(limit = 10, type: string | null = 'purchase') {
  const { data } = await API.get('/merchant/inventory/movements', {
    params: {
      limit,
      ...(type ? { type } : {}),
    },
  })
  return data as {
    message: string
    data: InventoryMovementItem[]
    meta: { count: number; limit: number }
  }
}

export async function getStoreCategories() {
  const { data } = await API.get('/categories')
  return Array.isArray(data) ? data : []
}
