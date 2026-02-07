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

  return (
    <article className="h-full rounded-2xl border border-white/10 bg-white/5 p-4 shadow-soft transition-all duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-white/10">
      <div className="flex h-full flex-col gap-3">
        <button
          type="button"
          onClick={() => onImageClick(product)}
          className="group overflow-hidden rounded-2xl border border-white/15 bg-white/90 p-1.5 text-left transition-colors hover:border-brand-300/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300/60"
          aria-label={`Ver imagen grande y detalles de ${product.name}`}
        >
          <div className="aspect-[4/3] overflow-hidden rounded-xl bg-white">
            {image ? (
              <img
                src={image}
                alt={product.name}
                className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
              />
            ) : (
              <div className="flex h-full w-full items-center justify-center px-4 text-center text-sm font-medium text-ink/60">
                Sin imagen
              </div>
            )}
          </div>
        </button>

        <div className="space-y-1.5">
          <p className="text-2xl font-bold tracking-tight text-brand-200">
            ${formatPrice(product.price)}
          </p>
          <h3 className="line-clamp-2 min-h-[2.6rem] text-sm font-semibold leading-snug text-white">
            {product.name}
          </h3>
          {product.category && (
            <div>
              <Badge variant="neutral">{product.category.name}</Badge>
            </div>
          )}
          <p className="text-xs text-white/60">
            Stock: {product.stock}
          </p>
          {hasRating && (
            <p className="text-xs text-white/70">
              {ratingValue.toFixed(1)} / 5
              {product.reviews_count ? ` · ${product.reviews_count} reseñas` : ''}
            </p>
          )}
        </div>

        <div className="mt-auto grid grid-cols-2 gap-2 pt-0.5">
          <Link to={`/product/${product.id}`} className={buttonVariants('secondary', 'justify-center')}>
            Ver detalles
          </Link>
          <button
            type="button"
            onClick={() => onAdd(product)}
            className={buttonVariants('primary', 'justify-center')}
          >
            Agregar
          </button>
        </div>
      </div>
    </article>
  )
}
