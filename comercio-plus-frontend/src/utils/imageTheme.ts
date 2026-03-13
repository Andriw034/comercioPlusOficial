export type ImageBrightness = 'dark' | 'light'

const STORAGE_KEY = 'cp-image-brightness-cache-v1'
const DARK_THRESHOLD = 142
const MAX_SAMPLES = 48
const memoryCache = new Map<string, ImageBrightness>()

const readPersistedCache = (): Record<string, ImageBrightness> => {
  if (typeof window === 'undefined') return {}
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return {}
    const parsed = JSON.parse(raw) as Record<string, ImageBrightness>
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

const writePersistedCache = (cache: Record<string, ImageBrightness>) => {
  if (typeof window === 'undefined') return
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cache))
  } catch {
    // Ignore storage quota errors.
  }
}

const cacheBrightness = (url: string, brightness: ImageBrightness) => {
  memoryCache.set(url, brightness)
  const persisted = readPersistedCache()
  persisted[url] = brightness
  writePersistedCache(persisted)
}

const readCachedBrightness = (url: string): ImageBrightness | null => {
  const inMemory = memoryCache.get(url)
  if (inMemory) return inMemory

  const persisted = readPersistedCache()[url]
  if (persisted === 'dark' || persisted === 'light') {
    memoryCache.set(url, persisted)
    return persisted
  }

  return null
}

const computeBrightness = (image: HTMLImageElement): ImageBrightness => {
  const canvas = document.createElement('canvas')
  canvas.width = MAX_SAMPLES
  canvas.height = MAX_SAMPLES

  const context = canvas.getContext('2d', { willReadFrequently: true })
  if (!context) return 'dark'

  context.drawImage(image, 0, 0, MAX_SAMPLES, MAX_SAMPLES)
  const imageData = context.getImageData(0, 0, MAX_SAMPLES, MAX_SAMPLES)
  const pixels = imageData.data

  let totalLuminance = 0
  let sampled = 0

  for (let i = 0; i < pixels.length; i += 4) {
    const alpha = pixels[i + 3] / 255
    if (alpha < 0.1) continue

    const r = pixels[i]
    const g = pixels[i + 1]
    const b = pixels[i + 2]
    const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b

    totalLuminance += luminance
    sampled += 1
  }

  if (sampled === 0) return 'dark'
  const average = totalLuminance / sampled
  return average < DARK_THRESHOLD ? 'dark' : 'light'
}

export const getImageBrightness = async (url?: string | null): Promise<ImageBrightness> => {
  if (!url) return 'dark'

  const cached = readCachedBrightness(url)
  if (cached) return cached

  if (typeof window === 'undefined') return 'dark'

  return new Promise<ImageBrightness>((resolve) => {
    const image = new Image()
    image.crossOrigin = 'anonymous'
    image.decoding = 'async'
    image.referrerPolicy = 'no-referrer'

    image.onload = () => {
      try {
        const brightness = computeBrightness(image)
        cacheBrightness(url, brightness)
        resolve(brightness)
      } catch {
        resolve('dark')
      }
    }

    image.onerror = () => resolve('dark')
    image.src = url
  })
}

export const getThemeClassesByBrightness = (brightness: ImageBrightness) => {
  const isDark = brightness === 'dark'
  return {
    textPrimary: isDark ? 'text-white' : 'text-slate-950',
    textSecondary: isDark ? 'text-slate-100/90' : 'text-slate-800',
    textMuted: isDark ? 'text-slate-100/80' : 'text-slate-700',
    icon: isDark ? 'text-white' : 'text-slate-900',
    chip: isDark
      ? 'border-white/30 bg-white/15 text-white'
      : 'border-slate-900/15 bg-white/75 text-slate-900',
    overlay: isDark
      ? 'from-slate-950/30 via-slate-950/45 to-slate-950/72'
      : 'from-slate-950/36 via-slate-950/60 to-slate-950/80',
    buttonPrimary: isDark
      ? 'bg-white text-slate-900 hover:bg-slate-100'
      : 'bg-slate-900 text-white hover:bg-slate-800',
    buttonSecondary: isDark
      ? 'border-white/35 bg-black/20 text-white hover:bg-black/32'
      : 'border-slate-900/20 bg-white/75 text-slate-900 hover:bg-white',
  }
}

export const getStoredHeaderTheme = (storeId?: string | number | null): ImageBrightness | null => {
  if (!storeId || typeof window === 'undefined') return null
  const key = `cp-store-header-theme:${String(storeId)}`
  const value = localStorage.getItem(key)
  return value === 'dark' || value === 'light' ? value : null
}

export const storeHeaderTheme = (storeId: string | number | null | undefined, theme: ImageBrightness) => {
  if (!storeId || typeof window === 'undefined') return
  const key = `cp-store-header-theme:${String(storeId)}`
  localStorage.setItem(key, theme)
}
