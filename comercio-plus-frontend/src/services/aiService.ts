import type { SearchResponse } from '@/types/ai'

const API_URL = 'http://localhost:5000'

export async function searchParts(query: string): Promise<SearchResponse> {
  const response = await fetch(`${API_URL}/search`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ query }),
  })

  if (!response.ok) {
    throw new Error('Error buscando repuestos')
  }

  return response.json() as Promise<SearchResponse>
}

export interface MarketplaceProduct {
  title: string
  price: number
  currency: string
  permalink: string
  thumbnail: string
  condition: string
  seller: string
}

export interface MarketplaceResponse {
  query: string
  count: number
  products: MarketplaceProduct[]
  source: string
}

export async function searchMarketplace(query: string): Promise<MarketplaceResponse> {
  const response = await fetch(`${API_URL}/search-marketplace`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ query }),
  })

  if (!response.ok) {
    throw new Error('Error buscando en marketplace')
  }

  return response.json() as Promise<MarketplaceResponse>
}

export async function checkHealth(): Promise<boolean> {
  try {
    const response = await fetch(`${API_URL}/health`)
    const data = (await response.json()) as { status: string }
    return data.status === 'ok'
  } catch {
    return false
  }
}
