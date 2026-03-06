import { useCallback, useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import { useCart } from '@/context/CartContext'

interface ApiProduct {
  id: number | string
  name: string
  description: string | null
  price: number
  stock: number
  image: string | null
  image_url: string | null
  store_id: number | string | null
  category: { id: number; name: string } | null
  store: { id: number | string; name: string } | null
}

function toSafe(value: unknown, fallback: string): string {
  return typeof value === 'string' && value.trim().length > 0 ? value.trim() : fallback
}

function toNum(value: unknown): number {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

function resolveImage(product: ApiProduct): string {
  return toSafe(product.image_url, toSafe(product.image, ''))
}

// --- Skeleton ---
function DetailSkeleton() {
  return (
    <div className="grid grid-cols-1 gap-10 lg:grid-cols-2 animate-pulse">
      <div className="h-[460px] rounded-md bg-slate-200" />
      <div className="space-y-4">
        <div className="h-5 w-24 rounded bg-slate-200" />
        <div className="h-8 w-3/4 rounded bg-slate-200" />
        <div className="h-8 w-1/3 rounded bg-slate-200" />
        <div className="space-y-2">
          <div className="h-4 w-full rounded bg-slate-200" />
          <div className="h-4 w-5/6 rounded bg-slate-200" />
          <div className="h-4 w-2/3 rounded bg-slate-200" />
        </div>
        <div className="h-6 w-24 rounded-full bg-slate-200" />
        <div className="flex gap-3">
          <div className="h-11 w-40 rounded-lg bg-slate-200" />
          <div className="h-11 w-24 rounded-lg bg-slate-200" />
        </div>
      </div>
    </div>
  )
}

export default function ProductDetail() {
  const { id } = useParams<{ id: string }>()
  const { addToCart } = useCart()

  const [product, setProduct] = useState<ApiProduct | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [added, setAdded] = useState(false)

  const load = useCallback(async () => {
    if (!id) return
    setLoading(true)
    setError('')

    try {
      const resp = await API.get<{ data: ApiProduct }>(`/products/${id}`)
      setProduct(resp.data?.data ?? null)
    } catch {
      setError('No se pudo cargar el producto. Verifica tu conexión e inténtalo de nuevo.')
      setProduct(null)
    } finally {
      setLoading(false)
    }
  }, [id])

  useEffect(() => {
    void load()
  }, [load])

  const handleAddToCart = () => {
    if (!product) return
    addToCart({
      id: String(product.id),
      name: toSafe(product.name, 'Producto'),
      price: toNum(product.price),
      image: resolveImage(product),
      seller: toSafe(product.store?.name, 'ComercioPlus'),
      storeId: String(product.store_id ?? product.store?.id ?? ''),
    })
    setAdded(true)
    setTimeout(() => setAdded(false), 2000)
  }

  const breadcrumb = (
    <div className="mb-6 flex flex-wrap items-center gap-2 text-sm">
      <Link to="/stores" className="font-medium text-slate-600 transition hover:text-slate-900">
        Tiendas
      </Link>
      <span className="text-slate-400">/</span>
      <Link to="/products" className="font-medium text-slate-600 transition hover:text-slate-900">
        Productos
      </Link>
      <span className="text-slate-400">/</span>
      <span className="font-semibold text-slate-900 truncate max-w-[180px]">
        {loading ? 'Cargando...' : (product?.name ?? 'Detalle')}
      </span>
    </div>
  )

  return (
    <div className="min-h-screen bg-dark-50">
      <main className="mx-auto max-w-7xl px-6 py-10 lg:px-10">
        {breadcrumb}

        <div className="mb-6">
          <Link to="/products" className="text-body-sm text-dark-600 hover:text-primary">
            ← Volver a productos
          </Link>
        </div>

        {loading ? (
          <DetailSkeleton />
        ) : error ? (
          <Card className="text-center py-10">
            <p className="mb-4 text-body text-dark-600">{error}</p>
            <Button variant="primary" onClick={() => void load()}>
              Reintentar
            </Button>
          </Card>
        ) : !product ? (
          <Card className="text-center py-10">
            <h2 className="mb-3 text-h2">Producto no encontrado</h2>
            <Link to="/products">
              <Button variant="primary">Volver a productos</Button>
            </Link>
          </Card>
        ) : (
          <div className="grid grid-cols-1 gap-10 lg:grid-cols-2">
            {/* Image */}
            {resolveImage(product) ? (
              <div className="h-[460px] overflow-hidden rounded-md bg-slate-100">
                <img
                  src={resolveImage(product)}
                  alt={product.name}
                  className="h-full w-full object-cover"
                  onError={(e) => {
                    const target = e.currentTarget
                    target.style.display = 'none'
                    const parent = target.parentElement
                    if (parent) parent.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                  }}
                />
              </div>
            ) : (
              <div className="h-[460px] overflow-hidden rounded-md bg-gradient-to-br from-primary to-secondary" />
            )}

            {/* Info */}
            <Card>
              {product.category ? (
                <div className="mb-4">
                  <Badge variant="info">{product.category.name}</Badge>
                </div>
              ) : null}

              <h1 className="mb-3 text-h1">{product.name}</h1>
              <p className="mb-4 text-3xl font-bold text-primary">
                ${toNum(product.price).toLocaleString('es-CO')}
              </p>

              {product.description ? (
                <p className="mb-6 text-body text-dark-600">{product.description}</p>
              ) : null}

              <div className="mb-8 flex items-center gap-3">
                <Badge variant={product.stock > 0 ? 'success' : 'danger'}>
                  {product.stock > 0 ? `Stock: ${product.stock}` : 'Sin stock'}
                </Badge>
                {product.store ? (
                  <Badge variant="neutral">{product.store.name}</Badge>
                ) : null}
              </div>

              {added ? (
                <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                  ✅ Agregado al carrito
                </div>
              ) : null}

              <div className="flex gap-3">
                <Button
                  variant="primary"
                  onClick={handleAddToCart}
                  disabled={product.stock === 0}
                >
                  {product.stock === 0 ? 'Sin stock' : 'Agregar al carrito'}
                </Button>
                <Link to="/cart">
                  <Button variant="outline">Ver carrito</Button>
                </Link>
              </div>
            </Card>
          </div>
        )}
      </main>
    </div>
  )
}
