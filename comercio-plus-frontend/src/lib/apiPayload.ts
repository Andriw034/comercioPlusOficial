type ApiEnvelope<T> = {
  message?: string
  data?: T
  meta?: Record<string, unknown>
}

export const getApiPayload = <T>(response: any, fallback: T): T => {
  const raw = response?.data
  if (raw && typeof raw === 'object' && Object.prototype.hasOwnProperty.call(raw, 'data')) {
    return (raw as ApiEnvelope<T>).data ?? fallback
  }

  return (raw as T) ?? fallback
}

export const getApiMeta = <T extends Record<string, unknown> = Record<string, unknown>>(response: any): T => {
  const raw = response?.data
  if (raw && typeof raw === 'object' && raw.meta && typeof raw.meta === 'object') {
    return raw.meta as T
  }

  return {} as T
}
