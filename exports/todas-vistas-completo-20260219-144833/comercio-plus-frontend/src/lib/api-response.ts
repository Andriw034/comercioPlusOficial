export function extractList<T>(payload: unknown): T[] {
  if (Array.isArray(payload)) return payload as T[]

  if (!payload || typeof payload !== 'object') return []

  const record = payload as Record<string, unknown>

  if (Array.isArray(record.data)) return record.data as T[]
  if (Array.isArray(record.items)) return record.items as T[]
  if (Array.isArray(record.results)) return record.results as T[]

  const nested = record.data
  if (nested && typeof nested === 'object') {
    const nestedRecord = nested as Record<string, unknown>
    if (Array.isArray(nestedRecord.data)) return nestedRecord.data as T[]
    if (Array.isArray(nestedRecord.items)) return nestedRecord.items as T[]
    if (Array.isArray(nestedRecord.results)) return nestedRecord.results as T[]
  }

  return []
}
