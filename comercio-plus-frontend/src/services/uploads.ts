import axios from 'axios'
import API from './api'

const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024
const ALLOWED_IMAGE_MIME_TYPES = new Set([
  'image/jpeg',
  'image/png',
  'image/webp',
  'image/avif',
])

export interface UploadPayload {
  url: string
  public_id: string
  width: number | null
  height: number | null
}

interface UploadResponse {
  data: UploadPayload
  message: string
}

const validateImageFile = (file: File) => {
  if (!ALLOWED_IMAGE_MIME_TYPES.has(file.type)) {
    throw new Error('Formato no permitido. Usa JPG, PNG, WEBP o AVIF.')
  }

  if (file.size > MAX_IMAGE_SIZE_BYTES) {
    throw new Error('La imagen supera 5MB. Selecciona un archivo mas liviano.')
  }
}

const resolveUploadError = (error: unknown): string => {
  if (axios.isAxiosError(error)) {
    const status = error.response?.status
    const data = error.response?.data as any

    if (status === 422) {
      const firstError = data?.errors ? Object.values(data.errors).flat()[0] : null
      return firstError || data?.message || 'Archivo invalido.'
    }

    if (status === 401 || status === 403) {
      return 'No autorizado para subir imagenes.'
    }

    if (status && status >= 500) {
      return data?.message || 'Error del servidor al subir la imagen.'
    }

    return data?.message || error.message || 'No se pudo subir la imagen.'
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'No se pudo subir la imagen.'
}

const uploadImage = async (endpoint: string, file: File): Promise<UploadPayload> => {
  validateImageFile(file)

  const formData = new FormData()
  formData.append('image', file)

  try {
    const response = await API.post<UploadResponse>(endpoint, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    return response.data.data
  } catch (error) {
    throw new Error(resolveUploadError(error))
  }
}

export const uploadProductImage = (file: File) => uploadImage('/uploads/products', file)
export const uploadStoreLogo = (file: File) => uploadImage('/uploads/stores/logo', file)
export const uploadStoreCover = (file: File) => uploadImage('/uploads/stores/cover', file)
export const uploadProfilePhoto = (file: File) => uploadImage('/uploads/profiles/photo', file)

