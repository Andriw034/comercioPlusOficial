const CLOUDINARY_HOST_RE = /(^|\.)res\.cloudinary\.com$/i

const splitUrl = (url: string) => {
  const hashIndex = url.indexOf('#')
  const queryIndex = url.indexOf('?')
  const cutIndex =
    hashIndex >= 0 && queryIndex >= 0
      ? Math.min(hashIndex, queryIndex)
      : hashIndex >= 0
        ? hashIndex
        : queryIndex

  if (cutIndex === -1) {
    return { base: url, suffix: '' }
  }

  return {
    base: url.slice(0, cutIndex),
    suffix: url.slice(cutIndex),
  }
}

export const isCloudinaryUrl = (url?: string | null): boolean => {
  if (!url) return false
  try {
    const parsed = new URL(url)
    return CLOUDINARY_HOST_RE.test(parsed.hostname)
  } catch {
    return false
  }
}

export const applyCloudinaryTransform = (url: string, transformation: string): string => {
  if (!url || !transformation || !isCloudinaryUrl(url)) return url

  const { base, suffix } = splitUrl(url)
  const marker = '/upload/'
  const markerIndex = base.indexOf(marker)

  if (markerIndex === -1) return url

  const prefix = base.slice(0, markerIndex + marker.length)
  const rest = base.slice(markerIndex + marker.length)
  const normalizedRest = rest.replace(/^\/+/, '')

  return `${prefix}${transformation}/${normalizedRest}${suffix}`
}

export const IMAGE_TRANSFORMS = {
  logo: 'f_auto,q_auto,c_pad,b_white,w_512,h_512',
  cover21x9: 'f_auto,q_auto,c_fill,g_auto,w_1600,h_686',
  cover16x9: 'f_auto,q_auto,c_fill,g_auto,w_1600,h_900',
  cardCover: 'f_auto,q_auto,c_fill,g_auto,w_960,h_540',
} as const
