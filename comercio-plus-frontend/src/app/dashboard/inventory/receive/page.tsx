import { useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Volume2, VolumeX } from 'lucide-react'
import useInventoryReceive from '@/hooks/useInventoryReceive'
import ReceiveScannerPanel, { type ReceiveFeedback } from '@/components/inventory/ReceiveScannerPanel'
import ReceiveHistoryList from '@/components/inventory/ReceiveHistoryList'
import QuickCreateProductModal from '@/components/inventory/QuickCreateProductModal'
import type { ScanInErrorResponse } from '@/services/inventoryReceive'

const SOUND_PREF_KEY = 'inventory_receive_sound_enabled'

function toUuid() {
  if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
    return crypto.randomUUID()
  }
  return `req-${Date.now()}-${Math.random().toString(16).slice(2)}`
}

function playFeedbackSound(enabled: boolean, ok: boolean) {
  if (!enabled) return

  try {
    const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext
    if (!AudioContextClass) return

    const context = new AudioContextClass()
    const oscillator = context.createOscillator()
    const gain = context.createGain()

    oscillator.type = 'sine'
    oscillator.frequency.value = ok ? 880 : 220
    gain.gain.value = 0.025

    oscillator.connect(gain)
    gain.connect(context.destination)
    oscillator.start()
    oscillator.stop(context.currentTime + (ok ? 0.08 : 0.12))
  } catch {
    // optional
  }
}

export default function InventoryReceivePage() {
  const inputRef = useRef<HTMLInputElement | null>(null)
  const feedbackTimerRef = useRef<number | null>(null)

  const {
    movements,
    categories,
    loadingMovements,
    loadMovements,
    loadCategories,
    scanIn,
    createByScan,
  } = useInventoryReceive()

  const [soundEnabled, setSoundEnabled] = useState<boolean>(() => {
    const saved = localStorage.getItem(SOUND_PREF_KEY)
    if (saved === null) return true
    return saved === '1'
  })

  const [advancedOpen, setAdvancedOpen] = useState(false)
  const [code, setCode] = useState('')
  const [qty, setQty] = useState(1)
  const [reason, setReason] = useState('purchase')
  const [reference, setReference] = useState('')
  const [feedback, setFeedback] = useState<ReceiveFeedback | null>(null)
  const [busy, setBusy] = useState(false)

  const [quickCreateOpen, setQuickCreateOpen] = useState(false)
  const [quickCreateCode, setQuickCreateCode] = useState('')
  const [quickCreateName, setQuickCreateName] = useState('')
  const [quickCreateCategoryId, setQuickCreateCategoryId] = useState<number | ''>('')
  const [quickCreatePrice, setQuickCreatePrice] = useState('')
  const [quickCreateQty, setQuickCreateQty] = useState(1)

  const canSubmitScan = useMemo(() => code.trim().length > 0 && qty > 0 && !busy, [busy, code, qty])

  const focusScanner = () => {
    setTimeout(() => {
      inputRef.current?.focus()
      inputRef.current?.select()
    }, 0)
  }

  useEffect(() => {
    void loadMovements(10, 'purchase')
    void loadCategories()
    focusScanner()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    localStorage.setItem(SOUND_PREF_KEY, soundEnabled ? '1' : '0')
  }, [soundEnabled])

  useEffect(() => {
    if (!feedback) return

    if (feedbackTimerRef.current) {
      window.clearTimeout(feedbackTimerRef.current)
    }

    feedbackTimerRef.current = window.setTimeout(() => {
      setFeedback(null)
    }, 3000)

    return () => {
      if (feedbackTimerRef.current) {
        window.clearTimeout(feedbackTimerRef.current)
      }
    }
  }, [feedback])

  const handleScanSubmit = async () => {
    if (!canSubmitScan) return

    setBusy(true)
    setFeedback(null)

    try {
      const response = await scanIn({
        code: code.trim(),
        qty: Math.max(1, qty),
        reason,
        reference: reference.trim() || undefined,
        request_id: toUuid(),
      })

      const product = response?.data?.product
      const movedQty = response?.data?.movement?.quantity ?? qty

      setFeedback({
        type: 'ok',
        message: `Listo. Sume ${movedQty} unidades a ${product?.name || 'producto'}.`,
        detail: `Stock actual: ${product?.stock ?? '-'}`,
      })
      playFeedbackSound(soundEnabled, true)
      setCode('')
      await loadMovements(10, 'purchase')
    } catch (error: any) {
      const api = (error?.response?.data || {}) as ScanInErrorResponse
      const errorCode = api.error_code
      const message = api.message || 'No pude procesar ese escaneo.'

      setFeedback({
        type: 'error',
        message,
        detail: errorCode === 'PRODUCT_NOT_FOUND' ? 'No encuentro ese codigo en tu catalogo. ¿Quieres crearlo?' : undefined,
        code: errorCode,
      })
      playFeedbackSound(soundEnabled, false)

      if (errorCode === 'PRODUCT_NOT_FOUND') {
        setQuickCreateCode(code.trim())
        setQuickCreateQty(Math.max(1, qty))
        setQuickCreateOpen(true)
      }
    } finally {
      setBusy(false)
      focusScanner()
    }
  }

  const handleQuickCreate = async () => {
    if (!quickCreateName.trim() || quickCreateQty < 1 || busy) return

    setBusy(true)
    setFeedback(null)

    try {
      const response = await createByScan({
        code: quickCreateCode,
        code_type: 'barcode',
        name: quickCreateName.trim(),
        category_id: quickCreateCategoryId === '' ? undefined : Number(quickCreateCategoryId),
        price: quickCreatePrice.trim() ? Number(quickCreatePrice) : 0,
        initial_qty: Math.max(1, quickCreateQty),
        reason,
        reference: reference.trim() || undefined,
        request_id: toUuid(),
      })

      const product = response?.data?.product
      setFeedback({
        type: 'ok',
        message: `Listo. Cree ${product?.name || 'producto'} y sume ${quickCreateQty} unidades.`,
        detail: `Stock actual: ${product?.stock ?? '-'}`,
      })
      playFeedbackSound(soundEnabled, true)

      setQuickCreateOpen(false)
      setCode('')
      setQuickCreateCode('')
      setQuickCreateName('')
      setQuickCreatePrice('')
      setQuickCreateCategoryId('')
      setQuickCreateQty(1)

      await loadMovements(10, 'purchase')
    } catch (error: any) {
      const api = (error?.response?.data || {}) as ScanInErrorResponse
      setFeedback({
        type: 'error',
        message: api.message || 'No pude crear el producto desde escaneo.',
        code: api.error_code,
      })
      playFeedbackSound(soundEnabled, false)
    } finally {
      setBusy(false)
      focusScanner()
    }
  }

  return (
    <div className="space-y-6 pb-20 sm:pb-0">
      <header className="rounded-3xl border border-slate-200 bg-[linear-gradient(135deg,#0F172A_0%,#1E293B_55%,#111827_100%)] p-6 text-white shadow-[0_18px_45px_rgba(15,23,42,0.28)]">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div>
            <h1 className="font-display text-[30px] font-black tracking-tight text-orange-300">Ingreso por escaner</h1>
            <p className="mt-1 text-[13px] text-slate-300">Suma stock rapido escaneando el codigo del producto.</p>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <button
              type="button"
              onClick={() => setSoundEnabled((prev) => !prev)}
              className="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-white/20"
            >
              {soundEnabled ? <Volume2 size={14} /> : <VolumeX size={14} />}
              Sonido {soundEnabled ? 'ON' : 'OFF'}
            </button>

            <Link
              to="/dashboard/inventory"
              className="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-white/20"
            >
              Volver a inventario
            </Link>
          </div>
        </div>
      </header>

      <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <ReceiveScannerPanel
          inputRef={inputRef}
          code={code}
          qty={qty}
          reason={reason}
          reference={reference}
          busy={busy}
          advancedOpen={advancedOpen}
          feedback={feedback}
          onCodeChange={setCode}
          onQtyChange={setQty}
          onReasonChange={setReason}
          onReferenceChange={setReference}
          onToggleAdvanced={() => setAdvancedOpen((prev) => !prev)}
          onSubmit={() => void handleScanSubmit()}
        />

        <ReceiveHistoryList
          movements={movements}
          loading={loadingMovements}
          onRefresh={() => {
            void loadMovements(10, 'purchase')
          }}
        />
      </div>

      <QuickCreateProductModal
        open={quickCreateOpen}
        busy={busy}
        code={quickCreateCode}
        name={quickCreateName}
        categoryId={quickCreateCategoryId}
        price={quickCreatePrice}
        initialQty={quickCreateQty}
        categories={categories}
        onCodeChange={setQuickCreateCode}
        onNameChange={setQuickCreateName}
        onCategoryChange={setQuickCreateCategoryId}
        onPriceChange={setQuickCreatePrice}
        onInitialQtyChange={setQuickCreateQty}
        onClose={() => {
          setQuickCreateOpen(false)
          focusScanner()
        }}
        onSubmit={() => void handleQuickCreate()}
      />
    </div>
  )
}

