import { useEffect, useRef } from 'react'
import { Loader2 } from 'lucide-react'

type CategoryItem = {
  id: number
  name: string
}

type Props = {
  open: boolean
  busy: boolean
  code: string
  name: string
  categoryId: number | ''
  price: string
  initialQty: number
  categories: CategoryItem[]
  onCodeChange: (value: string) => void
  onNameChange: (value: string) => void
  onCategoryChange: (value: number | '') => void
  onPriceChange: (value: string) => void
  onInitialQtyChange: (value: number) => void
  onClose: () => void
  onSubmit: () => void
}

export default function QuickCreateProductModal({
  open,
  busy,
  code,
  name,
  categoryId,
  price,
  initialQty,
  categories,
  onCodeChange,
  onNameChange,
  onCategoryChange,
  onPriceChange,
  onInitialQtyChange,
  onClose,
  onSubmit,
}: Props) {
  const dialogRef = useRef<HTMLDivElement | null>(null)
  const firstInputRef = useRef<HTMLInputElement | null>(null)

  useEffect(() => {
    if (!open) return

    const previous = document.activeElement as HTMLElement | null
    document.body.style.overflow = 'hidden'
    window.setTimeout(() => firstInputRef.current?.focus(), 0)

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        event.preventDefault()
        onClose()
        return
      }

      if (event.key !== 'Tab') return

      const dialog = dialogRef.current
      if (!dialog) return
      const nodes = dialog.querySelectorAll<HTMLElement>(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
      )
      const focusables = Array.from(nodes).filter((node) => !node.hasAttribute('disabled'))
      if (focusables.length === 0) return

      const first = focusables[0]
      const last = focusables[focusables.length - 1]

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
      }
    }

    window.addEventListener('keydown', onKeyDown)

    return () => {
      window.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
      previous?.focus()
    }
  }, [open, onClose])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
      <div
        ref={dialogRef}
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-create-title"
        className="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-slate-900"
      >
        <h3 id="quick-create-title" className="text-[18px] font-bold text-slate-900 dark:text-white">
          No encuentro este codigo
        </h3>
        <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
          Quieres crear el producto y sumarlo de una vez al inventario?
        </p>

        <div className="mt-4 space-y-3">
          <input
            ref={firstInputRef}
            type="text"
            value={code}
            onChange={(event) => onCodeChange(event.target.value)}
            placeholder="Codigo"
            className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
          />

          <input
            type="text"
            value={name}
            onChange={(event) => onNameChange(event.target.value)}
            placeholder="Nombre del producto (requerido)"
            className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
          />

          <select
            value={categoryId}
            onChange={(event) => onCategoryChange(event.target.value ? Number(event.target.value) : '')}
            className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
          >
            <option value="">Categoria (opcional)</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>

          <div className="grid gap-3 sm:grid-cols-2">
            <input
              type="number"
              min={0}
              value={price}
              onChange={(event) => onPriceChange(event.target.value)}
              placeholder="Precio (opcional)"
              className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            <input
              type="number"
              min={1}
              value={initialQty}
              onChange={(event) => onInitialQtyChange(Math.max(1, Number(event.target.value) || 1))}
              placeholder="Cantidad inicial"
              className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
          </div>
        </div>

        <div className="mt-5 flex flex-wrap justify-end gap-2">
          <button
            type="button"
            onClick={onClose}
            className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/15 dark:bg-white/5 dark:text-white"
          >
            Cancelar
          </button>
          <button
            type="button"
            onClick={onSubmit}
            disabled={busy || name.trim().length === 0}
            className="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {busy && <Loader2 size={15} className="animate-spin" />}
            Crear y sumar
          </button>
        </div>
      </div>
    </div>
  )
}
