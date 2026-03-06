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

  const inputClass =
    scanState === 'success'
      ? 'border-emerald-500 bg-emerald-50'
      : scanState === 'error'
        ? 'border-red-500 bg-red-50'
        : isReady
          ? 'border-[#FF6A00] bg-white shadow-[0_0_0_4px_rgba(255,106,0,0.12)]'
          : 'border-[#FF6A00] bg-white'

  return (
    <div className="mb-2 rounded-[11px] border border-[#E2E8F0] bg-white p-4">
      <div className="mb-3 flex items-center gap-3">
        <div className="inline-flex h-9 w-9 items-center justify-center rounded-[9px] bg-[#F1F5F9] text-[#64748B]">
          <Icon name="grid" size={16} />
        </div>
        <div>
          <p className="text-[13px] font-extrabold text-[#0F172A]">Escaner USB</p>
          <p className="text-[11px] text-[#64748B]">Escanea y consulta contra tu catalogo</p>
        </div>
      </div>

      <div className="mb-3 flex flex-col gap-2 lg:flex-row">
        <div className="relative flex-1">
          <span
            className={`pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#FF6A00] ${
              busy ? 'animate-pulse' : ''
            }`}
          >
            <Icon name="search" size={20} />
          </span>
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
            placeholder="Escanea codigo de barras o SKU..."
            className={`w-full rounded-[11px] border-2 px-4 py-4 pl-14 font-mono text-[17px] font-bold tracking-[0.06em] text-[#0F172A] outline-none transition-all placeholder:font-sans placeholder:text-[13px] placeholder:font-semibold placeholder:tracking-normal placeholder:text-[#94A3B8] ${inputClass}`}
          />
        </div>

        <Button
          type="button"
          onClick={onSubmit}
          loading={busy}
          disabled={disabled}
          className="h-[58px] min-w-[190px] rounded-[9px] px-5 text-[12px] font-bold"
        >
          {busy ? 'Consultando...' : 'Consultar codigo'}
        </Button>
      </div>

      {statusMessage ? (
        <div
          className={`rounded-[10px] border px-3 py-2 text-[12px] font-semibold ${
            scanState === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
              : scanState === 'error'
                ? 'border-orange-200 bg-orange-50 text-[#9A3412]'
                : 'border-blue-200 bg-blue-50 text-blue-700'
          }`}
        >
          {statusMessage}
        </div>
      ) : null}

      <div className="mt-3 rounded-[9px] border border-[#FED7AA]/60 bg-[#FFF7ED]/70 px-3 py-2.5">
        <p className="mb-1 text-[10px] font-extrabold uppercase tracking-[0.1em] text-[#92400E]">Tips rapidos</p>
        <div className="grid gap-1 text-[11px] text-[#78350F] sm:grid-cols-3">
          <p>• Escaner listo para leer.</p>
          <p>• Presiona Enter para buscar.</p>
          <p>• Genera codigos automaticos.</p>
        </div>
      </div>

      <div className="mt-3 rounded-[9px] border border-dashed border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2.5">
        <p className="mb-1 text-[10px] font-bold uppercase tracking-[0.1em] text-[#94A3B8]">Como usar</p>
        <div className="space-y-1 text-[12px] text-[#64748B]">
          <p>1. Conecta tu lector por USB o Bluetooth.</p>
          <p>2. Escanea y el codigo aparecera en este campo.</p>
          <p>3. Tu lector puede enviar Enter automaticamente.</p>
        </div>
      </div>
    </div>
  )
}
