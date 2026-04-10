import { useState, useRef, useEffect, useCallback } from 'react'
import type { Message, UsageInfo, PartResult } from '@/types/ai'
import { searchParts, checkHealth } from '@/services/aiService'
import ChatMessage from '@/components/ai/ChatMessage'
import ChatInput from '@/components/ai/ChatInput'
import UsageCounter from '@/components/ai/UsageCounter'

const WELCOME_MESSAGE: Message = {
  id: 'welcome',
  role: 'assistant',
  content:
    'Soy tu asistente de repuestos de motos. Preguntame que repuesto necesitas y te digo cuales son compatibles.\n\nPuedes preguntar cosas como:',
  timestamp: new Date(),
}

const SUGGESTIONS = [
  'Que banda sirve para Viva R 2018?',
  'Pastillas de freno para YBR 125',
  'Bujia para Pulsar 200 NS',
  'Cadena para FZ 150',
]

function formatResponse(results: PartResult[], query: string): { content: string; parts: PartResult[] } {
  if (results.length === 0) {
    return {
      content: `No encontre repuestos compatibles con "${query}". Trabajamos con marcas como Yamaha, Honda, AKT, Suzuki y Bajaj. Verifica la marca y modelo e intenta de nuevo.`,
      parts: [],
    }
  }

  const moto = `${results[0].motorcycle_brand} ${results[0].motorcycle_model}`
  const count = results.length
  const content = `Encontre ${count} repuesto${count === 1 ? '' : 's'} compatible${count === 1 ? '' : 's'} con ${moto}:`

  return { content, parts: results }
}

export default function AIAssistant() {
  const [messages, setMessages] = useState<Message[]>([WELCOME_MESSAGE])
  const [usage, setUsage] = useState<UsageInfo>({ used: 0, limit: 5, plan: 'FREE' })
  const [isLoading, setIsLoading] = useState(false)
  const [serviceOnline, setServiceOnline] = useState<boolean | null>(null)
  const [showUpgrade, setShowUpgrade] = useState(false)
  const messagesEndRef = useRef<HTMLDivElement>(null)

  // Auto-scroll on new messages
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, isLoading])

  // Check service health on mount
  useEffect(() => {
    checkHealth().then(setServiceOnline)
  }, [])

  const addMessage = useCallback((msg: Message) => {
    setMessages(prev => [...prev, msg])
  }, [])

  const handleSend = async (text: string) => {
    // Check limit
    if (usage.used >= usage.limit) {
      setShowUpgrade(true)
      return
    }

    // Add user message
    const userMsg: Message = {
      id: `user-${Date.now()}`,
      role: 'user',
      content: text,
      timestamp: new Date(),
    }
    addMessage(userMsg)
    setIsLoading(true)

    try {
      const data = await searchParts(text)
      const { content, parts } = formatResponse(data.results, text)

      const assistantMsg: Message = {
        id: `assistant-${Date.now()}`,
        role: 'assistant',
        content,
        timestamp: new Date(),
        parts: parts.length > 0 ? parts : undefined,
      }
      addMessage(assistantMsg)
      setUsage(prev => ({ ...prev, used: prev.used + 1 }))
    } catch {
      addMessage({
        id: `error-${Date.now()}`,
        role: 'assistant',
        content: 'No pude conectar con el servidor de repuestos. Verifica que el microservicio este corriendo en localhost:5000.',
        timestamp: new Date(),
      })
    } finally {
      setIsLoading(false)
    }
  }

  const handleSuggestion = (suggestion: string) => {
    if (!isLoading && usage.used < usage.limit) {
      handleSend(suggestion)
    }
  }

  const handleClear = () => {
    setMessages([WELCOME_MESSAGE])
  }

  return (
    <div className="mx-auto flex h-[calc(100vh-64px)] max-w-2xl flex-col">
      {/* Header */}
      <div className="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 sm:px-6">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-comercioplus-600 text-sm font-bold text-white">
            AI
          </div>
          <div>
            <h1 className="text-base font-bold text-slate-900">Asistente de Repuestos</h1>
            <div className="flex items-center gap-1.5">
              {serviceOnline !== null && (
                <span className={`inline-block h-1.5 w-1.5 rounded-full ${serviceOnline ? 'bg-emerald-500' : 'bg-red-500'}`} />
              )}
              <p className="text-xs text-slate-500">
                {serviceOnline === null ? 'Conectando...' : serviceOnline ? 'En linea' : 'Sin conexion'}
              </p>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <UsageCounter usage={usage} onUpgrade={() => setShowUpgrade(true)} />
          {messages.length > 1 && (
            <button
              onClick={handleClear}
              className="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
              title="Limpiar chat"
            >
              <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
              </svg>
            </button>
          )}
        </div>
      </div>

      {/* Messages area */}
      <div className="flex-1 overflow-y-auto bg-slate-50 px-4 py-4 sm:px-6">
        <div className="space-y-4">
          {messages.map(msg => (
            <ChatMessage key={msg.id} message={msg} />
          ))}

          {/* Suggestions after welcome */}
          {messages.length === 1 && (
            <div className="flex flex-wrap gap-2 pl-10">
              {SUGGESTIONS.map(s => (
                <button
                  key={s}
                  onClick={() => handleSuggestion(s)}
                  className="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 transition-colors hover:border-comercioplus-600 hover:text-comercioplus-600"
                >
                  {s}
                </button>
              ))}
            </div>
          )}

          {/* Typing indicator */}
          {isLoading && (
            <div className="flex items-start">
              <div className="mr-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-comercioplus-600 text-xs font-bold text-white">
                AI
              </div>
              <div className="rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3">
                <div className="flex gap-1">
                  <span className="h-2 w-2 animate-bounce rounded-full bg-slate-400" style={{ animationDelay: '0ms' }} />
                  <span className="h-2 w-2 animate-bounce rounded-full bg-slate-400" style={{ animationDelay: '150ms' }} />
                  <span className="h-2 w-2 animate-bounce rounded-full bg-slate-400" style={{ animationDelay: '300ms' }} />
                </div>
              </div>
            </div>
          )}

          <div ref={messagesEndRef} />
        </div>
      </div>

      {/* Input area */}
      <div className="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
        <ChatInput
          onSend={handleSend}
          isLoading={isLoading}
          disabled={usage.used >= usage.limit}
        />
        <p className="mt-1.5 text-center text-[10px] text-slate-400">
          Enter para enviar &middot; Shift+Enter para nueva linea
        </p>
      </div>

      {/* Upgrade Modal */}
      {showUpgrade && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
            <div className="mb-4 text-center">
              <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-2xl">
                !
              </div>
              <h2 className="text-lg font-bold text-slate-900">Limite alcanzado</h2>
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
    </div>
  )
}
