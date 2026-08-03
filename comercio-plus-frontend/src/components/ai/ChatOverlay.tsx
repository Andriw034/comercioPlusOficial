import { useEffect, useState, useRef, useCallback } from 'react'
import { X } from 'lucide-react'
import type { Message, UsageInfo } from '@/types/ai'
import { askAssistant } from '@/services/aiService'
import ChatMessage from './ChatMessage'
import ChatInput from './ChatInput'
import UsageCounter from './UsageCounter'

const WELCOME_MESSAGE: Message = {
  id: 'welcome',
  role: 'assistant',
  content:
    'Soy tu asistente con IA. Preguntame por los repuestos de tu tienda y te ayudo a encontrar lo que el cliente necesita.\n\nEjemplos:\n- Que le sirve a una Boxer 2018?\n- Tienes pastillas de freno para NKD 125?\n- Que bandas tienes en stock?',
  timestamp: new Date(),
}

const SUGGESTIONS = [
  'Que le sirve a una Boxer 2018?',
  'Que tienes en stock?',
  'Pastillas para NKD 125',
  'Bandas disponibles',
]

interface ChatOverlayProps {
  isOpen: boolean
  onClose: () => void
  /** Tienda del comerciante, para cruzar las referencias con su inventario. */
  storeId?: number
}

export default function ChatOverlay({ isOpen, onClose, storeId }: ChatOverlayProps) {
  const [messages, setMessages] = useState<Message[]>([WELCOME_MESSAGE])
  const [usage, setUsage] = useState<UsageInfo>({ used: 0, limit: 5, plan: 'FREE' })
  const [isLoading, setIsLoading] = useState(false)
  const [showUpgrade, setShowUpgrade] = useState(false)
  const messagesEndRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, isLoading])

  useEffect(() => {
    if (!isOpen) return
    const handler = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose()
    }
    window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [isOpen, onClose])

  useEffect(() => {
    document.body.style.overflow = isOpen ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [isOpen])

  const handleSend = useCallback(
    async (text: string) => {
      if (!text.trim() || isLoading) return

      if (usage.used >= usage.limit) {
        setShowUpgrade(true)
        return
      }

      setMessages(prev => [
        ...prev,
        { id: `user-${Date.now()}`, role: 'user', content: text, timestamp: new Date() },
      ])
      setIsLoading(true)

      try {
        const { answer } = await askAssistant(text, storeId)

        setMessages(prev => [
          ...prev,
          {
            id: `assistant-${Date.now()}`,
            role: 'assistant',
            content: answer,
            timestamp: new Date(),
          },
        ])
        setUsage(prev => ({ ...prev, used: prev.used + 1 }))
      } catch {
        setMessages(prev => [
          ...prev,
          {
            id: `error-${Date.now()}`,
            role: 'assistant',
            content: 'No pude consultar el asistente. Intenta de nuevo en un momento.',
            timestamp: new Date(),
          },
        ])
      } finally {
        setIsLoading(false)
      }
    },
    [isLoading, usage, storeId],
  )

  if (!isOpen) return null

  return (
    <>
      <div className="fixed inset-0 z-40 bg-black/50" onClick={onClose} />

      <div className="fixed inset-x-0 top-0 z-50 mx-auto max-w-3xl px-4 pt-0 sm:px-6">
        <div className="animate-slide-down overflow-hidden rounded-b-2xl bg-white shadow-2xl">
          <div className="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-comercioplus-50 to-orange-50 px-4 py-3">
            <div className="flex items-center gap-3">
              <div className="flex h-9 w-9 items-center justify-center rounded-full bg-comercioplus-600 text-sm font-bold text-white">
                AI
              </div>
              <div>
                <div className="flex items-center gap-1.5">
                  <h2 className="text-sm font-bold text-slate-900">Asistente de Repuestos</h2>
                  <span className="rounded bg-amber-400 px-1.5 py-0.5 text-[9px] font-bold uppercase leading-none text-white">
                    Beta
                  </span>
                </div>
                <p className="text-[11px] text-slate-500">IA que responde con tu inventario</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <UsageCounter usage={usage} onUpgrade={() => setShowUpgrade(true)} />
              {messages.length > 1 && (
                <button
                  onClick={() => setMessages([WELCOME_MESSAGE])}
                  className="rounded-lg px-2 py-1 text-xs text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                >
                  Limpiar
                </button>
              )}
              <button
                onClick={onClose}
                className="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                aria-label="Cerrar"
              >
                <X className="h-5 w-5" />
              </button>
            </div>
          </div>

          <div className="flex h-[55vh] flex-col">
            <div className="flex-1 overflow-y-auto px-4 py-4">
              <div className="space-y-4">
                {messages.map(msg => (
                  <ChatMessage key={msg.id} message={msg} />
                ))}

                {messages.length === 1 && (
                  <div className="flex flex-wrap gap-2 pl-10">
                    {SUGGESTIONS.map(s => (
                      <button
                        key={s}
                        onClick={() => handleSend(s)}
                        className="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 transition-colors hover:border-comercioplus-600 hover:text-comercioplus-600"
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                )}

                {isLoading && (
                  <div className="flex items-start">
                    <div className="mr-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-comercioplus-600 text-xs font-bold text-white">
                      AI
                    </div>
                    <div className="rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3">
                      <div className="flex gap-1">
                        {[0, 150, 300].map(delay => (
                          <span
                            key={delay}
                            className="h-2 w-2 animate-bounce rounded-full bg-slate-400"
                            style={{ animationDelay: `${delay}ms` }}
                          />
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                <div ref={messagesEndRef} />
              </div>
            </div>

            <div className="border-t border-slate-200 bg-slate-50 px-4 py-3">
              <ChatInput onSend={handleSend} isLoading={isLoading} disabled={usage.used >= usage.limit} />
              <p className="mt-1 text-center text-[10px] text-slate-400">
                Enter para enviar &middot; Esc para cerrar
              </p>
            </div>
          </div>
        </div>
      </div>

      {showUpgrade && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
            <div className="mb-4 text-center">
              <h3 className="text-lg font-bold text-slate-900">Limite alcanzado</h3>
              <p className="mt-1 text-sm text-slate-500">
                Usaste {usage.limit}/{usage.limit} consultas gratuitas.
              </p>
            </div>
            <div className="rounded-xl border border-comercioplus-200 bg-comercioplus-50 p-4">
              <p className="text-sm font-semibold text-comercioplus-800">Plan PRO</p>
              <p className="text-xs text-comercioplus-600">50 consultas/mes</p>
              <p className="mt-1 text-lg font-bold text-comercioplus-700">$29.900 COP/mes</p>
            </div>
            <div className="mt-4 flex gap-2">
              <button
                onClick={() => setShowUpgrade(false)}
                className="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
              >
                Cerrar
              </button>
              <button
                onClick={() => setShowUpgrade(false)}
                className="flex-1 rounded-xl bg-comercioplus-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-comercioplus-700"
              >
                Ver planes
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  )
}
