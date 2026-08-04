import type { JSX } from 'react'

/**
 * Tuerca hexagonal: la marca del asistente.
 *
 * Reemplaza al globo de dialogo, que es el icono mas generico que existe para un
 * chat. La tuerca es el objeto universal de un taller, se lee a 20px y hace que el
 * boton no se confunda con el de soporte de cualquier otra pagina.
 */
export default function NutIcon({ className = '' }: { className?: string }): JSX.Element {
  return (
    <svg viewBox="0 0 24 24" className={className} fill="none" aria-hidden="true">
      <path
        d="M12 2.6 20.1 7.3v9.4L12 21.4 3.9 16.7V7.3Z"
        stroke="currentColor"
        strokeWidth="1.9"
        strokeLinejoin="round"
      />
      <circle cx="12" cy="12" r="3.6" stroke="currentColor" strokeWidth="1.9" />
    </svg>
  )
}
