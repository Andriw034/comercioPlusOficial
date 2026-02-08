import { useEffect, useMemo } from 'react'
import { Link } from 'react-router-dom'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/button'
import { formatPrice, resolveMediaUrl } from '@/lib/format'
import type { Product } from '@/types/api'

type Props = {
  open: boolean
  product: Product | null
  loading: boolean
  error: string
  onClose: () => void
  onAdd: (product: Product) => void
}

export default function ProductQuickViewModal({
  open,
  product,
  loading,
  error,
  onClose,
  onAdd,
}: Props) {
  useEffect(() => {
    if (!open) return

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose()
    }

    window.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      window.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [open, onClose])

  const image = useMemo(() => resolveMediaUrl(product?.image_url || (product as any)?.image), [product])
  const ratingValue = Number(product?.rating ?? (product as any)?.average_rating)
  const hasRating = Number.isFinite(ratingValue) && ratingValue > 0
  const reviewsCount = Number(product?.reviews_count ?? 0)

  if (!open) return null

  return (
    <div
      className="fixed inset-0 z-50 flex items-start justify-center bg-black/60 px-4 py-6 sm:items-center"
      role="dialog"
      aria-modal="true"
      aria-label="Vista rapida del producto"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) onClose()
      }}
    >
      <div className="w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white text-slate-900 shadow-card dark:border-white/10 dark:bg-panel dark:text-white">
        <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10 sm:px-6">
          <h2 className="text-sm font-semibold text-slate-900 dark:text-white">Vista rapida</h2>
          <button
            type="button"
            onClick={onClose}
            className={buttonVariants('ghost', 'h-9 w-9 rounded-full p-0 text-slate-900 dark:text-white')}
            aria-label="Cerrar vista rapida"
          >
            <span aria-hidden="true">x</span>
          </button>
        </div>

        <div className="max-h-[80vh] overflow-auto p-5 sm:p-6">
          {loading && (
            <div className="flex justify-center py-16">
              <div className="h-10 w-10 animate-spin rounded-full border-2 border-slate-900/10 border-t-brand-500 dark:border-white/20" />
            </div>
          )}

          {!loading && error && (
            <div className="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-200">
              {error}
            </div>
          )}

          {!loading && !error && product && (
            <div className="grid gap-6 lg:grid-cols-[1.05fr_1fr]">
              <div className="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                <div className="relative aspect-square overflow-hidden rounded-xl">
                  {image ? (
                    <img
                      src={image}
                      alt={product.name}
                      className="h-full w-full object-contain"
                      loading="lazy"
                      decoding="async"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center text-sm font-medium text-slate-500 dark:text-white/60">
                      Sin imagen
                    </div>
                  )}

                  <div className="absolute left-3 top-3 flex flex-wrap gap-2">
                    {typeof product.stock === 'number' && product.stock === 0 ? (
                      <Badge variant="danger">Agotado</Badge>
                    ) : typeof product.stock === 'number' && product.stock <= 3 ? (
                      <Badge variant="brand">Ultimas</Badge>
                    ) : null}
                  </div>

                  {hasRating && (
                    <div className="absolute right-3 top-3">
                      <span className="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-900 dark:bg-white/10 dark:text-white">
                        <span aria-hidden>*</span>
                        {ratingValue.toFixed(1)}
                        {reviewsCount > 0 ? <span className="font-medium opacity-80">({reviewsCount})</span> : null}
                      </span>
                    </div>
                  )}
                </div>
              </div>

              <div className="space-y-4">
                <div className="space-y-1">
                  <p className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    ${formatPrice(product.price)}
                  </p>
                  <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{product.name}</h3>
                </div>

                <div className="grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-white/10 dark:bg-white/5">
                  <p className="text-slate-700 dark:text-white/75">
                    <span className="text-slate-500 dark:text-white/60">Stock:</span> {product.stock}
                  </p>

                  <p className="text-slate-700 dark:text-white/75">
                    <span className="text-slate-500 dark:text-white/60">Estado:</span> {product.status || 'Activo'}
                  </p>

                  {product.store?.name ? (
                    <p className="text-slate-700 dark:text-white/75">
                      <span className="text-slate-500 dark:text-white/60">Tienda:</span> {product.store.name}
                    </p>
                  ) : null}
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                  <p className="text-sm leading-relaxed text-slate-700 dark:text-white/75">
                    {product.description || 'Sin descripcion adicional por ahora.'}
                  </p>
                </div>

                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                  <button
                    type="button"
                    onClick={() => onAdd(product)}
                    className={buttonVariants('primary', 'h-10 justify-center text-[13px]')}
                    disabled={typeof product.stock === 'number' && product.stock === 0}
                  >
                    Agregar
                  </button>

                  <Link
                    to={`/product/${product.id}`}
                    onClick={onClose}
                    className={buttonVariants('secondary', 'h-10 justify-center text-[13px]')}
                  >
                    Ver detalles
                  </Link>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}