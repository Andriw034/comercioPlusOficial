import type { AssistantResult } from '@/types/ai'

/**
 * Texto para cuando no hay compatibilidades: explica por que no hay y con que
 * motos si tenemos datos.
 *
 * Antes, en este caso, se mostraba un texto fijo con referencias y medidas que
 * parecia un dato verificado sin serlo. Un comerciante puede vender la pieza
 * equivocada por eso, asi que aca no se inventa nada.
 */
export function buildEmptyMessage(result: AssistantResult): string {
  const { interpretacion: i, sin_resultados_por: reason, motos_con_datos: motos } = result

  const entendido = [i.marca, i.modelo, i.anio].filter(Boolean).join(' ')
  const pieza = i.tipo_pieza_label

  let text: string

  if (reason === 'moto_desconocida') {
    text = 'No reconoci ninguna moto en tu pregunta.'
  } else if (reason === 'sin_datos_de_esa_pieza_para_esa_moto') {
    text = `No tengo datos de ${pieza?.toLowerCase() ?? 'esa pieza'} para ${entendido || 'esa moto'}.`
  } else {
    text = `Todavia no tengo compatibilidades cargadas para ${entendido || 'esa moto'}.`
  }

  if (motos.length > 0) {
    const lista = motos.slice(0, 8).join(', ')
    const resto = motos.length > 8 ? ` y ${motos.length - 8} mas` : ''
    text += `\n\nHoy tengo datos verificados de: ${lista}${resto}.`
  }

  return text
}
