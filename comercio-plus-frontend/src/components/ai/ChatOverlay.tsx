import { useEffect, useState, useRef, useCallback } from 'react'
import { X } from 'lucide-react'
import type { Message, UsageInfo, PartResult } from '@/types/ai'
import { searchParts, searchMarketplace } from '@/services/aiService'
import type { MarketplaceProduct } from '@/services/aiService'
import ChatMessage from './ChatMessage'
import ChatInput from './ChatInput'
import UsageCounter from './UsageCounter'

const WELCOME_MESSAGE: Message = {
  id: 'welcome',
  role: 'assistant',
  content:
    'Soy tu asistente de repuestos. Preguntame sobre compatibilidad de piezas para motos.\n\nEjemplos:\n- Que banda sirve para Yamaha Viva R 2018?\n- Pastillas de freno para AKT NKD 125\n- Bujias para FZ 16',
  timestamp: new Date(),
}

const SUGGESTIONS = [
  'Banda para Viva R 2018',
  'Pastillas YBR 125',
  'Bujia Pulsar 200 NS',
  'Cadena FZ 150',
]

interface ChatOverlayProps {
  isOpen: boolean
  onClose: () => void
}

// ── Helpers ──────────────────────────────────────────────────

function normalizeText(text: string): string {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s]/g, '')
    .trim()
}

const CORRECTIONS: Record<string, string> = {
  vanda: 'banda',
  vandas: 'bandas',
  pastiya: 'pastilla',
  pastias: 'pastillas',
  caena: 'cadena',
  filtra: 'filtro',
  'viva r style 115': 'viva r',
  'viva r style': 'viva r',
  'viva 115': 'viva r',
}

const BRANDS = ['yamaha', 'honda', 'akt', 'suzuki', 'bajaj', 'kawasaki', 'ktm', 'tvs', 'hero', 'auteco']

const MODELS = [
  // Yamaha
  'viva r', 'viva', 'ybr 125', 'ybr', 'crypton', 'fz 16', 'fz', 'nmax',
  // AKT
  'nkd 125', 'nkd', 'cr4', 'cr5', 'flex',
  // Honda
  'pcx', 'wave', 'cb 125',
  // Suzuki
  'gixxer', 'gn 125', 'gn', 'an 125',
  // Bajaj
  'pulsar 200', 'pulsar 150', 'pulsar 135', 'pulsar',
  'discover', 'boxer',
  // Kawasaki
  'ninja',
  // TVS
  'apache',
  // Hero
  'splendor',
  // Auteco
  'victory',
]

const PART_TYPES = [
  'banda', 'bandas', 'pastilla', 'pastillas', 'freno', 'frenos',
  'bujia', 'bujias', 'cadena', 'cadenas', 'filtro', 'filtros',
  'kit', 'arrastre', 'pinon', 'catalina', 'aceite',
]

function extractSearchTerms(question: string): string[] {
  let corrected = normalizeText(question)

  // Longer corrections first so "viva r style 115" matches before "viva"
  const sortedKeys = Object.keys(CORRECTIONS).sort((a, b) => b.length - a.length)
  for (const wrong of sortedKeys) {
    corrected = corrected.replace(new RegExp(wrong, 'g'), CORRECTIONS[wrong])
  }

  const foundBrand = BRANDS.find(b => corrected.includes(b))

  // Check longer models first
  const sortedModels = [...MODELS].sort((a, b) => b.length - a.length)
  const foundModel = sortedModels.find(m => corrected.includes(m))

  const foundPartType = PART_TYPES.find(p => corrected.includes(p))

  const queries: string[] = []

  if (foundModel) queries.push(foundModel)
  if (foundBrand && foundModel) queries.push(`${foundBrand} ${foundModel}`)
  if (foundBrand && !foundModel) queries.push(foundBrand)
  if (foundPartType) queries.push(foundPartType)

  if (queries.length === 0) {
    const words = corrected.split(/\s+/).filter(w => w.length > 2)
    if (words.length > 0) queries.push(words.slice(0, 3).join(' '))
  }

  return queries.length > 0 ? queries : ['']
}

function detectPartCategory(text: string): string {
  const n = normalizeText(text)
  if (n.includes('banda')) return 'bandas de transmision'
  if (n.includes('pastilla') || n.includes('freno')) return 'pastillas de freno'
  if (n.includes('bujia')) return 'bujias'
  if (n.includes('cadena')) return 'cadenas'
  if (n.includes('filtro')) return 'filtros'
  return ''
}

function buildFallbackContent(category: string): string {
  switch (category) {
    case 'bandas de transmision':
      return (
        '**Bandas de transmision:**\n' +
        '- Yamaha Viva R / YBR 125: Gates G-125, DID 125X, Bando 125\n' +
        '- AKT NKD 125: Bando 125, Gates G-125\n' +
        '- Honda Wave 110: Gates G-110, DID 110\n\n' +
        'Duracion promedio: 10,000-15,000 km\n' +
        'Sintomas de desgaste: vibracion, perdida de potencia'
      )
    case 'pastillas de freno':
      return (
        '**Pastillas de freno:**\n' +
        '- Yamaha FZ 16: EBC FA213, Vesrah VD-253\n' +
        '- AKT NKD 125: EBC FA442\n' +
        '- Honda PCX 125: EBC FA231\n\n' +
        'Cambiar cada 8,000-12,000 km\n' +
        'Revisar grosor minimo: 2mm'
      )
    case 'bujias':
      return (
        '**Bujias:**\n' +
        '- Yamaha YBR 125 / Viva R: NGK CR7HSA, Champion RG4HC\n' +
        '- Yamaha FZ 16: NGK CR8E, Denso U22EPR9\n' +
        '- Honda Wave 110: NGK C7HSA\n\n' +
        'Cambiar cada 5,000-8,000 km\n' +
        'Gap correcto: 0.7-0.8mm'
      )
    default:
      return (
        '**Marcas disponibles:**\n' +
        '- Yamaha: YBR 125, Viva R, FZ 16, Crypton, NMAX\n' +
        '- Honda: Wave 110, PCX 125, CB 125F\n' +
        '- AKT: NKD 125, CR4 150, CR5 180, Flex\n' +
        '- Suzuki: Gixxer 150, AN 125'
      )
  }
}

function formatCOP(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value)
}

function formatEnhancedResponse(
  results: PartResult[],
  meliProducts: MarketplaceProduct[],
  originalQuestion: string,
): Message {
  let content = ''

  // Section 1: Verified DB results
  if (results.length > 0) {
    const moto = `${results[0].motorcycle_brand} ${results[0].motorcycle_model}`
    const years = `${results[0].year_from}-${results[0].year_to}`

    const byType: Record<string, PartResult[]> = {}
    for (const part of results) {
      const key = part.part_type || 'Otros'
      if (!byType[key]) byType[key] = []
      byType[key].push(part)
    }

    content += `**Compatibilidades verificadas** para **${moto}** (${years}):\n\n`

    for (const [tipo, parts] of Object.entries(byType)) {
      content += `**${tipo}:**\n`
      for (const part of parts) {
        content += `- **${part.part_reference}** (${part.part_brand})`
        if (part.notes) content += ` — ${part.notes}`
        content += '\n'
      }
      content += '\n'
    }
  } else {
    const category = detectPartCategory(originalQuestion)
    const label = category || 'repuestos'
    content += `No encontre compatibilidades verificadas para "${originalQuestion}".\n\n`
    content += `**Info general sobre ${label}:**\n\n`
    content += buildFallbackContent(category)
    content += '\n\n'
  }

  // Section 2: Marketplace results
  if (meliProducts.length > 0) {
    content += `**Disponibles en Mercado Libre** (${meliProducts.length}):\n`
    content += '_Precios de referencia — confirmar compatibilidad_\n\n'

    for (const product of meliProducts.slice(0, 3)) {
      content += `- ${product.title}\n`
      content += `  ${formatCOP(product.price)} — ${product.seller || 'Vendedor ML'}\n\n`
    }
  }

  // Tips
  if (results.length > 0) {
    content += '**Tips de venta:**\n'
    content += '- Verifica el ano exacto de la moto del cliente\n'
    content += '- Confirma la referencia antes de ordenar\n'
  } else {
    content += '**Intenta preguntar asi:**\n'
    content += '- "Que banda sirve para Yamaha YBR 125 2016?"\n'
    content += '- "Pastillas de freno para AKT NKD 125"\n'
    content += '- "Bujias compatibles con FZ 16"\n'
  }

  return {
    id: `assistant-${Date.now()}`,
    role: 'assistant',
    content,
    timestamp: new Date(),
    parts: results.length > 0 ? results : undefined,
  }
}

// ── Component ───────────────────────────────────────────────

export default function ChatOverlay({ isOpen, onClose }: ChatOverlayProps) {
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
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
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

      const userMsg: Message = {
        id: `user-${Date.now()}`,
        role: 'user',
        content: text,
        timestamp: new Date(),
      }
      setMessages(prev => [...prev, userMsg])
      setIsLoading(true)

      try {
        const queries = extractSearchTerms(text)

        // 1. Search verified DB (cascading)
        let dbResults: PartResult[] = []
        for (const query of queries) {
          if (!query) continue
          try {
            const data = await searchParts(query)
            if (data.results && data.results.length > 0) {
              dbResults = data.results
              break
            }
          } catch {
            // try next query
          }
        }

        // 2. Search marketplace (non-blocking)
        let meliProducts: MarketplaceProduct[] = []
        try {
          const meliQuery = queries[0] || text
          const meliData = await searchMarketplace(meliQuery)
          meliProducts = meliData.products || []
        } catch {
          // marketplace is optional
        }

        const assistantMsg = formatEnhancedResponse(dbResults, meliProducts, text)
        setMessages(prev => [...prev, assistantMsg])
        setUsage(prev => ({ ...prev, used: prev.used + 1 }))
      } catch {
        setMessages(prev => [
          ...prev,
          {
            id: `error-${Date.now()}`,
            role: 'assistant',
            content:
              'No pude conectar con el servidor de repuestos. Verifica que el microservicio este corriendo en localhost:5000.',
            timestamp: new Date(),
          },
        ])
      } finally {
        setIsLoading(false)
      }
    },
    [isLoading, usage],
  )

  const handleClear = () => {
    setMessages([WELCOME_MESSAGE])
  }

  if (!isOpen) return null

  return (
    <>
      {/* Backdrop */}
      <div className="fixed inset-0 z-40 bg-black/50" onClick={onClose} />

      {/* Panel */}
      <div className="fixed inset-x-0 top-0 z-50 mx-auto max-w-3xl px-4 pt-0 sm:px-6">
        <div className="animate-slide-down overflow-hidden rounded-b-2xl bg-white shadow-2xl">
          {/* Header */}
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
                <p className="text-[11px] text-slate-500">Busqueda inteligente por compatibilidad</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <UsageCounter usage={usage} onUpgrade={() => setShowUpgrade(true)} />
              {messages.length > 1 && (
                <button
                  onClick={handleClear}
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

          {/* Chat area */}
          <div className="flex h-[55vh] flex-col">
            {/* Messages */}
            <div className="flex-1 overflow-y-auto px-4 py-4">
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
                        onClick={() => handleSend(s)}
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
                        <span
                          className="h-2 w-2 animate-bounce rounded-full bg-slate-400"
                          style={{ animationDelay: '0ms' }}
                        />
                        <span
                          className="h-2 w-2 animate-bounce rounded-full bg-slate-400"
                          style={{ animationDelay: '150ms' }}
                        />
                        <span
                          className="h-2 w-2 animate-bounce rounded-full bg-slate-400"
                          style={{ animationDelay: '300ms' }}
                        />
                      </div>
                    </div>
                  </div>
                )}

                <div ref={messagesEndRef} />
              </div>
            </div>

            {/* Input */}
            <div className="border-t border-slate-200 bg-slate-50 px-4 py-3">
              <ChatInput onSend={handleSend} isLoading={isLoading} disabled={usage.used >= usage.limit} />
              <p className="mt-1 text-center text-[10px] text-slate-400">
                Enter para enviar &middot; Esc para cerrar
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Upgrade modal */}
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
