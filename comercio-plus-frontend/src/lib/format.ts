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
  if (/^https?:\/\//i.test(value)) return value
  if (value.startsWith('//')) return `https:${value}`
  if (value.startsWith('/')) return API_ORIGIN ? `${API_ORIGIN}${value}` : value
  return API_ORIGIN ? `${API_ORIGIN}/${value}` : `/${value}`
}
