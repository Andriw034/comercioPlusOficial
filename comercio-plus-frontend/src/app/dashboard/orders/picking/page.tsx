import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import type { AxiosError } from 'axios'
import {
  activatePickingFallback,
  completePicking,
  getPickingContext,
  manualPickingAction,
  resetPicking,
  scanPickingCode,
  type ApiDomainError,
  type PickingContextResponse,
} from '@/services/picking'

type WorkMode = 'scanner' | 'manual'
type SyncState = 'saved' | 'saving' | 'offline'

type FeedbackState = {
  type: 'ok' | 'error' | 'warn'
  message: string
  detail?: string
  technicalCode?: string
  scannedCode?: string
  suggestedCode?: string | null
  allowRetry?: boolean
  allowManual?: boolean
  allowMissing?: boolean
}

type ScanHistoryItem = {
  id: string
  at: number
  type: 'ok' | 'error' | 'manual'
  message: string
  code?: string
  qty?: number
}

const SOUND_STORAGE_KEY = 'comercio_plus_picking_sound_enabled'

function readSoundPreference(): boolean {
  try {
    const raw = window.localStorage.getItem(SOUND_STORAGE_KEY)
    return raw === null ? true : raw === '1'
  } catch {
    return true
  }
}

function parseDomainError(error: unknown): ApiDomainError {
  const axiosError = error as AxiosError<ApiDomainError>
  return (
    axiosError?.response?.data || {
      message: 'No se pudo completar la accion.',
    }
  )
}

function normalizeCode(value: string): string {
  return value.trim().toUpperCase().replace(/[^A-Z0-9]/g, '')
}

function levenshtein(a: string, b: string): number {
  if (a === b) return 0
  if (a.length === 0) return b.length
  if (b.length === 0) return a.length

  const rows = b.length + 1
  const cols = a.length + 1
  const matrix: number[][] = Array.from({ length: rows }, (_, row) =>
    Array.from({ length: cols }, (_, col) => (row === 0 ? col : col === 0 ? row : 0)),
  )

  for (let row = 1; row < rows; row += 1) {
    for (let col = 1; col < cols; col += 1) {
      const cost = a[col - 1] === b[row - 1] ? 0 : 1
      matrix[row][col] = Math.min(
        matrix[row][col - 1] + 1,
        matrix[row - 1][col] + 1,
        matrix[row - 1][col - 1] + cost,
      )
    }
  }

  return matrix[rows - 1][cols - 1]
}

function findSuggestedCode(scannedCode: string, lines: PickingContextResponse['data']['lines']): string | null {
  const source = normalizeCode(scannedCode)
  if (!source) return null

  const candidates = lines
    .flatMap((line) => line.codes.map((code) => code.value))
    .filter(Boolean)
    .map((value) => ({ raw: value, norm: normalizeCode(value) }))
    .filter((item) => item.norm.length > 0)

  let best: { raw: string; score: number } | null = null

  for (const candidate of candidates) {
    const distance = levenshtein(source, candidate.norm)
    const closePrefix =
      candidate.norm.startsWith(source) ||
      source.startsWith(candidate.norm) ||
      candidate.norm.includes(source) ||
      source.includes(candidate.norm)
    const score = closePrefix ? Math.min(distance, 1) : distance

    if (!best || score < best.score) {
      best = { raw: candidate.raw, score }
    }
  }

  if (!best) return null
  return best.score <= 2 ? best.raw : null
}

function parseScanFeedback(
  apiError: ApiDomainError,
  scannedCode: string,
  lines: PickingContextResponse['data']['lines'],
): FeedbackState {
  const errorCode = apiError.error_code || 'SCAN_ERROR'
  const suggestedCode = findSuggestedCode(scannedCode, lines)

  if (errorCode === 'FALLBACK_REQUIRED') {
    return {
      type: 'warn',
      message: 'Para evitar errores, continuemos en modo manual.',
      detail: 'Acumulaste 3 fallos consecutivos de escaneo. Puedes volver al escaner despues de una accion manual.',
      technicalCode: errorCode,
      scannedCode,
      allowManual: true,
      allowMissing: true,
      allowRetry: false,
    }
  }

  if (errorCode === 'CODE_NOT_FOUND') {
    return {
      type: 'error',
      message: 'No encuentro ese codigo en este pedido.',
      detail: suggestedCode
        ? `Quiza hubo un error de lectura. Quisiste decir ${suggestedCode}?`
        : 'Revisa el codigo o usa modo manual para continuar.',
      technicalCode: errorCode,
      scannedCode,
      suggestedCode,
      allowRetry: true,
      allowManual: true,
      allowMissing: true,
    }
  }

  if (errorCode === 'CODE_NOT_IN_ORDER') {
    return {
      type: 'error',
      message: 'Ese codigo existe, pero no corresponde a este pedido.',
      detail: 'Puedes marcar faltante o continuar con alistamiento manual.',
      technicalCode: errorCode,
      scannedCode,
      allowRetry: true,
      allowManual: true,
      allowMissing: true,
    }
  }

  if (errorCode === 'ITEM_ALREADY_COMPLETE') {
    return {
      type: 'warn',
      message: 'Esta linea ya esta completa.',
      detail: 'No se sumaron unidades adicionales.',
      technicalCode: errorCode,
      scannedCode,
      allowRetry: true,
      allowManual: true,
      allowMissing: false,
    }
  }

  if (errorCode === 'QTY_EXCEEDED') {
    return {
      type: 'warn',
      message: 'La cantidad escaneada excede lo pendiente.',
      detail: 'Ajusta la cantidad o continua linea por linea.',
      technicalCode: errorCode,
      scannedCode,
      allowRetry: true,
      allowManual: true,
      allowMissing: false,
    }
  }

  return {
    type: 'error',
    message: apiError.message || 'No se pudo procesar el escaneo.',
    detail: 'Puedes reintentar o usar el modo manual.',
    technicalCode: errorCode,
    scannedCode,
    allowRetry: true,
    allowManual: true,
    allowMissing: true,
  }
}

function StatusChip({ label, ok = false }: { label: string; ok?: boolean }) {
  return (
    <span
      className={`rounded-full px-2.5 py-1 text-[11px] font-semibold ${
        ok
          ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
          : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
      }`}
    >
      {label}
    </span>
  )
}

export default function OrderPickingPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const scannerInputRef = useRef<HTMLInputElement | null>(null)
  const orderId = Number(id || 0)

  const [context, setContext] = useState<PickingContextResponse | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [busy, setBusy] = useState(false)

  const [mode, setMode] = useState<WorkMode>('scanner')
  const [scanCode, setScanCode] = useState('')
  const [scanQty, setScanQty] = useState(1)
  const [manualCode, setManualCode] = useState('')
  const [manualQty, setManualQty] = useState(1)

  const [noteLineId, setNoteLineId] = useState(0)
  const [noteText, setNoteText] = useState('')

  const [missingLineId, setMissingLineId] = useState(0)
  const [missingQty, setMissingQty] = useState(1)
  const [missingReason, setMissingReason] = useState('')

  const [fallbackVisible, setFallbackVisible] = useState(false)
  const [fallbackModalOpen, setFallbackModalOpen] = useState(false)
  const [feedback, setFeedback] = useState<FeedbackState | null>(null)
  const [historyOpen, setHistoryOpen] = useState(false)
  const [history, setHistory] = useState<ScanHistoryItem[]>([])
  const [soundEnabled, setSoundEnabled] = useState(readSoundPreference)
  const [isOnline, setIsOnline] = useState<boolean>(() => navigator.onLine)
  const [progressPulse, setProgressPulse] = useState(false)

  const lines = context?.data.lines || []
  const totals = context?.meta.totals
  const session = context?.meta.session
  const nextPendingLine = lines.find((line) => line.pending_qty > 0) || null

  const completion = useMemo(() => {
    if (!totals) return 0
    return Math.max(0, Math.min(100, Number(totals.completion_pct || 0)))
  }, [totals])
  const canComplete = (totals?.pending_units || 0) <= 0 && !busy
  const syncState: SyncState = !isOnline ? 'offline' : busy ? 'saving' : 'saved'
  const syncLabel =
    syncState === 'offline' ? 'Sin conexion' : syncState === 'saving' ? 'Guardando...' : 'Guardado'

  const focusScanner = () => {
    if (mode !== 'scanner') return
    window.requestAnimationFrame(() => {
      scannerInputRef.current?.focus()
    })
  }

  const pushHistory = (entry: Omit<ScanHistoryItem, 'id' | 'at'>) => {
    setHistory((prev) => [
      {
        id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        at: Date.now(),
        ...entry,
      },
      ...prev,
    ].slice(0, 12))
  }

  const playTone = (variant: 'ok' | 'warn' | 'error') => {
    if (!soundEnabled) return
    try {
      const audioContext = new window.AudioContext()
      const oscillator = audioContext.createOscillator()
      const gain = audioContext.createGain()
      oscillator.connect(gain)
      gain.connect(audioContext.destination)
      oscillator.type = 'sine'
      oscillator.frequency.value = variant === 'ok' ? 740 : variant === 'warn' ? 460 : 320
      gain.gain.value = 0.03
      oscillator.start()
      oscillator.stop(audioContext.currentTime + 0.08)
      window.setTimeout(() => void audioContext.close(), 120)
    } catch {
      // ignore sound failures
    }
  }

  const reload = async () => {
    if (!orderId) return
    setLoading(true)
    setLoadError('')

    try {
      const data = await getPickingContext(orderId)
      setContext(data)
      const required = Boolean(data.meta?.session?.fallback_required)
      setFallbackVisible(required)
      if (required) {
        setFallbackModalOpen(true)
      }
    } catch (error) {
      const apiError = parseDomainError(error)
      setLoadError(apiError.message || 'No se pudo cargar el contexto de alistamiento.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void reload()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [orderId])

  useEffect(() => {
    if (mode === 'scanner' && !fallbackVisible) {
      focusScanner()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mode, fallbackVisible, context?.data.order.id])

  useEffect(() => {
    const onOnline = () => setIsOnline(true)
    const onOffline = () => setIsOnline(false)
    window.addEventListener('online', onOnline)
    window.addEventListener('offline', onOffline)
    return () => {
      window.removeEventListener('online', onOnline)
      window.removeEventListener('offline', onOffline)
    }
  }, [])

  useEffect(() => {
    try {
      window.localStorage.setItem(SOUND_STORAGE_KEY, soundEnabled ? '1' : '0')
    } catch {
      // ignore storage failures
    }
  }, [soundEnabled])

  useEffect(() => {
    setProgressPulse(true)
    const timeoutId = window.setTimeout(() => setProgressPulse(false), 650)
    return () => window.clearTimeout(timeoutId)
  }, [completion])

  useEffect(() => {
    if (feedback?.type !== 'ok') return
    const timeoutId = window.setTimeout(() => setFeedback(null), 3000)
    return () => window.clearTimeout(timeoutId)
  }, [feedback])

  const onScan = async () => {
    const code = scanCode.trim()
    if (!code || !orderId) return

    setBusy(true)
    setFeedback(null)

    try {
      await scanPickingCode(orderId, code, Math.max(1, scanQty))
      setScanCode('')
      await reload()
      setFeedback({
        type: 'ok',
        message: `Listo. Sume ${Math.max(1, scanQty)} unidad${Math.max(1, scanQty) > 1 ? 'es' : ''}.`,
        detail: 'El alistamiento se actualizo correctamente.',
        scannedCode: code,
      })
      pushHistory({
        type: 'ok',
        message: 'Escaneo aplicado',
        code,
        qty: Math.max(1, scanQty),
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      const nextSession = apiError.meta?.session
      const required = apiError.error_code === 'FALLBACK_REQUIRED' || nextSession?.fallback_required === true
      setFallbackVisible(required)
      if (required) {
        setFallbackModalOpen(true)
      }
      const uiFeedback = parseScanFeedback(apiError, code, lines)
      setFeedback(uiFeedback)
      pushHistory({
        type: 'error',
        message: uiFeedback.message,
        code,
        qty: Math.max(1, scanQty),
      })
      playTone(required ? 'warn' : 'error')
    } finally {
      setBusy(false)
      focusScanner()
    }
  }

  const onManualPickByLine = async (orderProductId: number, qty = 1) => {
    if (!orderId) return
    setBusy(true)
    setFeedback(null)

    try {
      await manualPickingAction(orderId, {
        action: 'pick_item',
        order_product_id: orderProductId,
        qty: Math.max(1, qty),
      })
      await reload()
      setFallbackVisible(false)
      setFeedback({
        type: 'ok',
        message: `Listo. Marque ${Math.max(1, qty)} unidad${Math.max(1, qty) > 1 ? 'es' : ''} manualmente.`,
        detail: 'Puedes continuar en manual o volver al escaner.',
      })
      pushHistory({
        type: 'manual',
        message: `Manual +${Math.max(1, qty)}`,
        qty: Math.max(1, qty),
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo alistar manualmente.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onManualPickByCode = async () => {
    if (!orderId) return
    const code = manualCode.trim()
    if (!code) return

    setBusy(true)
    setFeedback(null)

    try {
      await manualPickingAction(orderId, {
        action: 'pick_by_code',
        code,
        qty: Math.max(1, manualQty),
      })
      setManualCode('')
      await reload()
      setFallbackVisible(false)
      setFeedback({
        type: 'ok',
        message: 'Listo. Aplique el codigo en modo manual.',
        detail: 'Puedes seguir con el mismo flujo.',
        scannedCode: code,
      })
      pushHistory({
        type: 'manual',
        message: 'Codigo aplicado en manual',
        code,
        qty: Math.max(1, manualQty),
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo aplicar el codigo manual.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onMarkMissing = async () => {
    if (!orderId || !missingLineId || !missingReason.trim()) return

    setBusy(true)
    setFeedback(null)

    try {
      await manualPickingAction(orderId, {
        action: 'mark_missing',
        order_product_id: missingLineId,
        qty: Math.max(1, missingQty),
        reason: missingReason.trim(),
      })
      setMissingReason('')
      await reload()
      setFallbackVisible(false)
      setFeedback({
        type: 'ok',
        message: 'Faltante registrado.',
        detail: 'El pedido sigue su flujo con cantidad faltante informada.',
      })
      pushHistory({
        type: 'manual',
        message: 'Faltante registrado',
        qty: Math.max(1, missingQty),
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo registrar faltante.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onAddNote = async () => {
    if (!orderId || !noteLineId || !noteText.trim()) return

    setBusy(true)
    setFeedback(null)

    try {
      await manualPickingAction(orderId, {
        action: 'add_note',
        order_product_id: noteLineId,
        note: noteText.trim(),
      })
      setNoteText('')
      await reload()
      setFallbackVisible(false)
      setFeedback({
        type: 'ok',
        message: 'Nota agregada al alistamiento.',
      })
      pushHistory({
        type: 'manual',
        message: 'Nota registrada',
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo agregar la nota.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onActivateFallback = async () => {
    if (!orderId) return
    setBusy(true)
    setFeedback(null)

    try {
      await activatePickingFallback(orderId)
      await reload()
      setFallbackVisible(true)
      setMode('manual')
      setFallbackModalOpen(false)
      setFeedback({
        type: 'ok',
        message: 'Listo. Activamos modo manual para continuar sin errores.',
      })
      pushHistory({
        type: 'manual',
        message: 'Fallback manual activado',
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo activar fallback.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onComplete = async () => {
    if (!orderId) return
    setBusy(true)
    setFeedback(null)

    try {
      await completePicking(orderId)
      await reload()
      setFeedback({
        type: 'ok',
        message: 'Pedido completado correctamente.',
        detail: 'El alistamiento quedo cerrado para este pedido.',
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo completar picking.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onReset = async () => {
    if (!orderId) return
    if (!window.confirm('Esto reinicia el alistamiento. Deseas continuar?')) return

    setBusy(true)
    setFeedback(null)

    try {
      await resetPicking(orderId)
      await reload()
      setFallbackVisible(false)
      setFallbackModalOpen(false)
      setFeedback({
        type: 'ok',
        message: 'Alistamiento reiniciado.',
      })
      pushHistory({
        type: 'manual',
        message: 'Picking reiniciado',
      })
      playTone('ok')
    } catch (error) {
      const apiError = parseDomainError(error)
      setFeedback({
        type: 'error',
        message: apiError.message || 'No se pudo reiniciar picking.',
        technicalCode: apiError.error_code,
      })
      playTone('error')
    } finally {
      setBusy(false)
    }
  }

  const onFeedbackRetry = () => {
    setFeedback(null)
    focusScanner()
  }

  const onFeedbackManual = () => {
    setMode('manual')
    setFallbackModalOpen(false)
  }

  const onFeedbackMissing = () => {
    const targetLineId = nextPendingLine?.order_product_id || 0
    setMode('manual')
    if (targetLineId) {
      setMissingLineId(targetLineId)
    }
    setMissingReason('No encontrado durante escaneo')
  }

  const onApplySuggestedCode = () => {
    if (!feedback?.suggestedCode) return
    setScanCode(feedback.suggestedCode)
    focusScanner()
  }

  const FailStrip = () => {
    const fails = Math.max(0, Math.min(3, session?.scan_consecutive_failures || 0))
    const activeClass =
      fails >= 3 ? 'bg-red-500' : fails === 2 ? 'bg-amber-500' : fails === 1 ? 'bg-amber-400' : 'bg-slate-300 dark:bg-white/15'
    const label =
      fails === 0 ? 'Todo bien' : fails === 1 ? 'Llevas 1 error' : fails === 2 ? 'Cuidado: 2 errores seguidos' : 'Mejor continuemos en manual'
    const labelColor =
      fails >= 3 ? 'text-red-600 dark:text-red-300' : fails === 2 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-white/50'

    return (
      <div className="flex items-center gap-2">
        <div className="flex items-center gap-1">
          <span className={`h-1.5 w-7 rounded-full ${fails >= 1 ? activeClass : 'bg-slate-300 dark:bg-white/15'}`} />
          <span className={`h-1.5 w-7 rounded-full ${fails >= 2 ? activeClass : 'bg-slate-300 dark:bg-white/15'}`} />
          <span className={`h-1.5 w-7 rounded-full ${fails >= 3 ? 'animate-pulse bg-red-500' : 'bg-slate-300 dark:bg-white/15'}`} />
        </div>
        <span className={`text-xs font-semibold ${labelColor}`}>{label}</span>
      </div>
    )
  }

  if (!orderId) {
    return (
      <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        ID de pedido invalido.
      </div>
    )
  }

  if (loading) {
    return (
      <div className="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-white/50">
        Cargando alistamiento...
      </div>
    )
  }

  if (loadError || !context) {
    return (
      <div className="space-y-4">
        <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
          {loadError || 'No se pudo cargar el pedido.'}
        </div>
        <button
          type="button"
          onClick={() => void reload()}
          className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
        >
          Reintentar
        </button>
      </div>
    )
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-xs text-slate-500 dark:text-white/40">Pedidos / Alistamiento</p>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
            Pedido #{context.data.order.id}
          </h1>
        </div>

        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={() => setSoundEnabled((prev) => !prev)}
            className={`rounded-xl px-3 py-2 text-xs font-semibold ${
              soundEnabled
                ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                : 'border border-slate-200 bg-slate-50 text-slate-600 dark:border-white/15 dark:bg-white/5 dark:text-white/60'
            }`}
          >
            {soundEnabled ? 'Sonido: ON' : 'Sonido: OFF'}
          </button>
          <button
            type="button"
            onClick={onComplete}
            disabled={!canComplete}
            className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
          >
            Completar picking
          </button>
          <button
            type="button"
            onClick={onReset}
            disabled={busy}
            className="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Reiniciar
          </button>
          <Link
            to="/dashboard/orders"
            className="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 dark:bg-white/10 dark:text-white"
          >
            Volver
          </Link>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
        <div className="mb-2 flex items-center justify-between text-xs text-slate-500 dark:text-white/40">
          <span>Progreso</span>
          <span>{completion.toFixed(0)}%</span>
        </div>
        <div className="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
          <div className="relative h-2 rounded-full bg-orange-600 transition-all duration-500" style={{ width: `${completion}%` }}>
            {progressPulse && (
              <span className="absolute inset-0 animate-pulse bg-gradient-to-r from-transparent via-white/40 to-transparent" />
            )}
          </div>
        </div>
        <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
          <div className="flex flex-wrap gap-2 text-xs">
            <StatusChip label={`Pedidas: ${totals?.ordered_units ?? 0}`} />
            <StatusChip label={`Alistadas: ${totals?.picked_units ?? 0}`} ok />
            <StatusChip label={`Faltantes: ${totals?.missing_units ?? 0}`} />
            <StatusChip label={`Pendientes: ${totals?.pending_units ?? 0}`} />
          </div>
          <span
            className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ${
              syncState === 'offline'
                ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300'
                : syncState === 'saving'
                  ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                  : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
            }`}
          >
            <span className="h-1.5 w-1.5 rounded-full bg-current" />
            {syncLabel}
          </span>
        </div>
        <div className="mt-3 flex flex-wrap gap-2 text-xs">
          <StatusChip label={`Fallos escaneo: ${session?.scan_consecutive_failures ?? 0}`} ok={!session?.fallback_required} />
        </div>
        <div className="mt-3">
          <FailStrip />
        </div>
      </div>

      {feedback && (
        <div
          className={`rounded-2xl border p-3 text-sm ${
            feedback.type === 'ok'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
              : feedback.type === 'warn'
                ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300'
                : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300'
          }`}
        >
          <p className="font-semibold">{feedback.message}</p>
          {feedback.detail && <p className="mt-1 text-xs opacity-90">{feedback.detail}</p>}
          {(feedback.technicalCode || feedback.scannedCode) && (
            <p className="mt-2 font-mono text-[11px] opacity-70">
              {[feedback.technicalCode, feedback.scannedCode].filter(Boolean).join(' · ')}
            </p>
          )}

          <div className="mt-3 flex flex-wrap gap-2">
            {feedback.allowRetry && (
              <button
                type="button"
                onClick={onFeedbackRetry}
                className="rounded-lg border border-current/30 px-3 py-1.5 text-xs font-semibold"
              >
                Reintentar
              </button>
            )}
            {feedback.allowManual && (
              <button
                type="button"
                onClick={onFeedbackManual}
                className="rounded-lg border border-current/30 px-3 py-1.5 text-xs font-semibold"
              >
                Abrir manual
              </button>
            )}
            {feedback.allowMissing && (
              <button
                type="button"
                onClick={onFeedbackMissing}
                className="rounded-lg border border-current/30 px-3 py-1.5 text-xs font-semibold"
              >
                Marcar faltante
              </button>
            )}
            {feedback.suggestedCode && (
              <button
                type="button"
                onClick={onApplySuggestedCode}
                className="rounded-lg border border-current/30 px-3 py-1.5 text-xs font-semibold"
              >
                Usar sugerencia
              </button>
            )}
          </div>
        </div>
      )}

      {fallbackVisible && (
        <div className="rounded-2xl border border-orange-300 bg-orange-50 p-4 dark:border-orange-500/30 dark:bg-orange-500/10">
          <p className="text-sm font-semibold text-orange-800 dark:text-orange-200">Escaner pausado para evitar errores.</p>
          <p className="mt-1 text-xs text-orange-700 dark:text-orange-300">
            Detectamos 3 fallos seguidos. Te recomiendo continuar en manual y luego volver al escaner.
          </p>
          <div className="mt-3 flex flex-wrap gap-2">
            <button
              type="button"
              onClick={onActivateFallback}
              disabled={busy}
              className="rounded-lg bg-orange-600 px-3 py-2 text-xs font-semibold text-white disabled:opacity-60"
            >
              Activar modo manual
            </button>
            <button
              type="button"
              onClick={() => {
                setMode('manual')
                setFallbackModalOpen(false)
              }}
              className="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-orange-700 dark:bg-white/10 dark:text-orange-200"
            >
              Ir a modo manual
            </button>
            <button
              type="button"
              onClick={() => setFallbackModalOpen(true)}
              className="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-orange-700 dark:bg-white/10 dark:text-orange-200"
            >
              Ver ayuda
            </button>
          </div>
        </div>
      )}

      <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
        <div className="mb-3 flex flex-wrap gap-2">
          <button
            type="button"
            onClick={() => setMode('scanner')}
            className={`rounded-lg px-3 py-2 text-xs font-semibold ${
              mode === 'scanner'
                ? 'bg-orange-600 text-white'
                : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/70'
            }`}
          >
            Escaner
          </button>
          <button
            type="button"
            onClick={() => setMode('manual')}
            className={`rounded-lg px-3 py-2 text-xs font-semibold ${
              mode === 'manual'
                ? 'bg-orange-600 text-white'
                : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/70'
            }`}
          >
            Manual
          </button>
        </div>

        {mode === 'scanner' ? (
          <div className="space-y-3">
            <div className="rounded-2xl border-2 border-orange-300 bg-orange-50/50 p-3 dark:border-orange-500/40 dark:bg-orange-500/5">
              <div className="mb-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wide text-orange-700 dark:text-orange-300">
                <span>Zona de escaneo</span>
                <span>{fallbackVisible ? 'Pausado' : 'Listo'}</span>
              </div>
              <div className="grid gap-3 md:grid-cols-[1fr_120px_170px]">
                <input
                  ref={scannerInputRef}
                  type="text"
                  value={scanCode}
                  onChange={(event) => setScanCode(event.target.value)}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                      event.preventDefault()
                      void onScan()
                    }
                  }}
                  placeholder="Escanea o pega codigo de barras / QR / SKU"
                  disabled={busy || fallbackVisible}
                  className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-mono tracking-wide text-slate-900 outline-none focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-white"
                />
                <div className="flex items-center rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
                  <button
                    type="button"
                    onClick={() => setScanQty((prev) => Math.max(1, prev - 1))}
                    className="w-10 text-lg text-slate-600 hover:text-slate-900 dark:text-white/70 dark:hover:text-white"
                  >
                    -
                  </button>
                  <input
                    type="number"
                    min={1}
                    max={999}
                    value={scanQty}
                    onChange={(event) => setScanQty(Math.max(1, Number(event.target.value) || 1))}
                    className="w-full border-x border-slate-200 bg-transparent py-2 text-center text-sm font-semibold text-slate-900 outline-none dark:border-white/10 dark:text-white"
                  />
                  <button
                    type="button"
                    onClick={() => setScanQty((prev) => Math.min(999, prev + 1))}
                    className="w-10 text-lg text-slate-600 hover:text-slate-900 dark:text-white/70 dark:hover:text-white"
                  >
                    +
                  </button>
                </div>
                <button
                  type="button"
                  onClick={() => void onScan()}
                  disabled={busy || fallbackVisible}
                  className="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                >
                  Aplicar escaneo
                </button>
              </div>
              <div className="mt-2 flex items-center justify-between gap-2 text-xs text-slate-500 dark:text-white/50">
                <p>Compatible con lector USB tipo teclado: mantiene foco automatico en este campo.</p>
                {nextPendingLine && (
                  <p className="rounded-full bg-white px-2 py-1 font-semibold text-slate-700 dark:bg-white/10 dark:text-white/70">
                    Siguiente: {nextPendingLine.product_name}
                  </p>
                )}
              </div>
            </div>

            <div className="rounded-xl border border-slate-200 p-3 dark:border-white/10">
              <div className="mb-2 flex items-center justify-between">
                <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-white/40">
                  Historial de escaneo
                </p>
                <button
                  type="button"
                  onClick={() => setHistoryOpen((prev) => !prev)}
                  className="text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-white/60 dark:hover:text-white"
                >
                  {historyOpen ? 'Ocultar' : 'Ver mas'}
                </button>
              </div>

              {history.length === 0 && (
                <p className="text-xs text-slate-500 dark:text-white/40">Aun no hay eventos de escaneo.</p>
              )}

              {history.length > 0 && (
                <div className="space-y-2">
                  {(historyOpen ? history : history.slice(0, 1)).map((item) => (
                    <div key={item.id} className="flex items-center gap-2 text-xs">
                      <span
                        className={`inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold ${
                          item.type === 'ok'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                            : item.type === 'manual'
                              ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
                              : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300'
                        }`}
                      >
                        {item.type === 'ok' ? '✓' : item.type === 'manual' ? 'M' : '!'}
                      </span>
                      <span className="font-mono text-slate-700 dark:text-white/70">{item.code || '--'}</span>
                      <span className="flex-1 text-slate-600 dark:text-white/60">{item.message}</span>
                      <span className="font-mono text-slate-500 dark:text-white/40">
                        {new Date(item.at).toLocaleTimeString()}
                      </span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="rounded-xl border border-slate-200 p-3 dark:border-white/10">
              <p className="mb-2 text-xs font-semibold uppercase text-slate-500 dark:text-white/40">Manual por codigo</p>
              <div className="grid gap-2 md:grid-cols-[1fr_100px_140px]">
                <input
                  type="text"
                  value={manualCode}
                  onChange={(event) => setManualCode(event.target.value)}
                  placeholder="Codigo"
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                />
                <input
                  type="number"
                  min={1}
                  max={999}
                  value={manualQty}
                  onChange={(event) => setManualQty(Math.max(1, Number(event.target.value) || 1))}
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                />
                <button
                  type="button"
                  onClick={() => void onManualPickByCode()}
                  disabled={busy}
                  className="rounded-lg bg-orange-600 px-3 py-2 text-xs font-semibold text-white disabled:opacity-60"
                >
                  Aplicar
                </button>
              </div>
            </div>

            <div className="rounded-xl border border-slate-200 p-3 dark:border-white/10">
              <p className="mb-2 text-xs font-semibold uppercase text-slate-500 dark:text-white/40">Marcar faltante</p>
              <div className="grid gap-2 md:grid-cols-[1fr_90px_1fr_140px]">
                <select
                  value={missingLineId}
                  onChange={(event) => setMissingLineId(Number(event.target.value) || 0)}
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                >
                  <option value={0}>Selecciona linea</option>
                  {lines.map((line) => (
                    <option key={line.order_product_id} value={line.order_product_id}>
                      {line.product_name}
                    </option>
                  ))}
                </select>
                <input
                  type="number"
                  min={1}
                  max={999}
                  value={missingQty}
                  onChange={(event) => setMissingQty(Math.max(1, Number(event.target.value) || 1))}
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                />
                <input
                  type="text"
                  value={missingReason}
                  onChange={(event) => setMissingReason(event.target.value)}
                  placeholder="Motivo"
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                />
                <button
                  type="button"
                  onClick={() => void onMarkMissing()}
                  disabled={busy}
                  className="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white disabled:opacity-60"
                >
                  Guardar faltante
                </button>
              </div>
            </div>

            <div className="rounded-xl border border-slate-200 p-3 dark:border-white/10">
              <p className="mb-2 text-xs font-semibold uppercase text-slate-500 dark:text-white/40">Agregar nota</p>
              <div className="grid gap-2 md:grid-cols-[1fr_1fr_120px]">
                <select
                  value={noteLineId}
                  onChange={(event) => setNoteLineId(Number(event.target.value) || 0)}
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                >
                  <option value={0}>Selecciona linea</option>
                  {lines.map((line) => (
                    <option key={line.order_product_id} value={line.order_product_id}>
                      {line.product_name}
                    </option>
                  ))}
                </select>
                <input
                  type="text"
                  value={noteText}
                  onChange={(event) => setNoteText(event.target.value)}
                  placeholder="Nota del alistamiento"
                  className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5"
                />
                <button
                  type="button"
                  onClick={() => void onAddNote()}
                  disabled={busy}
                  className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white disabled:opacity-60 dark:bg-white dark:text-slate-900"
                >
                  Guardar nota
                </button>
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
        <h2 className="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Lineas del pedido</h2>
        <div className="space-y-2">
          {lines.length === 0 && (
            <p className="text-xs text-slate-500 dark:text-white/40">Este pedido no tiene lineas para alistar.</p>
          )}

          {lines.map((line) => {
            const complete = line.pending_qty <= 0
            const isNext = nextPendingLine?.order_product_id === line.order_product_id
            return (
              <div
                key={line.order_product_id}
                className={`rounded-xl border p-3 dark:border-white/10 ${
                  isNext
                    ? 'border-orange-300 bg-orange-50/50 dark:border-orange-500/40 dark:bg-orange-500/10'
                    : 'border-slate-200'
                }`}
              >
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <div>
                    <p className="text-sm font-semibold text-slate-900 dark:text-white">
                      {line.product_name}
                      {isNext && (
                        <span className="ml-2 rounded-full bg-orange-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">
                          Siguiente
                        </span>
                      )}
                    </p>
                    <p className="text-xs text-slate-500 dark:text-white/40">
                      Pedido: {line.quantity} | Alistado: {line.qty_picked} | Faltante: {line.qty_missing} | Pendiente: {line.pending_qty}
                    </p>
                  </div>
                  <div className="flex items-center gap-2">
                    <StatusChip label={complete ? 'Completo' : 'Pendiente'} ok={complete} />
                    <button
                      type="button"
                      onClick={() => void onManualPickByLine(line.order_product_id, 1)}
                      disabled={busy || complete}
                      className="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60"
                    >
                      +1 manual
                    </button>
                  </div>
                </div>
                {line.codes.length > 0 && (
                  <p className="mt-2 text-[11px] text-slate-500 dark:text-white/40">
                    Codigos: {line.codes.map((code) => `${code.type}:${code.value}`).join(' | ')}
                  </p>
                )}
              </div>
            )
          })}
        </div>
      </div>

      <div className="text-right">
        <button
          type="button"
          onClick={() => navigate('/dashboard/orders')}
          className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
        >
          Volver a pedidos
        </button>
      </div>

      {fallbackModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
          <div className="w-full max-w-lg rounded-2xl border border-orange-300 bg-white shadow-2xl dark:border-orange-500/30 dark:bg-slate-900">
            <div className="border-b border-orange-200 bg-orange-50 px-5 py-4 dark:border-orange-500/20 dark:bg-orange-500/10">
              <h3 className="text-lg font-bold text-orange-700 dark:text-orange-300">
                Escaneo pausado para evitar errores
              </h3>
              <p className="mt-1 text-sm text-slate-600 dark:text-white/70">
                Detectamos 3 fallos seguidos. Sigamos en manual para completar rapido y sin reprocesos.
              </p>
            </div>

            <div className="space-y-3 px-5 py-4">
              <button
                type="button"
                onClick={() => void onActivateFallback()}
                disabled={busy}
                className="w-full rounded-xl border border-orange-300 bg-orange-600 px-4 py-3 text-left text-sm font-semibold text-white disabled:opacity-60"
              >
                1. Continuar en modo manual (recomendado)
              </button>
              <button
                type="button"
                onClick={() => {
                  setMode('manual')
                  setFallbackModalOpen(false)
                }}
                className="w-full rounded-xl border border-slate-200 px-4 py-3 text-left text-sm font-semibold text-slate-700 dark:border-white/15 dark:text-white/80"
              >
                2. Ir manual sin activar fallback
              </button>
              <button
                type="button"
                onClick={() => setFallbackModalOpen(false)}
                className="w-full rounded-xl border border-slate-200 px-4 py-3 text-left text-sm font-semibold text-slate-700 dark:border-white/15 dark:text-white/80"
              >
                3. Cerrar y revisar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
