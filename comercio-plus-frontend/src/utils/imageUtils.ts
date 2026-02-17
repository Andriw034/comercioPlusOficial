interface ImageDimensions {
  width: number
  height: number
}

type ImageType = 'logo' | 'cover' | 'product'

const IMAGE_STANDARDS = {
  logo: { width: 512, height: 512, maxSize: 2 * 1024 * 1024 },
  cover: { width: 1920, height: 400, maxSize: 5 * 1024 * 1024 },
  product: { width: 800, height: 800, maxSize: 3 * 1024 * 1024 },
} as const

export async function getImageDimensions(file: File): Promise<ImageDimensions> {
  return new Promise((resolve, reject) => {
    const image = new Image()
    const objectUrl = URL.createObjectURL(file)

    image.onload = () => {
      URL.revokeObjectURL(objectUrl)
      resolve({ width: image.width, height: image.height })
    }

    image.onerror = () => {
      URL.revokeObjectURL(objectUrl)
      reject(new Error('No se pudo leer la imagen.'))
    }

    image.src = objectUrl
  })
}

export async function generatePreview(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onloadend = () => resolve((reader.result as string) || '')
    reader.onerror = () => reject(new Error('No se pudo generar la vista previa.'))

    reader.readAsDataURL(file)
  })
}

export async function validateImage(
  file: File,
  type: ImageType
): Promise<{ valid: boolean; error?: string }> {
  const standard = IMAGE_STANDARDS[type]

  if (!file.type.startsWith('image/')) {
    return { valid: false, error: 'El archivo debe ser una imagen (PNG, JPG, WEBP o AVIF).' }
  }

  if (file.size > standard.maxSize) {
    return {
      valid: false,
      error: `El archivo supera ${standard.maxSize / (1024 * 1024)}MB.`,
    }
  }

  try {
    const { width, height } = await getImageDimensions(file)
    if (width < standard.width / 2 || height < standard.height / 2) {
      return {
        valid: false,
        error: `Resolucion muy baja. Minimo recomendado: ${standard.width / 2}x${standard.height / 2}px.`,
      }
    }
  } catch (error) {
    return {
      valid: false,
      error: error instanceof Error ? error.message : 'No se pudo validar la imagen.',
    }
  }

  return { valid: true }
}
