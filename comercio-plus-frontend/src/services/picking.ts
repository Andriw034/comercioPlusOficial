import API from '@/lib/api'

export type PickingSessionMeta = {
  scan_consecutive_failures: number
  fallback_required: boolean
}

export type PickingLine = {
  order_product_id: number
  product_id: number
  product_name: string
  image_url?: string | null
  quantity: number
  qty_picked: number
  qty_packed: number
  qty_missing: number
  pending_qty: number
  codes: Array<{
    type: string
    value: string
    is_primary: boolean
  }>
}

export type PickingContextResponse = {
  message: string
  data: {
    order: {
      id: number
      store_id: number
      status: string
      fulfillment_status: string
      invoice_number?: string | null
      created_at?: string | null
    }
    lines: PickingLine[]
  }
  meta: {
    totals: {
      ordered_units: number
      picked_units: number
      missing_units: number
      pending_units: number
      completion_pct: number
    }
    session: PickingSessionMeta
  }
}

export type ApiDomainError = {
  message: string
  error_code?: string
  errors?: Record<string, string[]>
  meta?: {
    session?: PickingSessionMeta
  }
}

export type PickingEventItem = {
  id: number
  order_id: number
  order_product_id?: number | null
  product_id?: number | null
  product_name?: string | null
  user_name?: string | null
  mode: string
  action: string
  code?: string | null
  qty: number
  error_code?: string | null
  message?: string | null
  status?: string | null
  fulfillment_status?: string | null
  created_at?: string | null
}

export async function getPickingContext(orderId: number | string) {
  const { data } = await API.get<PickingContextResponse>(`/merchant/orders/${orderId}/picking`)
  return data
}

export async function scanPickingCode(orderId: number | string, code: string, qty = 1) {
  const { data } = await API.post(`/merchant/orders/${orderId}/picking/scan`, { code, qty })
  return data
}

export async function manualPickingAction(
  orderId: number | string,
  payload:
    | { action: 'pick_item'; order_product_id: number; qty: number }
    | { action: 'pick_by_code'; code: string; qty?: number }
    | { action: 'mark_missing'; order_product_id: number; qty: number; reason: string }
    | { action: 'add_note'; order_product_id: number; note: string },
) {
  const { data } = await API.post(`/merchant/orders/${orderId}/picking/manual`, payload)
  return data
}

export async function activatePickingFallback(orderId: number | string, reason = 'three_scan_failures') {
  const { data } = await API.post(`/merchant/orders/${orderId}/picking/fallback`, {
    selected_mode: 'manual',
    reason,
  })
  return data
}

export async function completePicking(orderId: number | string, note?: string) {
  const { data } = await API.post(`/merchant/orders/${orderId}/picking/complete`, {
    completion_mode: 'strict',
    note,
  })
  return data
}

export async function resetPicking(orderId: number | string) {
  const { data } = await API.post(`/merchant/orders/${orderId}/picking/reset`, {
    confirm: true,
  })
  return data
}

export async function getRecentPickingEvents(limit = 10) {
  const { data } = await API.get('/merchant/picking/events', {
    params: { limit },
  })

  return data as {
    message: string
    data: PickingEventItem[]
    meta: { count: number; limit: number }
  }
}
