import { useState, useRef, useEffect, useCallback } from 'react'
import { Search, Package, X, Loader2, Mic, MicOff, Clock, TrendingUp } from 'lucide-react'
import { Link } from 'react-router-dom'
import API from '@/services/api'
import { formatPrice } from '@/lib/format'
import { extractList } from '@/lib/api-response'
import type { Product } from '@/types/api'

// ── Search history (localStorage) ───────────────────────────

const HISTORY_KEY = 'cp_search_history'
const MAX_HISTORY = 10

function getHistory(): string[] {
  try {
    return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]') as string[]
  } catch {
    return []
  }
}

function pushHistory(term: string) {
  if (!term || term.length < 2) return
  const prev = getHistory().filter(h => h.toLowerCase() !== term.toLowerCase())
  prev.unshift(term)
  localStorage.setItem(HISTORY_KEY, JSON.stringify(prev.slice(0, MAX_HISTORY)))
}

function clearHistory() {
  localStorage.removeItem(HISTORY_KEY)
}

// ── Popular suggestions ─────────────────────────────────────

const POPULAR = [
  'bandas transmision',
  'bujias NGK',
  'pastillas freno',
  'filtro aceite',
  'cadenas DID',
  'kit arrastre',
]

// ── SpeechRecognition type shim ─────────────────────────────

interface SpeechRecognitionEvent {
  results: { [index: number]: { [index: number]: { transcript: string } } }
}
interface SpeechRecognitionErrorEvent {
  error: string
}
interface SpeechRecognitionInstance {
  lang: string
  continuous: boolean
  interimResults: boolean
  onstart: (() => void) | null
  onresult: ((e: SpeechRecognitionEvent) => void) | null
  onerror: ((e: SpeechRecognitionErrorEvent) => void) | null
  onend: (() => void) | null
  start(): void
  stop(): void
}

function createRecognition(): SpeechRecognitionInstance | null {
  const W = window as unknown as Record<string, unknown>
  const Ctor = (W.SpeechRecognition ?? W.webkitSpeechRecognition) as
    | (new () => SpeechRecognitionInstance)
    | undefined
  if (!Ctor) return null
  const r = new Ctor()
  r.lang = 'es-CO'
  r.continuous = false
  r.interimResults = false
  return r
}

// ── Component ───────────────────────────────────────────────

export default function InventorySearchBar() {
  const [query, setQuery] = useState('')
  const [results, setResults] = useState<Product[]>([])
  const [isOpen, setIsOpen] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [isListening, setIsListening] = useState(false)
  const [history, setHistory] = useState<string[]>(getHistory)
  const [showIdlePanel, setShowIdlePanel] = useState(false)

  const containerRef = useRef<HTMLDivElement>(null)
  const inputRef = useRef<HTMLInputElement>(null)
  const debounceRef = useRef<ReturnType<typeof setTimeout>>(null)
  const recognitionRef = useRef<SpeechRecognitionInstance | null>(null)

  // Close on outside click
  useEffect(() => {
    function handler(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setIsOpen(false)
        setShowIdlePanel(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  // Close on Escape
  useEffect(() => {
    function handler(e: KeyboardEvent) {
      if (e.key === 'Escape') {
        setIsOpen(false)
        setShowIdlePanel(false)
        inputRef.current?.blur()
      }
    }
    document.addEventListener('keydown', handler)
    return () => document.removeEventListener('keydown', handler)
  }, [])

  // API search
  const search = useCallback(async (term: string) => {
    if (term.length < 2) {
      setResults([])
      setIsOpen(false)
      return
    }
    setIsLoading(true)
    setIsOpen(true)
    setShowIdlePanel(false)
    try {
      const { data } = await API.get('/products', {
        params: { search: term, per_page: 8 },
      })
      setResults(extractList<Product>(data))
      pushHistory(term)
      setHistory(getHistory())
    } catch {
      setResults([])
    } finally {
      setIsLoading(false)
    }
  }, [])

  const handleChange = (value: string) => {
    setQuery(value)
    if (value.length === 0) {
      setResults([])
      setShowIdlePanel(true)
      setIsOpen(true)
      return
    }
    setShowIdlePanel(false)
    if (debounceRef.current) clearTimeout(debounceRef.current)
    debounceRef.current = setTimeout(() => search(value), 300)
  }

  const handlePick = (term: string) => {
    setQuery(term)
    search(term)
  }

  const handleClear = () => {
    setQuery('')
    setResults([])
    setShowIdlePanel(false)
    setIsOpen(false)
    inputRef.current?.focus()
  }

  const handleClearHistory = () => {
    clearHistory()
    setHistory([])
  }

  // ── Voice search ────────────────────────────────────────

  const toggleVoice = useCallback(() => {
    if (isListening) {
      recognitionRef.current?.stop()
      setIsListening(false)
      return
    }

    if (!recognitionRef.current) {
      recognitionRef.current = createRecognition()
    }

    const r = recognitionRef.current
    if (!r) return

    r.onstart = () => setIsListening(true)
    r.onend = () => setIsListening(false)
    r.onresult = (e: SpeechRecognitionEvent) => {
      const transcript = e.results[0][0].transcript
      setQuery(transcript)
      search(transcript)
      setIsListening(false)
    }
    r.onerror = () => setIsListening(false)

    try {
      r.start()
    } catch {
      setIsListening(false)
    }
  }, [isListening, search])

  const hasVoice =
    typeof window !== 'undefined' &&
    ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)

  // ── Render ──────────────────────────────────────────────

  return (
    <div className="relative w-full max-w-lg" ref={containerRef}>
      {/* Input */}
      <div className="relative">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
          ref={inputRef}
          type="text"
          value={query}
          onChange={e => handleChange(e.target.value)}
          onFocus={() => {
            if (query.length === 0) {
              setShowIdlePanel(true)
              setIsOpen(true)
            } else if (results.length > 0) {
              setIsOpen(true)
            }
          }}
          placeholder="Buscar en inventario..."
          className={`w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 text-sm text-slate-700 placeholder:text-slate-400 transition-colors focus:border-comercioplus-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-comercioplus-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500 ${
            hasVoice ? 'pr-16' : 'pr-8'
          }`}
        />

        <div className="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-0.5">
          {query && (
            <button
              type="button"
              onClick={handleClear}
              className="rounded p-1 text-slate-400 hover:text-slate-600"
            >
              <X className="h-3.5 w-3.5" />
            </button>
          )}
          {hasVoice && (
            <button
              type="button"
              onClick={toggleVoice}
              className={`rounded p-1 transition-colors ${
                isListening
                  ? 'animate-pulse bg-red-500 text-white'
                  : 'text-slate-400 hover:text-slate-600'
              }`}
              title={isListening ? 'Escuchando...' : 'Buscar por voz'}
            >
              {isListening ? <MicOff className="h-4 w-4" /> : <Mic className="h-4 w-4" />}
            </button>
          )}
        </div>
      </div>

      {/* Dropdown */}
      {isOpen && (
        <div className="absolute z-50 mt-1.5 w-full max-h-[70vh] sm:max-h-96 overflow-hidden overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
          {/* Idle panel: history + popular */}
          {showIdlePanel && (
            <>
              {history.length > 0 && (
                <div>
                  <div className="flex items-center justify-between bg-slate-50 px-3 py-1.5 dark:bg-slate-800/60">
                    <span className="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">
                      <Clock className="h-3 w-3" />
                      Recientes
                    </span>
                    <button
                      type="button"
                      onClick={handleClearHistory}
                      className="text-[11px] text-slate-400 hover:text-red-500"
                    >
                      Limpiar
                    </button>
                  </div>
                  <ul>
                    {history.slice(0, 5).map(h => (
                      <li key={h}>
                        <button
                          type="button"
                          onClick={() => handlePick(h)}
                          className="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-600 transition-colors hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                          <Clock className="h-3.5 w-3.5 flex-shrink-0 text-slate-300" />
                          {h}
                        </button>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              <div>
                <div className="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-slate-400 dark:bg-slate-800/60">
                  <TrendingUp className="h-3 w-3" />
                  Populares
                </div>
                <ul>
                  {POPULAR.map(s => (
                    <li key={s}>
                      <button
                        type="button"
                        onClick={() => handlePick(s)}
                        className="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-600 transition-colors hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800"
                      >
                        <TrendingUp className="h-3.5 w-3.5 flex-shrink-0 text-slate-300" />
                        {s}
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            </>
          )}

          {/* Loading */}
          {isLoading && !showIdlePanel && (
            <div className="flex items-center justify-center gap-2 py-6 text-sm text-slate-400">
              <Loader2 className="h-4 w-4 animate-spin" />
              Buscando...
            </div>
          )}

          {/* No results */}
          {!isLoading && !showIdlePanel && results.length === 0 && query.length >= 2 && (
            <div className="py-6 text-center text-sm text-slate-400">
              Sin resultados para &ldquo;{query}&rdquo;
            </div>
          )}

          {/* Results */}
          {!isLoading && !showIdlePanel && results.length > 0 && (
            <>
              <ul className="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
                {results.map(product => (
                  <li key={product.id}>
                    <Link
                      to={`/dashboard/products?highlight=${product.id}`}
                      onClick={() => setIsOpen(false)}
                      className="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                      <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800">
                        <Package className="h-4 w-4 text-slate-400" />
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium text-slate-800 dark:text-slate-100">
                          {product.name}
                        </p>
                        <p className="truncate text-xs text-slate-400">
                          {product.sku ? `SKU: ${product.sku}` : ''}
                          {product.sku && product.category?.name ? ' · ' : ''}
                          {product.category?.name ?? ''}
                        </p>
                      </div>
                      <div className="flex flex-shrink-0 flex-col items-end gap-0.5">
                        <span className="text-sm font-semibold text-slate-800 dark:text-slate-100">
                          {formatPrice(product.price)}
                        </span>
                        <span
                          className={`text-xs font-medium ${
                            product.stock > 0 ? 'text-emerald-600' : 'text-red-500'
                          }`}
                        >
                          {product.stock > 0 ? `${product.stock} en stock` : 'Agotado'}
                        </span>
                      </div>
                    </Link>
                  </li>
                ))}
              </ul>

              <div className="border-t border-slate-100 bg-slate-50 px-4 py-2 text-center text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-900/50">
                {results.length} resultado{results.length !== 1 ? 's' : ''}
                {' · '}
                <Link
                  to={`/dashboard/products?search=${encodeURIComponent(query)}`}
                  onClick={() => setIsOpen(false)}
                  className="font-medium text-comercioplus-600 hover:underline"
                >
                  Ver todos
                </Link>
              </div>
            </>
          )}
        </div>
      )}
    </div>
  )
}
