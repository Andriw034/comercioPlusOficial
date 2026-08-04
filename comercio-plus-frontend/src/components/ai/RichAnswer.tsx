import { Fragment, type JSX } from 'react'

/**
 * Muestra la respuesta de la IA con el formato minimo que ella usa.
 *
 * Los modelos escriben en Markdown por costumbre ("**Aceite 20W50:** $25.000") y el
 * chat lo pintaba tal cual, con los asteriscos a la vista. Al cliente de la tienda
 * eso se le ve como un error del sistema.
 *
 * Se soportan a proposito SOLO negrita y vinetas, que es el 99% de lo que aparece en
 * una respuesta de repuestos. No se usa dangerouslySetInnerHTML ni una libreria de
 * Markdown: se arman nodos de React, asi que el texto del modelo NUNCA puede
 * inyectar HTML en la pagina de la tienda.
 */

const NEGRITA = /\*\*(.+?)\*\*/g

/** Convierte los **...** de una linea en <strong>, dejando el resto como texto. */
function conNegritas(linea: string, clave: string): JSX.Element {
  const partes: (string | JSX.Element)[] = []
  let ultimo = 0

  for (const match of linea.matchAll(NEGRITA)) {
    const inicio = match.index ?? 0

    if (inicio > ultimo) partes.push(linea.slice(ultimo, inicio))
    partes.push(<strong key={`${clave}-b${inicio}`}>{match[1]}</strong>)
    ultimo = inicio + match[0].length
  }

  if (ultimo < linea.length) partes.push(linea.slice(ultimo))

  return <Fragment key={clave}>{partes}</Fragment>
}

export default function RichAnswer({ text }: { text: string }): JSX.Element {
  const lineas = text.split('\n')
  const bloques: JSX.Element[] = []
  let vinetas: string[] = []

  const cerrarVinetas = (i: number): void => {
    if (vinetas.length === 0) return

    bloques.push(
      <ul key={`ul-${i}`} className="my-1 list-disc space-y-0.5 pl-4">
        {vinetas.map((v, j) => (
          <li key={j}>{conNegritas(v, `li-${i}-${j}`)}</li>
        ))}
      </ul>,
    )
    vinetas = []
  }

  lineas.forEach((linea, i) => {
    const limpia = linea.trim()
    // "* item", "- item" y "• item" son las tres formas que usan los modelos.
    const vineta = limpia.match(/^[*\-•]\s+(.*)$/)

    if (vineta) {
      vinetas.push(vineta[1])
      return
    }

    cerrarVinetas(i)

    if (limpia === '') return

    bloques.push(
      <p key={`p-${i}`} className="whitespace-pre-wrap">
        {conNegritas(limpia, `p-${i}`)}
      </p>,
    )
  })

  cerrarVinetas(lineas.length)

  return <div className="space-y-1">{bloques}</div>
}
