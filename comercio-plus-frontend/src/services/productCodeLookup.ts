import API from '@/lib/api'
import type { Product } from '@/types/api'

export type ProductCodeLookupPayload = {
  code: string
  code_type?: 'barcode' | 'qr' | 'sku'
}

export type ProductCodeLookupResponse = {
  message: string
  data: {
    found: boolean
    source?: 'product_codes' | 'legacy_column'
    code?: {
      type: 'barcode' | 'qr' | 'sku'
      value: string
      is_primary: boolean
    }
    product?: Product
  }
  error_code?: 'PRODUCT_NOT_FOUND'
  suggested_action?: 'CREATE_PRODUCT'
}

export async function lookupMerchantProductCode(payload: ProductCodeLookupPayload) {
  const { data } = await API.post('/merchant/products/lookup-code', payload)
  return data as ProductCodeLookupResponse
}

