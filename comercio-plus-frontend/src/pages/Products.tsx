import { useCallback, useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/Button'
import Input from '@/components/Input'
import ProductCard from '@/components/ProductCard'
import { useCart } from '@/context/CartContext'

interface ApiProduct {
  id: number | string
  name: string
  price: number
  stock: number
  image: string | null
  image_url: string | null
  description: string | null
  store_id: number | string | null
  category: { id: number; name: string } | null
}

interface ApiMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

type SortKey = 'newest' | 'price_asc' | 'price_desc'

const SORT_OPTIONS: Array<{ label: string; value: SortKey }> = [
  { label: 'Más recientes', value: 'newest' },
  { label: 'Precio: Menor a mayor', value: 'price_asc' },
  { label: 'Precio: Mayor a menor', value: 'price_desc' },
]

function toStr(value: unknown, fallback: string): string {
  return typeof value === 'string' && value.trim().length > 0 ? value.trim() : fallback
}

function toNum(value: unknown): number {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

function resolveImage(p: ApiProduct): string {
  return toStr(p.image_url, toStr(p.image, ''))
}

function normalizeProduct(raw: unknown): ApiProduct | null {
  if (!raw || typeof raw !== 'object') return null
  const r = raw as Record<string, unknown>
  const id = r.id != null ? String(r.id) : ''
  if (!id) return null

  const cat = r.category && typeof r.category === 'object'
    ? (r.category as Record<string, unknown>)
    : null

  return {
    id,
    name: toStr(r.name, 'Producto'),
    price: toNum(r.price),
    stock: toNum(r.stock),
    image: typeof r.image === 'string' ? r.image : null,
    image_url: typeof r.image_url === 'string' ? r.image_url : null,
    description: typeof r.description === 'string' ? r.description : null,
    store_id: r.store_id != null ? String(r.store_id) : null,
    category: cat ? { id: toNum(cat.id), name: toStr(cat.name, 'General') } : null,
  }
}

// --- Skeletons ---
function CardSkeleton() {
  return (
    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white animate-pulse">
      <div className="h-48 bg-slate-200" />
      <div className="p-5 space-y-3">
        <div className="h-5 w-3/4 rounded bg-slate-200" />
        <div className="h-6 w-1/2 rounded bg-slate-200" />
        <div className="h-4 w-1/3 rounded bg-slate-200" />
        <div className="h-9 w-full rounded-lg bg-slate-200" />
      </div>
    </div>
  )
}

function GridSkeleton() {
  return (
    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {Array.from({ length: 8 }, (_, i) => <CardSkeleton key={i} />)}
    </div>
  )
}

export default function Products() {
  const navigate = useNavigate()
  const { addToCart } = useCart()

  const [products, setProducts] = useState<ApiProduct[]>([])
  const [meta, setMeta] = useState<ApiMeta | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [notice, setNotice] = useState('')

  const [query, setQuery] = useState('')
  const [sort, setSort] = useState<SortKey>('newest')
  const [page, setPage] = useState(1)

  // Debounce query to avoid firing on every keystroke
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const [debouncedQuery, setDebouncedQuery] = useState('')

  const handleQueryChange = (value: string) => {
    setQuery(value)
    if (debounceRef.current) clearTimeout(debounceRef.current)
    debounceRef.current = setTimeout(() => {
      setDebouncedQuery(value)
      setPage(1)
    }, 350)
  }

  const load = useCallback(async () => {
    setLoading(true)
    setError('')

    try {
      const params: Record<string, string | number> = {
        per_page: 24,
        page,
        sort,
      }
      if (debouncedQuery.trim()) params.q = debouncedQuery.trim()

      const resp = await API.get<{ data: unknown[]; meta: ApiMeta }>('/public/products', { params })
      const raw = resp.data?.data ?? []
      setProducts(raw.map(normalizeProduct).filter((p): p is ApiProduct => p !== null))
      setMeta(resp.data?.meta ?? null)
    } catch {
      setError('No se pudieron cargar los productos. Verifica tu conexión e inténtalo de nuevo.')
      setProducts([])
    } finally {
      setLoading(false)
    }
  }, [debouncedQuery, sort, page])

  useEffect(() => {
    void load()
  }, [load])

  const handleAddToCart = (product: ApiProduct) => {
    addToCart({
      id: String(product.id),
      name: product.name,
      price: product.price,
      image: resolveImage(product),
      seller: 'ComercioPlus',
      storeId: String(product.store_id ?? ''),
    })
    setNotice(`${product.name} agregado al carrito`)
    setTimeout(() => setNotice(''), 2500)
  }

  const resetFilters = () => {
    setQuery('')
    setDebouncedQuery('')
    setSort('newest')
    setPage(1)
  }

  const totalPages = meta?.last_page ?? 1

  return (
    <div className="min-h-screen bg-dark-50">
      <main className="mx-auto max-w-7xl px-6 py-10 lg:px-10">
        {notice ? (
          <div className="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            ✅ {notice}
          </div>
        ) : null}

        <div className="mb-10 flex flex-col gap-6">
          <div>
            <h1 className="mb-2 text-h1">Productos</h1>
            <p className="text-body text-dark-600">
              Explora el catálogo completo de productos disponibles.
              {meta ? ` ${meta.total.toLocaleString('es-CO')} productos encontrados.` : ''}
            </p>
          </div>

          <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
            <Input
              placeholder="Buscar producto..."
              value={query}
              onChange={(event) => handleQueryChange(event.target.value)}
              fullWidth
            />

            <select
              value={sort}
              onChange={(event) => {
                setSort(event.target.value as SortKey)
                setPage(1)
              }}
              className="input-dark"
            >
              {SORT_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>

            <div />

            <Button
              variant="outline"
              onClick={resetFilters}
              disabled={loading}
            >
              Limpiar filtros
            </Button>
          </div>
        </div>

        {loading ? (
          <GridSkeleton />
        ) : error ? (
          <div className="rounded-xl border border-rose-200 bg-rose-50 px-6 py-10 text-center">
            <p className="mb-4 text-body text-rose-700">{error}</p>
            <Button variant="primary" onClick={() => void load()}>
              Reintentar
            </Button>
          </div>
        ) : products.length === 0 ? (
          <div className="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center">
            <p className="mb-2 text-lg font-semibold text-slate-700">Sin resultados</p>
            <p className="mb-6 text-body text-dark-600">
              No se encontraron productos{debouncedQuery ? ` para "${debouncedQuery}"` : ''}.
            </p>
            <Button variant="outline" onClick={resetFilters}>
              Limpiar filtros
            </Button>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {products.map((product) => (
                <ProductCard
                  key={String(product.id)}
                  id={String(product.id)}
                  name={product.name}
                  price={product.price}
                  stock={product.stock}
                  image={resolveImage(product) || undefined}
                  onClick={() => navigate(`/products/${String(product.id)}`)}
                  onAddToCart={() => handleAddToCart(product)}
                />
              ))}
            </div>

            {totalPages > 1 ? (
              <div className="mt-10 flex items-center justify-center gap-3">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page <= 1 || loading}
                >
                  ← Anterior
                </Button>
                <span className="text-sm text-slate-600">
                  Página {page} de {totalPages}
                </span>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                  disabled={page >= totalPages || loading}
                >
                  Siguiente →
                </Button>
              </div>
            ) : null}
          </>
        )}
      </main>
    </div>
  )
}
