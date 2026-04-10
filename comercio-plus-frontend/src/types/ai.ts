export interface PartResult {
  part_reference: string
  part_type: string
  part_brand: string
  part_description: string
  motorcycle_brand: string
  motorcycle_model: string
  year_from: number
  year_to: number
  notes: string | null
}

export interface SearchResponse {
  query: string
  count: number
  results: PartResult[]
}

export interface Message {
  id: string
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
  parts?: PartResult[]
}

export type Plan = 'FREE' | 'PRO' | 'BUSINESS'

export interface UsageInfo {
  used: number
  limit: number
  plan: Plan
}
