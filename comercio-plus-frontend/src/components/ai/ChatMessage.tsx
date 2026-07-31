import { AlertTriangle, CheckCircle2, PackageX, Wand2 } from 'lucide-react'
import { formatPrice } from '@/lib/format'
import type { Compatibility, Message } from '@/types/ai'

interface ChatMessageProps {
  message: Message
}

function InventoryBadge({ item }: { item: Compatibility }) {
  if (item.en_inventario === null) {
    return (
      <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500">
        <PackageX className="h-3 w-3" />
        No esta en tu inventario
      </span>
    )
  }

  const { disponible, stock, precio } = item.en_inventario

  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium ${
        disponible ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
      }`}
    >
      <CheckCircle2 className="h-3 w-3" />
      {disponible ? `${stock} en stock` : 'Sin stock'} &middot; {formatPrice(precio)}
    </span>
  )
}

function CompatibilityCard({ item }: { item: Compatibility }) {
  return (
    <div className="rounded-xl border border-slate-200 bg-white p-3">
      <div className="flex items-start justify-between gap-2">
        <div className="min-w-0">
          <p className="text-sm font-semibold text-slate-900">
            {item.marca} {item.referencia}
          </p>
          <p className="text-xs text-slate-500">{item.tipo_label}</p>
        </div>
        <span className="flex-shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">
          {item.anios}
        </span>
      </div>

      <p className="mt-1 text-xs text-slate-600">{item.moto}</p>
      {item.notas && <p className="mt-1 text-xs italic text-slate-400">{item.notas}</p>}

      <div className="mt-2">
        <InventoryBadge item={item} />
      </div>
    </div>
  )
}

export default function ChatMessage({ message }: ChatMessageProps) {
  const isUser = message.role === 'user'
  const result = message.result

  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'}`}>
      {!isUser && (
        <div className="mr-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-comercioplus-600 text-xs font-bold text-white">
          AI
        </div>
      )}

      <div className="max-w-[85%] sm:max-w-[75%]">
        <div
          className={`rounded-2xl px-4 py-3 text-sm leading-relaxed ${
            isUser
              ? 'rounded-br-md bg-comercioplus-600 text-white'
              : 'rounded-bl-md bg-slate-100 text-slate-800'
          }`}
        >
          {message.content && <p className="whitespace-pre-line">{message.content}</p>}

          {result && (
            <div className="mt-2 space-y-3">
              {/* Que se entendio de la pregunta */}
              {result.interpretacion.correcciones_aplicadas.length > 0 && (
                <div className="flex items-start gap-2 rounded-lg bg-white/70 p-2">
                  <Wand2 className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-comercioplus-600" />
                  <p className="text-[11px] text-slate-600">
                    {result.interpretacion.correcciones_aplicadas.map((c, i) => (
                      <span key={`${c.escribiste}-${i}`}>
                        {i > 0 && ' · '}
                        Entendi &quot;{c.escribiste}&quot; como <strong>{c.entendimos}</strong>
                      </span>
                    ))}
                  </p>
                </div>
              )}

              {/* Aviso cuando el resultado no es de la moto preguntada */}
              {result.aviso && (
                <div className="flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 p-2.5">
                  <AlertTriangle className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-600" />
                  <p className="text-[11px] font-medium leading-snug text-amber-800">{result.aviso}</p>
                </div>
              )}

              {result.compatibilidades.map((item, i) => (
                <CompatibilityCard key={`${item.referencia}-${item.moto}-${i}`} item={item} />
              ))}

              {/* Que mas ofrecerle al cliente */}
              {result.sugerencias.length > 0 && (
                <div className="rounded-lg bg-white/70 p-2.5">
                  <p className="text-[11px] font-semibold text-slate-700">Tambien hay datos de:</p>
                  <p className="mt-0.5 text-[11px] text-slate-600">
                    {result.sugerencias.map(s => s.label).join(' · ')}
                  </p>
                </div>
              )}

              {result.compatibilidades.length > 0 && (
                <div className="flex items-start gap-2 rounded-lg border border-slate-200 bg-white p-2.5">
                  <AlertTriangle className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-slate-400" />
                  <p className="text-[11px] leading-snug text-slate-500">
                    Datos en fase BETA. Confirma la referencia con el catalogo del fabricante antes de vender.
                  </p>
                </div>
              )}
            </div>
          )}
        </div>

        <p className={`mt-1 text-[10px] text-slate-400 ${isUser ? 'text-right' : 'text-left'}`}>
          {message.timestamp.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' })}
        </p>
      </div>

      {isUser && (
        <div className="ml-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold text-white">
          Tu
        </div>
      )}
    </div>
  )
}
