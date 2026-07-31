/** Producto de la tienda que coincide con una referencia de compatibilidad. */
export interface InventoryMatch {
  producto_id: number
  nombre: string
  precio: number
  stock: number
  disponible: boolean
}

export interface Compatibility {
  referencia: string
  marca: string
  tipo: string
  tipo_label: string
  descripcion: string | null
  moto: string
  anios: string
  notas: string | null
  en_inventario: InventoryMatch | null
}

export interface Correction {
  escribiste: string
  entendimos: string
}

export interface Interpretation {
  marca: string | null
  modelo: string | null
  anio: number | null
  tipo_pieza: string | null
  tipo_pieza_label: string | null
  correcciones_aplicadas: Correction[]
}

/**
 * Que tan lejos quedo el resultado de lo que se pregunto. Cuando no es
 * `moto_exacta`, la UI debe mostrar el `aviso`: son piezas de otra moto o de
 * otros años, y venderlas sin verificar puede ser un error.
 */
export type Scope =
  | 'moto_exacta'
  | 'moto_otros_anios'
  | 'moto_otras_piezas'
  | 'otras_motos_de_la_marca'
  | 'otras_motos'
  | 'sin_resultados'

export type NoResultsReason =
  | 'moto_desconocida'
  | 'sin_datos_de_esa_moto'
  | 'sin_datos_de_esa_pieza_para_esa_moto'
  | null

export interface Suggestion {
  tipo: string
  label: string
}

export interface AssistantResult {
  interpretacion: Interpretation
  alcance: Scope
  aviso: string | null
  compatibilidades: Compatibility[]
  sugerencias: Suggestion[]
  sin_resultados_por: NoResultsReason
  motos_con_datos: string[]
}

export interface Message {
  id: string
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
  /** Resultado estructurado; se renderiza como tarjetas, no como texto. */
  result?: AssistantResult
}

export type Plan = 'FREE' | 'PRO' | 'BUSINESS'

export interface UsageInfo {
  used: number
  limit: number
  plan: Plan
}
