import { useRef, useState } from 'react'
import { askAssistant, type AssistantProduct, type AssistantTurn } from '@/services/aiService'
import NutIcon from '@/components/ai/NutIcon'
import PartTag from '@/components/ai/PartTag'
import RichAnswer from '@/components/ai/RichAnswer'

interface ChatMessage {
  role: 'user' | 'assistant'
  text: string
  products?: AssistantProduct[]
  /** Los avisos de error no se reenvian como contexto de la conversacion. */
  isError?: boolean
}

interface StoreAiChatProps {
  storeId: number | string
  storeName: string
}

/**
 * Preguntas de arranque, en el idioma del mostrador. Son tocables porque el cliente
 * suele estar con el celular en una mano y la moto en la otra: escribir es el paso
 * que mas gente pierde antes de preguntar.
 */
const EJEMPLOS = [
  '¿Qué le sirve a una Boxer 2018?',
  '¿Qué bujía lleva la NKD 125?',
  '¿Tienen guaya de clutch?',
]

export default function StoreAiChat({ storeId, storeName }: StoreAiChatProps) {
  const [open, setOpen] = useState(false)
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const scrollRef = useRef<HTMLDivElement | null>(null)

  const scrollToBottom = (): void => {
    window.requestAnimationFrame(() => {
      const el = scrollRef.current
      if (el) el.scrollTop = el.scrollHeight
    })
  }

  const sendQuestion = async (texto?: string): Promise<void> => {
    const question = (texto ?? input).trim()
    if (!question || loading) return

    const history: AssistantTurn[] = messages
      .filter((m) => !m.isError)
      .map((m) => ({ role: m.role, content: m.text }))

    setMessages((prev) => [...prev, { role: 'user', text: question }])
    setInput('')
    setLoading(true)
    scrollToBottom()

    try {
      const result = await askAssistant(question, Number(storeId), history)

      setMessages((prev) => [
        ...prev,
        {
          role: 'assistant',
          text: result.answer,
          products: result.products,
        },
      ])
    } catch (error: unknown) {
      const message =
        (error as { response?: { data?: { message?: string } } })?.response?.data?.message ??
        'No pude responder en este momento. Intenta de nuevo en un momento.'
      setMessages((prev) => [...prev, { role: 'assistant', text: message, isError: true }])
    } finally {
      setLoading(false)
      scrollToBottom()
    }
  }

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>): void => {
    event.preventDefault()
    void sendQuestion()
  }

  return (
    <>
      {/* El boton desaparece con el panel abierto: si no, quedan dos "cerrar" a la
          vista al mismo tiempo (este y el del encabezado) diciendo lo mismo. */}
      {!open && (
        <button
          type="button"
          onClick={() => setOpen(true)}
          className="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#FF6A00] text-white shadow-[0_6px_20px_rgba(255,106,0,0.35)] outline-none transition hover:brightness-110 focus-visible:ring-2 focus-visible:ring-[#141A22] focus-visible:ring-offset-2"
          aria-label="Abrir asistente"
        >
          <NutIcon className="h-6 w-6" />
        </button>
      )}

      {open && (
        <div className="fixed inset-x-3 bottom-5 z-40 flex h-[70vh] max-h-[560px] flex-col overflow-hidden rounded-2xl border border-[#E3E0DB] bg-[#F5F3F0] shadow-[0_18px_50px_rgba(20,26,34,0.18)] motion-safe:animate-chat-in sm:inset-x-auto sm:right-5 sm:w-[380px]">
          {/* Cabecera sobre el mismo papel: el naranja se reserva para el contenido. */}
          <header className="flex items-center gap-2.5 border-b border-[#E3E0DB] px-4 py-3">
            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#141A22] text-[#FF6A00]">
              <NutIcon className="h-4 w-4" />
            </span>
            <div className="min-w-0 flex-1">
              <p className="truncate font-display text-[15px] font-semibold leading-tight text-[#141A22]">
                {storeName}
              </p>
              <p className="text-[11px] leading-tight text-[#5C6B7A]">
                Responde con el inventario de la tienda
              </p>
            </div>
            <button
              type="button"
              onClick={() => setOpen(false)}
              className="-mr-1 rounded-lg px-2 py-1 text-[18px] leading-none text-[#5C6B7A] outline-none transition hover:bg-[#E3E0DB] focus-visible:ring-2 focus-visible:ring-[#141A22]"
              aria-label="Cerrar asistente"
            >
              ×
            </button>
          </header>

          <div
            ref={scrollRef}
            className="flex-1 space-y-4 overflow-y-auto px-4 py-4"
            aria-live="polite"
            aria-busy={loading}
          >
            {messages.length === 0 && (
              <div className="pt-2">
                <p className="font-display text-[17px] font-semibold leading-snug text-[#141A22]">
                  ¿Qué necesita tu moto?
                </p>
                <p className="mt-1 text-[13px] leading-relaxed text-[#5C6B7A]">
                  Pregunta por un repuesto, un precio, o si algo de otra moto te sirve.
                </p>
                <ul className="mt-3 space-y-2">
                  {EJEMPLOS.map((ejemplo) => (
                    <li key={ejemplo}>
                      <button
                        type="button"
                        onClick={() => void sendQuestion(ejemplo)}
                        className="w-full rounded-xl border border-[#E3E0DB] bg-white px-3 py-2 text-left text-[13px] text-[#141A22] outline-none transition hover:border-[#141A22] focus-visible:ring-2 focus-visible:ring-[#141A22]"
                      >
                        {ejemplo}
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {messages.map((msg, i) =>
              msg.role === 'user' ? (
                <div key={i} className="flex justify-end">
                  <p className="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-br-md bg-[#FF6A00] px-3.5 py-2 text-[13px] leading-relaxed text-white">
                    {msg.text}
                  </p>
                </div>
              ) : (
                /* El asistente habla sobre el papel, sin globo: deja las etiquetas de
                   repuesto como el unico objeto con peso en la conversacion. */
                <div key={i} data-msg="assistant" className="text-[13px] leading-relaxed text-[#141A22]">
                  <RichAnswer text={msg.text} />

                  {msg.products && msg.products.length > 0 && (
                    <ul className="mt-3 space-y-2.5 rounded-xl border border-[#E3E0DB] bg-white p-3">
                      {msg.products.map((p) => (
                        <PartTag key={p.id} product={p} />
                      ))}
                    </ul>
                  )}
                </div>
              ),
            )}

            {loading && (
              // Dice lo que de verdad esta pasando: primero se busca en el catalogo
              // y despues responde la IA. "Escribiendo..." seria inventarse una
              // persona que no existe.
              <p className="flex items-center gap-2 text-[12px] text-[#5C6B7A]">
                <span className="h-1.5 w-1.5 rounded-full bg-[#FF6A00] motion-safe:animate-pulse" />
                Buscando en el inventario…
              </p>
            )}
          </div>

          <form onSubmit={handleSubmit} className="flex gap-2 border-t border-[#E3E0DB] bg-white p-3">
            <input
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder="Escribe tu pregunta…"
              aria-label="Tu pregunta"
              className="min-w-0 flex-1 rounded-xl border border-[#E3E0DB] px-3 py-2 text-[13px] text-[#141A22] outline-none transition placeholder:text-[#9AA3AD] focus:border-[#FF6A00] focus:ring-2 focus:ring-[#FF6A00]/20"
            />
            <button
              type="submit"
              disabled={loading || input.trim().length === 0}
              className="shrink-0 rounded-xl bg-[#FF6A00] px-4 py-2 text-[13px] font-semibold text-white outline-none transition hover:brightness-110 focus-visible:ring-2 focus-visible:ring-[#141A22] focus-visible:ring-offset-2 disabled:opacity-40"
            >
              Enviar
            </button>
          </form>
        </div>
      )}
    </>
  )
}
