import API from '@/lib/api'
import { MOTO_HERO_IMAGES } from '@/constants/motoHeroImages'

declare global {
  interface Window {
    IMAGE_API_URL?: string
  }
}

export type HomeImageItem = {
  url: string
  title: string
}

export type HomeImageSource = 'api' | 'cache' | 'fallback' | 'empty'

const HOME_IMAGES_CACHE_KEY = 'cp_home_images_cache_v1'
const HOME_IMAGES_CACHE_TTL_MS = 10 * 60 * 1000
const HOME_IMAGES_TIMEOUT_MS = 7000
const DEFAULT_HOME_IMAGES_ENDPOINT = '/hero-images'

const FALLBACK_HOME_IMAGES: HomeImageItem[] = MOTO_HERO_IMAGES.map((image) => ({
  url: image.url,
  title: image.title,
}))

type CachedHomeImages = {
  ts: number
  images: HomeImageItem[]
}

const isRecord = (value: unknown): value is Record<string, unknown> =>
  Boolean(value) && typeof value === 'object' && !Array.isArray(value)

const toText = (value: unknown): string => {
  if (typeof value === 'string') return value.trim()
  if (typeof value === 'number' && Number.isFinite(value)) return String(value)
  return ''
}

const pickImageUrl = (value: Record<string, unknown>): string => {
  const urls = isRecord(value.urls) ? value.urls : null
  const candidates = [
    value.url,
    value.image,
    value.src,
    value.path,
    value.cover,
    value.cover_url,
    value.image_url,
    urls?.regular,
    urls?.small,
    urls?.full,
  ]

  for (const candidate of candidates) {
    const normalized = toText(candidate)
    if (normalized) return normalized
  }

  return ''
}

const pickImageTitle = (value: Record<string, unknown>, index: number): string => {
  const candidates = [value.title, value.name, value.alt, value.alt_description]
  for (const candidate of candidates) {
    const normalized = toText(candidate)
    if (normalized) return normalized
  }
  return `Imagen ${index + 1}`
}

const sanitizeImages = (images: HomeImageItem[]): HomeImageItem[] =>
  images
    .map((image) => ({
      url: toText(image.url),
      title: toText(image.title) || 'Imagen',
    }))
    .filter((image) => image.url.length > 0)
    .slice(0, 8)

const mergeWithMotoPreset = (images: HomeImageItem[]): HomeImageItem[] => {
  const motoPreset = sanitizeImages(FALLBACK_HOME_IMAGES)
  const apiImages = sanitizeImages(images)
  const merged: HomeImageItem[] = [...motoPreset]

  const known = new Set(motoPreset.map((item) => item.url))
  for (const image of apiImages) {
    if (!known.has(image.url)) {
      merged.push(image)
      known.add(image.url)
    }
  }

  return sanitizeImages(merged)
}

export const normalizeImages = (response: unknown): HomeImageItem[] => {
  const source = Array.isArray(response)
    ? response
    : isRecord(response) && Array.isArray(response.data)
      ? response.data
      : isRecord(response) && Array.isArray(response.images)
        ? response.images
        : []

  const normalized = source
    .map((entry, index) => {
      if (typeof entry === 'string') {
        return { url: entry.trim(), title: `Imagen ${index + 1}` }
      }

      if (!isRecord(entry)) return null
      const url = pickImageUrl(entry)
      if (!url) return null

      return {
        url,
        title: pickImageTitle(entry, index),
      }
    })
    .filter((entry): entry is HomeImageItem => entry !== null)

  return sanitizeImages(normalized)
}

const readCacheRaw = (): CachedHomeImages | null => {
  if (typeof window === 'undefined') return null

  try {
    const raw = sessionStorage.getItem(HOME_IMAGES_CACHE_KEY)
    if (!raw) return null
    const parsed = JSON.parse(raw) as unknown
    if (!isRecord(parsed)) return null
    const ts = Number(parsed.ts)
    if (!Number.isFinite(ts)) return null
    const images = Array.isArray(parsed.images) ? normalizeImages(parsed.images) : []
    return { ts, images }
  } catch {
    return null
  }
}

export const readCachedHomeImages = (): HomeImageItem[] => {
  const cached = readCacheRaw()
  if (!cached) return []
  if (Date.now() - cached.ts > HOME_IMAGES_CACHE_TTL_MS) return []
  return cached.images
}

export const writeCachedHomeImages = (images: HomeImageItem[]): void => {
  if (typeof window === 'undefined') return
  try {
    const payload: CachedHomeImages = { ts: Date.now(), images: sanitizeImages(images) }
    sessionStorage.setItem(HOME_IMAGES_CACHE_KEY, JSON.stringify(payload))
  } catch {
    // Ignore storage quota / privacy mode errors.
  }
}

export const resolveHomeImagesEndpoint = (): string => {
  if (typeof window !== 'undefined' && typeof window.IMAGE_API_URL === 'string') {
    const fromWindow = window.IMAGE_API_URL.trim()
    if (fromWindow.length > 0) return fromWindow
  }

  const fromEnv = String(import.meta.env.VITE_IMAGE_API_URL || '').trim()
  if (fromEnv.length > 0) return fromEnv

  return DEFAULT_HOME_IMAGES_ENDPOINT
}

export const fetchHomeImages = async (endpoint = resolveHomeImagesEndpoint()): Promise<HomeImageItem[]> => {
  const controller = new AbortController()
  const timer = setTimeout(() => controller.abort(), HOME_IMAGES_TIMEOUT_MS)

  try {
    const response = await fetch(endpoint, {
      method: 'GET',
      headers: { Accept: 'application/json' },
      signal: controller.signal,
      credentials: 'omit',
    })

    if (!response.ok) {
      throw new Error(`HTTP_${response.status}`)
    }

    const payload = await response.json()
    return normalizeImages(payload)
  } catch {
    if (endpoint.startsWith('/')) {
      const { data } = await API.get(endpoint, { timeout: HOME_IMAGES_TIMEOUT_MS })
      return normalizeImages(data)
    }
    throw new Error('HOME_IMAGES_FETCH_FAILED')
  } finally {
    clearTimeout(timer)
  }
}

export const loadHomeImages = async (): Promise<{ images: HomeImageItem[]; source: HomeImageSource }> => {
  try {
    const images = await fetchHomeImages()
    if (images.length > 0) {
      const merged = mergeWithMotoPreset(images)
      writeCachedHomeImages(merged)
      return { images: merged, source: 'api' }
    }
  } catch {
    // fallback handled below
  }

  const cached = readCachedHomeImages()
  if (cached.length > 0) {
    return { images: mergeWithMotoPreset(cached), source: 'cache' }
  }

  if (FALLBACK_HOME_IMAGES.length > 0) {
    return { images: [...FALLBACK_HOME_IMAGES], source: 'fallback' }
  }

  return { images: [], source: 'empty' }
}

export const homeImagesFallback = [...FALLBACK_HOME_IMAGES]
