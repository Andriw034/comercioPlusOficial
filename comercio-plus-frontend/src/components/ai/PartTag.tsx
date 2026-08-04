import type { JSX } from 'react'
import { formatPrice } from '@/lib/format'
import type { AssistantProduct } from '@/services/aiService'

/**
 * Etiqueta de repuesto, al estilo de la tarjeta de una caja de la estanteria.
 *
 * Estos productos son los que el backend encontro en la base de la tienda: precio y
 * stock reales, no texto del modelo. Antes se pintaban como una nota al pie gris
 * debajo de la prosa, o sea que el dato mas confiable del mensaje se veia como el
 * menos importante. Aca se invierte esa jerarquia.
 *
 * La barra de la izquierda codifica la existencia y no es decoracion:
 *   solida (naranja) = lo tenemos aca, ahora
 *   punteada (gris)  = esta en el catalogo pero no hay
 * El punteado se lee literalmente como "no confirmado".
 *
 * Cifras en monoespaciada tabular para poder comparar precios de un vistazo cuando
 * el asistente devuelve varias opciones.
 */
export default function PartTag({ product }: { product: AssistantProduct }): JSX.Element {
  const hay = Number(product.stock) > 0

  return (
    <li className="flex items-stretch gap-2.5">
      <span
        aria-hidden="true"
        className={
          hay
            ? 'w-[3px] shrink-0 rounded-full bg-[#FF6A00]'
            : 'w-[3px] shrink-0 rounded-full border-l-[3px] border-dashed border-[#B9B4AC]'
        }
      />

      <span className="min-w-0 flex-1 py-0.5">
        <span className="block truncate text-[13px] font-semibold leading-snug text-[#141A22]">
          {product.name}
        </span>

        <span className="mt-0.5 flex items-baseline gap-2 font-mono text-[12px] tabular-nums">
          <span className={hay ? 'font-semibold text-[#FF6A00]' : 'text-[#5C6B7A] line-through'}>
            ${formatPrice(product.price)}
          </span>
          <span className="text-[11px] text-[#5C6B7A]">
            {hay ? `quedan ${product.stock}` : 'sin existencias'}
          </span>
        </span>
      </span>
    </li>
  )
}
