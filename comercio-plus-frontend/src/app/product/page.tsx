import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Product } from '@/types/api'
import { formatPrice, resolveMediaUrl } from '@/lib/format'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/button'

export default function ProductDetail() {
  const { id } = useParams()
  const [product, setProduct] = useState<Product | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [isInWishlist, setIsInWishlist] = useState(false)

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true)
        setError('')
        const response = await API.get(`/products/${id}`)
        setProduct(response.data?.data || response.data)
      } catch (err: any) {
        console.error('Error loading product:', err)
        setError(err.response?.data?.message || 'Error al cargar el producto. Inténtalo de nuevo.')
      } finally {
        setLoading(false)
      }
    }

    load()
  }, [id])

  const imageSrc = useMemo(() => {
    if (!product) return ''
    return resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png'
  }, [product])

  const ratingValue = useMemo(() => Number(product?.rating ?? (product as any)?.average_rating), [product])
  const hasRating = Number.isFinite(ratingValue) && ratingValue > 0
  const reviewsCount = Number(product?.reviews_count ?? 0)

  if (loading) {
    return (
      <div className="flex justify-center py-20">
        <div className="h-10 w-10 animate-spin rounded-full border-2 border-slate-900/10 dark:border-white/20 border-t-brand-500" />
      </div>
    )
  }

  if (error) {
    return (
      <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">
        {error}
      </GlassCard>
    )
  }

  if (!product) {
    return (
      <GlassCard className="text-center">
        <p className="text-slate-600 dark:text-white/70">Producto no encontrado.</p>
        <Link to="/products" className={buttonVariants('secondary')}>
          Ver todos los productos
        </Link>
      </GlassCard>
    )
  }

  return (
    <div className="space-y-6">
      {/* Breadcrumb limpio */}
      <nav className="flex text-sm text-slate-600 dark:text-white/60" aria-label="Breadcrumb">
        <ol className="inline-flex items-center gap-2">
          <li>
            <Link to="/" className="hover:text-slate-900 dark:hover:text-white">
              Inicio
            </Link>
          </li>
          <li className="opacity-50">/</li>
          <li>
            <Link
              to={`/products?category_id=${product.category?.id}`}
              className="hover:text-slate-900 dark:hover:text-white"
            >
              {product.category?.name || 'Productos'}
            </Link>
          </li>
          <li className="opacity-50">/</li>
          <li className="text-slate-900 dark:text-white/90 line-clamp-1">{product.name}</li>
        </ol>
      </nav>

      <div className="grid gap-6 lg:grid-cols-[1.1fr_1fr]">
        {/* Imagen estilo Temu (cuadrada, foco en foto) */}
        <GlassCard className="p-0 overflow-hidden">
          <div className="relative aspect-square bg-white">
            <img src={imageSrc} alt={product.name} className="h-full w-full object-cover" />

            {/* Badges arriba */}
            <div className="absolute left-4 top-4 flex flex-wrap gap-2">
              {product.category?.name ? <Badge variant="neutral">{product.category.name}</Badge> : null}
              {typeof product.stock === 'number' && product.stock === 0 ? (
                <Badge variant="danger">Agotado</Badge>
              ) : typeof product.stock === 'number' && product.stock <= 3 ? (
                <Badge variant="brand">¡Últimas!</Badge>
              ) : null}
            </div>

            {/* Rating tipo pill */}
            {hasRating && (
              <div className="absolute right-4 top-4">
                <span className="inline-flex items-center gap-1 rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold text-slate-900 backdrop-blur dark:bg-slate-900/40 dark:text-white">
                  <span aria-hidden>★</span>
                  {ratingValue.toFixed(1)}
                  {reviewsCount > 0 ? <span className="font-medium opacity-80">({reviewsCount})</span> : null}
                </span>
              </div>
            )}
          </div>
        </GlassCard>

        {/* Info */}
        <GlassCard className="space-y-5">
          <div className="space-y-2">
            <div className="flex flex-wrap items-center gap-2">
              <Badge variant="brand">Producto destacado</Badge>
              {typeof product.stock === 'number' ? (
                <span className="text-xs text-slate-600 dark:text-white/60">
                  Stock: <span className="font-semibold text-slate-900 dark:text-white">{product.stock}</span>
                </span>
              ) : null}
            </div>

            <h1 className="text-2xl sm:text-3xl font-semibold text-slate-900 dark:text-white">
              {product.name}
            </h1>

            <div className="flex items-end justify-between gap-3">
              <p className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                ${formatPrice(product.price)}
              </p>
            </div>

            {product.description ? (
              <p className="text-sm text-slate-600 dark:text-white/70">{product.description}</p>
            ) : (
              <p className="text-sm text-slate-500 dark:text-white/60">Sin descripción.</p>
            )}
          </div>

          {/* Acciones */}
          <div className="flex flex-wrap gap-3">
            <button
              onClick={() => alert(`Producto "${product.name}" agregado al carrito (funcionalidad pendiente)`)}
              className={buttonVariants('primary')}
              disabled={typeof product.stock === 'number' && product.stock === 0}
            >
              Agregar al carrito
            </button>

            <button
              onClick={() => setIsInWishlist((prev) => !prev)}
              className={buttonVariants('secondary')}
            >
              {isInWishlist ? 'En favoritos' : 'Guardar'}
            </button>
          </div>

          {/* Tienda */}
          <div className="border-t border-white/10 pt-5">
            <p className="text-sm text-slate-600 dark:text-white/60">Vendido por</p>
            <Link
              to={`/store/${(product as any)?.store?.slug || (product as any)?.store?.id}`}
              className="text-base font-semibold text-slate-900 dark:text-white hover:text-brand-500"
            >
              {(product as any)?.store?.name || 'Tienda'}
            </Link>
            {(product as any)?.store?.description ? (
              <p className="text-sm text-slate-500 dark:text-white/60">
                {(product as any)?.store?.description}
              </p>
            ) : null}
          </div>
        </GlassCard>
      </div>
    </div>
  )
}
