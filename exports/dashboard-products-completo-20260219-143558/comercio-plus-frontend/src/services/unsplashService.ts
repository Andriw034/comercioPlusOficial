/**
 * unsplashService.ts - Servicio para consumir Unsplash API
 * 
 * Características:
 * - Cache de imágenes
 * - Fallback automático
 * - Rate limiting
 * - Type-safe
 */

interface UnsplashImage {
  id: string
  urls: {
    raw: string
    full: string
    regular: string
    small: string
    thumb: string
  }
  alt_description: string | null
  description: string | null
  user: {
    name: string
    username: string
  }
  width: number
  height: number
}

interface UnsplashSearchResponse {
  total: number
  total_pages: number
  results: UnsplashImage[]
}

// URLs de fallback si falla la API
const FALLBACK_MOTORCYCLE_IMAGES: UnsplashImage[] = [
  {
    id: 'fallback-1',
    urls: {
      raw: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=80',
      full: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=80',
      regular: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=80',
      small: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=640&q=80',
      thumb: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=200&q=80',
    },
    alt_description: 'Motociclista en carretera',
    description: 'Motorcycle rider on highway',
    user: { name: 'Unsplash', username: 'unsplash' },
    width: 1920,
    height: 1080,
  },
  {
    id: 'fallback-2',
    urls: {
      raw: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=80',
      full: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=80',
      regular: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=80',
      small: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=640&q=80',
      thumb: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=200&q=80',
    },
    alt_description: 'Moto deportiva',
    description: 'Sport motorcycle',
    user: { name: 'Unsplash', username: 'unsplash' },
    width: 1920,
    height: 1080,
  },
  {
    id: 'fallback-3',
    urls: {
      raw: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=80',
      full: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=80',
      regular: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=80',
      small: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=640&q=80',
      thumb: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=200&q=80',
    },
    alt_description: 'Motociclista en ruta',
    description: 'Motorcycle on scenic route',
    user: { name: 'Unsplash', username: 'unsplash' },
    width: 1920,
    height: 1080,
  },
  {
    id: 'fallback-4',
    urls: {
      raw: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=80',
      full: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=80',
      regular: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=80',
      small: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=640&q=80',
      thumb: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=200&q=80',
    },
    alt_description: 'Moto custom',
    description: 'Custom motorcycle',
    user: { name: 'Unsplash', username: 'unsplash' },
    width: 1920,
    height: 1080,
  },
  {
    id: 'fallback-5',
    urls: {
      raw: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1920&q=80',
      full: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1920&q=80',
      regular: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1920&q=80',
      small: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=640&q=80',
      thumb: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=200&q=80',
    },
    alt_description: 'Moto clásica',
    description: 'Classic motorcycle',
    user: { name: 'Unsplash', username: 'unsplash' },
    width: 1920,
    height: 1080,
  },
]

class UnsplashService {
  private accessKey: string | undefined
  private baseUrl = 'https://api.unsplash.com'
  private cache: Map<string, { data: UnsplashImage[]; timestamp: number }> = new Map()
  private cacheExpiry = 1000 * 60 * 60 // 1 hora

  constructor() {
    this.accessKey = import.meta.env.VITE_UNSPLASH_ACCESS_KEY
  }

  /**
   * Buscar imágenes en Unsplash
   * @param query - Término de búsqueda (ej: "motorcycle", "motorcycle racing")
   * @param perPage - Cantidad de imágenes (default: 10)
   */
  async searchPhotos(query: string, perPage: number = 10): Promise<UnsplashImage[]> {
    // Verificar cache
    const cacheKey = `search:${query}:${perPage}`
    const cached = this.getFromCache(cacheKey)
    if (cached) {
      console.log('📦 Usando imágenes desde cache')
      return cached
    }

    // Si no hay API key, usar fallback
    if (!this.accessKey) {
      console.warn('⚠️ No se encontró VITE_UNSPLASH_ACCESS_KEY, usando imágenes de fallback')
      return FALLBACK_MOTORCYCLE_IMAGES.slice(0, perPage)
    }

    try {
      const url = new URL(`${this.baseUrl}/search/photos`)
      url.searchParams.append('query', query)
      url.searchParams.append('per_page', perPage.toString())
      url.searchParams.append('orientation', 'landscape')
      url.searchParams.append('client_id', this.accessKey)

      console.log('🔍 Buscando imágenes en Unsplash:', query)

      const controller = new AbortController()
      const timeoutId = setTimeout(() => controller.abort(), 10000) // 10s timeout

      const response = await fetch(url.toString(), {
        signal: controller.signal,
      })

      clearTimeout(timeoutId)

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const data: UnsplashSearchResponse = await response.json()

      console.log(`✅ Cargadas ${data.results.length} imágenes`)

      // Guardar en cache
      this.saveToCache(cacheKey, data.results)

      return data.results
    } catch (error) {
      console.error('❌ Error al cargar imágenes de Unsplash:', error)
      console.log('🔄 Usando imágenes de fallback')
      return FALLBACK_MOTORCYCLE_IMAGES.slice(0, perPage)
    }
  }

  /**
   * Obtener imágenes aleatorias
   * @param query - Término de búsqueda
   * @param count - Cantidad de imágenes (default: 10)
   */
  async getRandomPhotos(query: string, count: number = 10): Promise<UnsplashImage[]> {
    const cacheKey = `random:${query}:${count}`
    const cached = this.getFromCache(cacheKey)
    if (cached) return cached

    if (!this.accessKey) {
      return this.shuffleArray(FALLBACK_MOTORCYCLE_IMAGES).slice(0, count)
    }

    try {
      const url = new URL(`${this.baseUrl}/photos/random`)
      url.searchParams.append('query', query)
      url.searchParams.append('count', count.toString())
      url.searchParams.append('orientation', 'landscape')
      url.searchParams.append('client_id', this.accessKey)

      const response = await fetch(url.toString())

      if (!response.ok) throw new Error(`HTTP ${response.status}`)

      const data: UnsplashImage[] = await response.json()

      this.saveToCache(cacheKey, data)

      return data
    } catch (error) {
      console.error('Error al cargar imágenes aleatorias:', error)
      return this.shuffleArray(FALLBACK_MOTORCYCLE_IMAGES).slice(0, count)
    }
  }

  /**
   * Obtener imágenes de fallback (sin API)
   */
  getFallbackImages(): UnsplashImage[] {
    return FALLBACK_MOTORCYCLE_IMAGES
  }

  /**
   * Limpiar cache
   */
  clearCache(): void {
    this.cache.clear()
    console.log('🗑️ Cache limpiado')
  }

  /**
   * Precargar imagen (para smooth transitions)
   */
  preloadImage(url: string): Promise<void> {
    return new Promise((resolve, reject) => {
      const img = new Image()
      img.onload = () => resolve()
      img.onerror = reject
      img.src = url
    })
  }

  /**
   * Precargar múltiples imágenes
   */
  async preloadImages(images: UnsplashImage[]): Promise<void> {
    const promises = images.map((img) => this.preloadImage(img.urls.regular))
    await Promise.all(promises)
    console.log(`✅ Precargadas ${images.length} imágenes`)
  }

  // Métodos privados

  private getFromCache(key: string): UnsplashImage[] | null {
    const cached = this.cache.get(key)
    if (!cached) return null

    const now = Date.now()
    if (now - cached.timestamp > this.cacheExpiry) {
      this.cache.delete(key)
      return null
    }

    return cached.data
  }

  private saveToCache(key: string, data: UnsplashImage[]): void {
    this.cache.set(key, {
      data,
      timestamp: Date.now(),
    })
  }

  private shuffleArray<T>(array: T[]): T[] {
    const shuffled = [...array]
    for (let i = shuffled.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
    }
    return shuffled
  }
}

// Singleton instance
export const unsplashService = new UnsplashService()

// Export type
export type { UnsplashImage }
