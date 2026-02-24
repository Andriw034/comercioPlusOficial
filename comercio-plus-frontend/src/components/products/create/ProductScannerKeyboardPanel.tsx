import type { RefObject } from 'react'
import Button from '@/components/ui/button'
import { Icon } from '@/components/Icon'

export type ScanState = 'idle' | 'ready' | 'success' | 'error'

type Props = {
  inputRef: RefObject<HTMLInputElement | null>
  code: string
  scanState?: ScanState
  statusMessage?: string
  disabled?: boolean
  busy?: boolean
  onCodeChange: (value: string) => void
  onSubmit: () => void
}

export default function ProductScannerKeyboardPanel({
  inputRef,
  code,
  scanState = 'idle',
  statusMessage,
  disabled = false,
  busy = false,
  onCodeChange,
  onSubmit,
}: Props) {
  const isReady = code.trim().length > 0

  const stateClass =
    scanState === 'success'
      ? 'border-emerald-400 bg-emerald-50/70 ring-4 ring-emerald-100 dark:bg-emerald-500/10 dark:ring-emerald-500/15'
      : scanState === 'error'
        ? 'border-red-400 bg-red-50/70 ring-4 ring-red-100 dark:bg-red-500/10 dark:ring-red-500/15'
        : isReady
          ? 'border-orange-400 bg-orange-50/70 ring-4 ring-orange-100 dark:bg-orange-500/10 dark:ring-orange-500/15'
          : 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5'

  return (
    <div className="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
      <div className="mb-4 flex items-center gap-3">
        <div
          className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-all ${
            isReady
              ? 'bg-gradient-to-br from-[#FF6B35] to-[#E65A2B] text-white shadow-[0_4px_10px_rgba(255,107,53,0.35)]'
              : 'bg-slate-100 text-slate-400 dark:bg-white/10 dark:text-slate-500'
          }`}
        >
          <Icon name="grid" size={16} />
        </div>
        <div className="min-w-0 flex-1">
          <p className="text-[13px] font-bold text-slate-900 dark:text-white">Lector de barras fisico</p>
          <p className="text-[11px] text-slate-500 dark:text-slate-400">Escanea y validamos el codigo en catalogo</p>
        </div>
      </div>

      <div className="mb-3 flex flex-col gap-2 sm:flex-row">
        <input
          ref={inputRef}
          type="text"
          value={code}
          disabled={disabled}
          onChange={(event) => onCodeChange(event.target.value)}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              event.preventDefault()
              onSubmit()
            }
          }}
          placeholder="Escanea aqui o escribe el codigo..."
          className={`w-full rounded-xl border-2 px-4 py-3 font-mono text-[16px] font-bold tracking-wide text-slate-900 outline-none transition-all placeholder:font-sans placeholder:text-[13px] placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 dark:text-white dark:placeholder:text-slate-500 ${stateClass}`}
        />

        <Button
          type="button"
          onClick={onSubmit}
          loading={busy}
          disabled={disabled}
          className="h-[52px] rounded-xl px-4 text-[12px] font-semibold sm:min-w-[170px]"
        >
          {busy ? 'Consultando...' : 'Consultar codigo'}
        </Button>
      </div>

      {statusMessage && (
        <div
          className={`mb-3 rounded-xl border px-3 py-2 text-[12px] font-medium ${
            scanState === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
              : scanState === 'error'
                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300'
                : 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300'
          }`}
        >
          {statusMessage}
        </div>
      )}

      <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3.5 py-3 dark:border-white/10 dark:bg-white/5">
        <p className="mb-2.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Como usar</p>
        <div className="space-y-2 text-[12px] text-slate-600 dark:text-slate-400">
          <p>1. Conecta tu lector por USB o Bluetooth.</p>
          <p>2. Escanea y el codigo aparecera en este campo.</p>
          <p>3. El lector suele enviar Enter automaticamente.</p>
        </div>
      </div>
    </div>
  )
}

