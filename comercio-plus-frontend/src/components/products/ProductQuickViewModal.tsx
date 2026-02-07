import { useEffect } from 'react'
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
      if (event.key === 'Escape') {
        onClose()
      }
    }

    window.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      window.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [open, onClose])

  if (!open) return null

  const image = resolveMediaUrl(product?.image_url || product?.image)
  const ratingValue = Number(product?.rating ?? product?.average_rating)
  const hasRating = Number.isFinite(ratingValue) && ratingValue > 0

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/70 px-4 py-6 backdrop-blur-sm sm:items-center">
      <div className="w-full max-w-5xl rounded-3xl border border-white/15 bg-panel shadow-card">
        <div className="flex items-center justify-between border-b border-white/10 px-5 py-4 sm:px-6">
          <h2 className="text-base font-semibold text-white">Vista rápida del producto</h2>
          <button
            type="button"
            onClick={onClose}
            className={buttonVariants('ghost', 'h-9 w-9 rounded-full p-0 text-white')}
            aria-label="Cerrar vista rápida"
          >
            <span aria-hidden="true">×</span>
          </button>
        </div>

        <div className="max-h-[80vh] overflow-auto p-5 sm:p-6">
          {loading && (
            <div className="flex justify-center py-16">
              <div className="h-10 w-10 animate-spin rounded-full border-2 border-white/20 border-t-brand-500" />
            </div>
          )}

          {!loading && error && (
            <div className="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
              {error}
            </div>
          )}

          {!loading && !error && product && (
            <div className="grid gap-6 lg:grid-cols-[1.15fr_1fr]">
              <div className="overflow-hidden rounded-2xl border border-white/15 bg-white/90 p-3">
                <div className="aspect-[4/3] overflow-hidden rounded-xl bg-white">
                  {image ? (
                    <img src={image} alt={product.name} className="h-full w-full object-cover" />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center text-sm font-medium text-ink/60">
                      Sin imagen
                    </div>
                  )}
                </div>
              </div>

              <div className="space-y-4">
                <p className="text-3xl font-bold tracking-tight text-brand-200">
                  ${formatPrice(product.price)}
                </p>
                <h3 className="text-xl font-semibold text-white">{product.name}</h3>
                {product.category && <Badge variant="neutral">{product.category.name}</Badge>}

                <div className="space-y-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                  <p className="text-sm text-white/75">
                    <span className="text-white/50">Stock:</span> {product.stock}
                  </p>
                  {hasRating && (
                    <p className="text-sm text-white/75">
                      <span className="text-white/50">Rating:</span> {ratingValue.toFixed(1)} / 5
                      {product.reviews_count ? ` · ${product.reviews_count} reseñas` : ''}
                    </p>
                  )}
                  <p className="text-sm text-white/75">
                    <span className="text-white/50">Estado:</span> {product.status || 'Activo'}
                  </p>
                  {product.store?.name && (
                    <p className="text-sm text-white/75">
                      <span className="text-white/50">Tienda:</span> {product.store.name}
                    </p>
                  )}
                </div>

                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                  <p className="text-sm leading-relaxed text-white/75">
                    {product.description || 'Sin descripción adicional por ahora.'}
                  </p>
                </div>

                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                  <button
                    type="button"
                    onClick={() => onAdd(product)}
                    className={buttonVariants('primary', 'justify-center')}
                  >
                    Agregar
                  </button>
                  <Link to={`/product/${product.id}`} onClick={onClose} className={buttonVariants('secondary', 'justify-center')}>
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
