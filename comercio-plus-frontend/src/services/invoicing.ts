import API from '@/lib/api'
import type {
  ElectronicDocument,
  ElectronicDocumentLog,
  InvoicingStats,
  PaginatedResponse,
} from '@/types/api'

export type InvoicingFilters = {
  status?: string
  document_type?: string
  customer?: string
  from?: string
  to?: string
  per_page?: number
  page?: number
}

type ApiListResponse = {
  message: string
  data: ElectronicDocument[]
  meta: PaginatedResponse<never>
}

type ApiDocResponse = {
  message: string
  data: ElectronicDocument
}

type ApiLogsResponse = {
  message: string
  data: ElectronicDocumentLog[]
}

type ApiStatsResponse = {
  message: string
  data: InvoicingStats
}

export async function fetchDocuments(filters: InvoicingFilters = {}): Promise<ApiListResponse> {
  const params = Object.fromEntries(
    Object.entries(filters).filter(([, v]) => v !== undefined && v !== '')
  )
  const res = await API.get('/api/merchant/invoicing', { params })
  return res.data
}

export async function fetchDocument(id: number): Promise<ApiDocResponse> {
  const res = await API.get(`/api/merchant/invoicing/${id}`)
  return res.data
}

export async function createInvoice(payload: Record<string, unknown>): Promise<ApiDocResponse> {
  const res = await API.post('/api/merchant/invoicing', payload)
  return res.data
}

export async function createFromOrder(orderId: number, payload: Record<string, unknown>): Promise<ApiDocResponse> {
  const res = await API.post(`/api/merchant/invoicing/from-order/${orderId}`, payload)
  return res.data
}

export async function updateDocument(id: number, payload: Record<string, unknown>): Promise<ApiDocResponse> {
  const res = await API.put(`/api/merchant/invoicing/${id}`, payload)
  return res.data
}

export async function sendToDAIN(id: number): Promise<ApiDocResponse> {
  const res = await API.post(`/api/merchant/invoicing/${id}/send`)
  return res.data
}

export async function cancelDocument(id: number, reason?: string): Promise<ApiDocResponse> {
  const res = await API.post(`/api/merchant/invoicing/${id}/cancel`, { reason })
  return res.data
}

export async function createCreditNote(id: number, reason: string, items?: Record<string, unknown>[]): Promise<ApiDocResponse> {
  const res = await API.post(`/api/merchant/invoicing/${id}/credit-note`, { reason, items })
  return res.data
}

export async function fetchLogs(id: number): Promise<ApiLogsResponse> {
  const res = await API.get(`/api/merchant/invoicing/${id}/logs`)
  return res.data
}

export async function fetchStats(): Promise<ApiStatsResponse> {
  const res = await API.get('/api/merchant/invoicing/stats')
  return res.data
}
