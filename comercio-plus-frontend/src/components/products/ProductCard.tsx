import { Link } from 'react-router-dom'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/button'
import { formatPrice, resolveMediaUrl } from '@/lib/format'
import type { Product } from '@/types/api'

type Props = {
  product: Product
  onAdd: (product: Product) => void
  onImageClick: (product: Product) => void
}

export default function ProductCard({ product, onAdd, onImageClick }: Props) {
  const image = resolveMediaUrl(product.image_url || product.image)

  const ratingValue = Number(product.rating ?? product.average_rating)
  const hasRating = Number.isFinite(ratingValue) && ratingValue > 0

  const reviewsCount = Number(product.reviews_count ?? 0)
  const hasReviews = Number.isFinite(reviewsCount) && reviewsCount > 0
  const showLowStockBadge = typeof product.stock === 'number' && product.stock <= 3 && product.stock > 0
  const showOutOfStockBadge = typeof product.stock === 'number' && product.stock === 0

  return (
    <article className="group h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:shadow-md dark:border-white/10 dark:bg-white/5">
      <div className="flex h-full flex-col">
        <button
          type="button"
          onClick={() => onImageClick(product)}
          className="relative block text-left"
          aria-label={`Ver imagen grande y detalles de ${product.name}`}
        >
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
            <div className="relative aspect-square overflow-hidden rounded-xl">
              {image ? (
                <img
                  src={image}
                  alt={product.name}
                  className="h-full w-full object-contain transition-transform duration-200 group-hover:scale-[1.02]"
                  loading="lazy"
                  decoding="async"
                />
              ) : (
                <div className="flex h-full w-full items-center justify-center px-4 text-center text-[13px] font-medium text-slate-500 dark:text-white/60">
                  Sin imagen
                </div>
              )}

              {(showLowStockBadge || showOutOfStockBadge) && (
                <div className="absolute left-2 top-2 flex flex-wrap gap-2">
                  {showLowStockBadge && <Badge variant="brand">Ultimas</Badge>}
                  {showOutOfStockBadge && <Badge variant="danger">Agotado</Badge>}
                </div>
              )}
            </div>
          </div>
        </button>

        <div className="flex flex-1 flex-col gap-2 px-4 pt-3 pb-4">
          <div className="flex items-end justify-between gap-3">
            <p className="text-[20px] font-extrabold leading-[1] text-slate-900 dark:text-white sm:text-[22px]">
              ${formatPrice(product.price)}
            </p>

            {hasRating && (
              <div className="flex items-center gap-1 text-[12px] text-slate-500 dark:text-white/60">
                <span className="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[12px] text-slate-900 dark:bg-white/10 dark:text-white">
                  <span aria-hidden>*</span>
                  <span className="font-semibold">{ratingValue.toFixed(1)}</span>
                  {hasReviews ? <span className="opacity-80">({reviewsCount})</span> : null}
                </span>
              </div>
            )}
          </div>

          <h3 className="line-clamp-2 min-h-[34px] text-[13px] font-semibold leading-[1.25] text-slate-900 dark:text-white sm:min-h-[38px] sm:text-[14px]">
            {product.name}
          </h3>

          <p className="text-[12px] text-slate-500 dark:text-white/60">Stock: {product.stock}</p>

          <div className="mt-auto grid grid-cols-2 gap-2 pt-2">
            <Link to={`/product/${product.id}`} className={buttonVariants('secondary', 'h-10 justify-center text-[13px]')}>
              Ver
            </Link>

            <button
              type="button"
              onClick={() => onAdd(product)}
              className={buttonVariants('primary', 'h-10 justify-center text-[13px] disabled:cursor-not-allowed disabled:opacity-50')}
              disabled={typeof product.stock === 'number' && product.stock === 0}
            >
              Agregar
            </button>
          </div>
        </div>
      </div>
    </article>
  )
}