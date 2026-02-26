import { API_ORIGIN } from './runtime'

export const formatPrice = (value: number | string | null | undefined) => {
  const num = Number(value ?? 0)
  return new Intl.NumberFormat('es-CO').format(Number.isFinite(num) ? num : 0)
}

export const formatDate = (value: string | Date | null | undefined) => {
  if (!value) return ''
  const date = value instanceof Date ? value : new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

export const resolveMediaUrl = (value?: string | null) => {
  if (!value) return ''
  const isDev = import.meta.env.DEV

  if (/^https?:\/\//i.test(value)) {
    try {
      const parsed = new URL(value)
      const isLocalHost = ['127.0.0.1', 'localhost'].includes(parsed.hostname)
      if (isLocalHost) {
        if (isDev) return `${parsed.pathname}${parsed.search}`
        if (API_ORIGIN) return `${API_ORIGIN}${parsed.pathname}${parsed.search}`
      }
    } catch {
      return value
    }
    return value
  }
  if (value.startsWith('//')) return `https:${value}`
  if (value.startsWith('/')) {
    if (isDev) return value
    return API_ORIGIN ? `${API_ORIGIN}${value}` : value
  }

  if (isDev) return `/${value}`
  return API_ORIGIN ? `${API_ORIGIN}/${value}` : `/${value}`
}
