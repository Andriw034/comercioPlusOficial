import { useState } from 'react'
import { AlertTriangle } from 'lucide-react'
import type { Message } from '@/types/ai'

interface ChatMessageProps {
  message: Message
}

const PART_TYPE_LABELS: Record<string, string> = {
  banda: 'Banda de transmision',
  pastilla_freno: 'Pastilla de freno',
  bujia: 'Bujia',
  cadena: 'Cadena',
  filtro_aceite: 'Filtro de aceite',
  kit_arrastre: 'Kit de arrastre',
  caucho_carburador: 'Caucho carburador',
  pinon_motor: 'Pinon motor',
  catalina: 'Catalina',
}

function formatPartType(type: string): string {
  return PART_TYPE_LABELS[type] ?? type
}

export default function ChatMessage({ message }: ChatMessageProps) {
  const isUser = message.role === 'user'
  const [reported, setReported] = useState(false)
  const hasParts = !isUser && message.parts && message.parts.length > 0

  const handleReport = () => {
    setReported(true)
    console.info('[AI Report]', {
      messageId: message.id,
      query: message.content,
      partsCount: message.parts?.length ?? 0,
      parts: message.parts?.map(p => p.part_reference),
      timestamp: new Date().toISOString(),
    })
  }

  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'}`}>
      {/* Avatar */}
      {!isUser && (
        <div className="mr-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-comercioplus-600 text-xs font-bold text-white">
          AI
        </div>
      )}

      <div className={`max-w-[85%] sm:max-w-[75%] ${isUser ? '' : ''}`}>
        {/* Bubble */}
        <div
          className={`rounded-2xl px-4 py-3 text-sm leading-relaxed ${
            isUser
              ? 'rounded-br-md bg-comercioplus-600 text-white'
              : 'rounded-bl-md bg-slate-100 text-slate-800'
          }`}
        >
          <p className="whitespace-pre-line">{message.content}</p>

          {/* Part cards */}
          {hasParts && (
            <div className="mt-3 space-y-2">
              {message.parts!.map((part, i) => (
                <div
                  key={`${part.part_reference}-${i}`}
                  className="rounded-xl border border-slate-200 bg-white p-3"
                >
                  <div className="flex items-start justify-between gap-2">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold text-slate-900">
                        {part.part_brand} {part.part_reference}
                      </p>
                      <p className="text-xs text-slate-500">{formatPartType(part.part_type)}</p>
                    </div>
                    <span className="flex-shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                      {part.year_from}-{part.year_to}
                    </span>
                  </div>
                  <p className="mt-1 text-xs text-slate-600">
                    {part.motorcycle_brand} {part.motorcycle_model}
                  </p>
                  {part.notes && (
                    <p className="mt-1 text-xs italic text-slate-400">{part.notes}</p>
                  )}
                </div>
              ))}

              {/* Disclaimer */}
              <div className="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-2.5">
                <AlertTriangle className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-500" />
                <p className="text-[11px] leading-snug text-amber-700">
                  Datos en fase BETA. Siempre confirma la referencia con el catalogo del fabricante antes de vender.
                </p>
              </div>

              {/* Report button */}
              {reported ? (
                <p className="text-center text-[11px] text-emerald-600">Reporte enviado. Gracias.</p>
              ) : (
                <button
                  onClick={handleReport}
                  className="w-full rounded-lg border border-slate-200 py-1.5 text-[11px] text-slate-400 transition-colors hover:border-red-300 hover:text-red-500"
                >
                  Reportar error en estos datos
                </button>
              )}
            </div>
          )}
        </div>

        {/* Timestamp */}
        <p className={`mt-1 text-[10px] text-slate-400 ${isUser ? 'text-right' : 'text-left'}`}>
          {message.timestamp.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' })}
        </p>
      </div>

      {/* User avatar */}
      {isUser && (
        <div className="ml-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold text-white">
          Tu
        </div>
      )}
    </div>
  )
}
