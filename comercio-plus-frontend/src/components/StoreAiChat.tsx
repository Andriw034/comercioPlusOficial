import { useRef, useState } from 'react'
import { askAssistant, type AssistantProduct, type AssistantTurn } from '@/services/aiService'
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

  const sendQuestion = async (): Promise<void> => {
    const question = input.trim()
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
      {/* Boton flotante */}
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#FF6A00] text-white shadow-lg transition hover:brightness-110"
        aria-label="Abrir asistente"
      >
        {open ? <span className="text-2xl leading-none">×</span> : <span className="text-xl">💬</span>}
      </button>

      {/* Panel de chat */}
      {open && (
        <div className="fixed bottom-24 right-5 z-40 flex h-[70vh] max-h-[560px] w-[92vw] max-w-sm flex-col overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-2xl">
          <div className="bg-[#FF6A00] px-4 py-3 text-white">
            <p className="text-sm font-bold">Asistente de {storeName}</p>
            <p className="text-xs text-white/80">Pregunta por repuestos y compatibilidad</p>
          </div>

          <div ref={scrollRef} className="flex-1 space-y-3 overflow-y-auto bg-[#F9FAFB] p-3">
            {messages.length === 0 && (
              <p className="mt-6 text-center text-sm text-slate-500">
                Hola 👋 Pregúntame qué repuesto le sirve a tu moto.
                <br />
                Ej: &quot;¿Qué le sirve a una Boxer 2018?&quot;
              </p>
            )}

            {messages.map((msg, i) => (
              <div key={i} className={msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'}>
                <div
                  className={
                    msg.role === 'user'
                      ? 'max-w-[85%] rounded-2xl rounded-br-sm bg-[#FF6A00] px-3 py-2 text-sm text-white'
                      : 'max-w-[85%] rounded-2xl rounded-bl-sm border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#1A1A2E]'
                  }
                >
                  {msg.role === 'assistant' ? (
                    <RichAnswer text={msg.text} />
                  ) : (
                    <p className="whitespace-pre-wrap">{msg.text}</p>
                  )}
                  {msg.products && msg.products.length > 0 && (
                    <div className="mt-2 space-y-1 border-t border-[#E5E7EB] pt-2">
                      {msg.products.map((p) => (
                        <p key={p.id} className="text-xs text-slate-600">
                          • {p.name} — ${p.price}
                          {Number(p.stock) > 0 ? '' : ' (sin stock)'}
                        </p>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            ))}

            {loading && (
              <div className="flex justify-start">
                <div className="rounded-2xl rounded-bl-sm border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-slate-400">
                  Escribiendo…
                </div>
              </div>
            )}
          </div>

          <form onSubmit={handleSubmit} className="flex gap-2 border-t border-[#E5E7EB] p-3">
            <input
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder="Escribe tu pregunta…"
              className="flex-1 rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm outline-none focus:border-[#FF6A00]"
            />
            <button
              type="submit"
              disabled={loading || input.trim().length === 0}
              className="rounded-lg bg-[#FF6A00] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-50"
            >
              Enviar
            </button>
          </form>
        </div>
      )}
    </>
  )
}
