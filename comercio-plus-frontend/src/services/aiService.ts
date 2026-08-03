import API from '@/lib/api'
import { getApiPayload } from '@/lib/apiPayload'
import type { AssistantResult } from '@/types/ai'

/**
 * Busqueda de compatibilidad de repuestos.
 *
 * Antes esto apuntaba a http://localhost:5000 (el microservicio Python), asi que
 * en produccion el navegador del usuario intentaba conectarse a su propia
 * maquina y siempre fallaba. Ahora usa la API de Laravel por ruta relativa, que
 * Vercel reenvia al backend.
 */
export async function searchParts(query: string, storeId?: number): Promise<AssistantResult> {
  const params = new URLSearchParams({ q: query })
  if (storeId) params.set('store_id', String(storeId))

  const response = await API.get(`/assistant/search?${params.toString()}`)

  return getApiPayload<AssistantResult>(response, {
    interpretacion: {
      marca: null,
      modelo: null,
      anio: null,
      tipo_pieza: null,
      tipo_pieza_label: null,
      correcciones_aplicadas: [],
    },
    alcance: 'sin_resultados',
    aviso: null,
    compatibilidades: [],
    sugerencias: [],
    sin_resultados_por: 'moto_desconocida',
    motos_con_datos: [],
  })
}

/** Producto de la tienda que Claude menciona en su respuesta. */
export interface AssistantProduct {
  id: number
  name: string
  price: string | number
  stock: number
}

/** Respuesta conversacional de Claude (IA real) usando el catalogo de la tienda. */
export interface AssistantAnswer {
  answer: string
  products: AssistantProduct[]
}

/**
 * Pregunta conversacional a Claude. A diferencia de searchParts (buscador
 * estructurado por compatibilidad verificada), aca Claude razona sobre el
 * inventario real de la tienda y responde en lenguaje natural.
 */
export async function askAssistant(question: string, storeId?: number): Promise<AssistantAnswer> {
  const response = await API.post('/assistant/ask', {
    question,
    store_id: storeId,
  })

  return getApiPayload<AssistantAnswer>(response, {
    answer: 'No pude generar una respuesta. Intenta reformular tu pregunta.',
    products: [],
  })
}
