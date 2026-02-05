import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Product } from '@/types/api'
import { formatPrice } from '@/lib/format'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/Button'

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

  if (loading) {
    return (
      <div className="flex justify-center py-20">
        <div className="h-10 w-10 animate-spin rounded-full border-2 border-white/20 border-t-brand-500" />
      </div>
    )
  }

  if (error) {
    return (
      <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">
        {error}
      </GlassCard>
    )
  }

  if (!product) {
    return (
      <GlassCard className="text-center">
        <p className="text-white/70">Producto no encontrado.</p>
        <Link to="/products" className={buttonVariants('secondary')}>Ver todos los productos</Link>
      </GlassCard>
    )
  }

  return (
    <div className="space-y-8">
      <nav className="flex text-sm text-white/60" aria-label="Breadcrumb">
        <ol className="inline-flex items-center gap-2">
          <li>
            <Link to="/" className="hover:text-white">Inicio</Link>
          </li>
          <li className="text-white/40">/</li>
          <li>
            <Link to={`/products?category_id=${product.category?.id}`} className="hover:text-white">
              {product.category?.name || 'Productos'}
            </Link>
          </li>
          <li className="text-white/40">/</li>
          <li className="text-white/80">{product.name}</li>
        </ol>
      </nav>

      <div className="grid gap-8 lg:grid-cols-[1.1fr_1fr]">
        <GlassCard className="p-0 overflow-hidden">
          <div className="aspect-[4/3] bg-white/5">
            <img
              src={product.image_url || '/placeholder-product.png'}
              alt={product.name}
              className="h-full w-full object-cover"
            />
          </div>
        </GlassCard>

        <GlassCard className="space-y-6">
          <div className="space-y-2">
            <Badge variant="brand">Producto destacado</Badge>
            <h1 className="text-3xl font-semibold text-white">{product.name}</h1>
            <p className="text-2xl text-brand-200 font-semibold">${formatPrice(product.price)}</p>
            <p className="text-sm text-white/60">{product.description}</p>
          </div>

          <div className="flex items-center gap-2 text-sm text-white/60">
            <span>{product.rating || '0.0'} ?</span>
            <span>•</span>
            <span>{product.reviews_count || 0} reseñas</span>
          </div>

          <div className="flex items-center gap-2 text-sm text-white/60">
            <span>Stock: {product.stock}</span>
          </div>

          <div className="flex flex-wrap gap-3">
            <button
              onClick={() => alert(`Producto "${product.name}" agregado al carrito (funcionalidad pendiente)`)}
              className={buttonVariants('primary')}
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

          <div className="border-t border-white/10 pt-6">
            <p className="text-sm text-white/60">Vendido por</p>
            <Link
              to={`/store/${product.store?.slug || product.store?.id}`}
              className="text-base font-semibold text-white hover:text-brand-200"
            >
              {product.store?.name}
            </Link>
            <p className="text-sm text-white/50">{product.store?.description}</p>
          </div>
        </GlassCard>
      </div>
    </div>
  )
}
