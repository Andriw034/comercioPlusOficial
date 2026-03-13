import type { RefObject } from 'react'
import { Camera, ChevronDown, ChevronUp, Loader2 } from 'lucide-react'

export type ReceiveFeedback = {
  type: 'ok' | 'error' | 'warn'
  message: string
  detail?: string
  code?: string
}

type Props = {
  code: string
  qty: number
  reason: string
  reference: string
  busy: boolean
  advancedOpen: boolean
  feedback: ReceiveFeedback | null
  inputRef: RefObject<HTMLInputElement | null>
  onCodeChange: (value: string) => void
  onQtyChange: (value: number) => void
  onReasonChange: (value: string) => void
  onReferenceChange: (value: string) => void
  onOpenCamera: () => void
  onToggleAdvanced: () => void
  onSubmit: () => void
}

const presets = [1, 5, 10]

export default function ReceiveScannerPanel({
  code,
  qty,
  reason,
  reference,
  busy,
  advancedOpen,
  feedback,
  inputRef,
  onCodeChange,
  onQtyChange,
  onReasonChange,
  onReferenceChange,
  onOpenCamera,
  onToggleAdvanced,
  onSubmit,
}: Props) {
  return (
    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
      <h2 className="text-[15px] font-semibold text-slate-900 dark:text-white">Escaner</h2>
      <p className="mt-1 text-[12px] text-slate-500 dark:text-white/50">
        Escanea el codigo del producto y suma stock inmediatamente.
      </p>

      <div className="mt-4 space-y-3">
        <input
          ref={inputRef}
          type="text"
          value={code}
          onChange={(event) => onCodeChange(event.target.value)}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              event.preventDefault()
              onSubmit()
            }
          }}
          placeholder="Escanea el codigo..."
          autoFocus
          className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[16px] font-medium text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-200 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:ring-orange-500/30"
        />

        <div className="flex justify-end">
          <button
            type="button"
            onClick={onOpenCamera}
            className="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
          >
            <Camera size={14} />
            Escanear con camara
          </button>
        </div>

        <div className="grid gap-3 sm:grid-cols-[auto_1fr]">
          <div className="inline-flex h-11 items-center rounded-xl border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5">
            <button
              type="button"
              onClick={() => onQtyChange(Math.max(1, qty - 1))}
              className="h-11 w-11 text-xl text-slate-600 transition-colors hover:text-slate-900 dark:text-white/70 dark:hover:text-white"
              aria-label="Disminuir cantidad"
            >
              -
            </button>
            <input
              type="number"
              min={1}
              value={qty}
              onChange={(event) => onQtyChange(Math.max(1, Number(event.target.value) || 1))}
              className="h-11 w-14 border-x border-slate-200 bg-transparent text-center text-[15px] font-bold text-slate-900 outline-none dark:border-white/10 dark:text-white"
            />
            <button
              type="button"
              onClick={() => onQtyChange(Math.min(999, qty + 1))}
              className="h-11 w-11 text-xl text-slate-600 transition-colors hover:text-slate-900 dark:text-white/70 dark:hover:text-white"
              aria-label="Aumentar cantidad"
            >
              +
            </button>
          </div>

          <div className="flex flex-wrap gap-2">
            {presets.map((preset) => (
              <button
                key={preset}
                type="button"
                onClick={() => onQtyChange(preset)}
                className={`rounded-lg border px-3 py-2 text-[12px] font-semibold transition-colors ${
                  qty === preset
                    ? 'border-orange-400 bg-orange-50 text-orange-700 dark:border-orange-500/40 dark:bg-orange-500/10 dark:text-orange-300'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-white/10 dark:bg-white/5 dark:text-white/70'
                }`}
              >
                {preset}
              </button>
            ))}
          </div>
        </div>

        <button
          type="button"
          onClick={onToggleAdvanced}
          className="inline-flex items-center gap-2 text-[12px] font-semibold text-slate-600 transition-colors hover:text-slate-900 dark:text-white/60 dark:hover:text-white"
        >
          {advancedOpen ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
          Opciones avanzadas
        </button>

        {advancedOpen && (
          <div className="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
            <label className="space-y-1">
              <span className="text-[12px] font-medium text-slate-700 dark:text-white/70">Motivo</span>
              <select
                value={reason}
                onChange={(event) => onReasonChange(event.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
              >
                <option value="purchase">Compra</option>
                <option value="return">Devolucion</option>
                <option value="adjustment">Ajuste</option>
              </select>
            </label>

            <label className="space-y-1">
              <span className="text-[12px] font-medium text-slate-700 dark:text-white/70">Referencia</span>
              <input
                type="text"
                value={reference}
                onChange={(event) => onReferenceChange(event.target.value)}
                placeholder="Factura o nota"
                className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
              />
            </label>
          </div>
        )}

        {feedback && (
          <div
            className={`rounded-xl border px-3 py-2 text-sm ${
              feedback.type === 'ok'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
                : feedback.type === 'warn'
                  ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300'
                  : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300'
            }`}
          >
            <p className="font-semibold">{feedback.message}</p>
            {feedback.detail && <p className="mt-1 text-[12px] opacity-90">{feedback.detail}</p>}
            {feedback.code && <p className="mt-1 font-mono text-[11px] opacity-75">{feedback.code}</p>}
          </div>
        )}

        <button
          type="button"
          onClick={onSubmit}
          disabled={busy || !code.trim() || qty < 1}
          className="hidden h-11 w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 text-sm font-semibold text-white transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60 sm:inline-flex"
        >
          {busy && <Loader2 size={15} className="animate-spin" />}
          Sumar al inventario
        </button>
      </div>

      <div className="fixed bottom-4 left-4 right-4 z-30 sm:hidden">
        <button
          type="button"
          onClick={onSubmit}
          disabled={busy || !code.trim() || qty < 1}
          className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 text-sm font-semibold text-white shadow-lg transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {busy && <Loader2 size={16} className="animate-spin" />}
          Sumar al inventario
        </button>
      </div>
    </section>
  )
}
