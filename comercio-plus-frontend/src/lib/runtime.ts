const stripTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const rawApiUrl = stripTrailingSlash((import.meta.env.VITE_API_URL || '').trim())
const rawApiOrigin = stripTrailingSlash((import.meta.env.VITE_API_ORIGIN || '').trim())
const rawSanctumUrl = stripTrailingSlash((import.meta.env.VITE_SANCTUM_URL || '').trim())

const fallbackApiUrl = import.meta.env.DEV ? 'http://127.0.0.1:8000/api' : '/api'

export const API_BASE_URL = rawApiUrl || fallbackApiUrl

const inferredOrigin = API_BASE_URL.startsWith('http')
  ? stripTrailingSlash(API_BASE_URL.replace(/\/api\/?$/, ''))
  : ''

export const API_ORIGIN = rawApiOrigin || inferredOrigin
export const SANCTUM_BASE_URL = rawSanctumUrl || inferredOrigin

