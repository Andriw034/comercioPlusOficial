import { useEffect, useMemo, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Category as CategoryType, Product } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import { buttonVariants } from '@/components/ui/button'
import ProductCard from '@/components/products/ProductCard'
import ProductQuickViewModal from '@/components/products/ProductQuickViewModal'

export default function Products() {
  const [searchParams] = useSearchParams()
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<CategoryType[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedCategory, setSelectedCategory] = useState('')
  const [sortBy, setSortBy] = useState('recent')
  const [quickViewOpen, setQuickViewOpen] = useState(false)
  const [quickViewLoading, setQuickViewLoading] = useState(false)
  const [quickViewError, setQuickViewError] = useState('')
  const [quickViewProduct, setQuickViewProduct] = useState<Product | null>(null)
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0,
  })

  const searchTimeout = useRef<number | null>(null)

  const visiblePages = useMemo(() => {
    const current = pagination.current_page
    const last = pagination.last_page
    const delta = 2
    const range: number[] = []
    const rangeWithDots: Array<number | string> = []

    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i += 1) {
      range.push(i)
    }

    if (current - delta > 2) {
      rangeWithDots.push(1, '...')
    } else {
      rangeWithDots.push(1)
    }

    rangeWithDots.push(...range)

    if (current + delta < last - 1) {
      rangeWithDots.push('...', last)
    } else if (last > 1) {
      rangeWithDots.push(last)
    }

    return rangeWithDots.filter(
      (item, _index, self) => item !== '...' || self.indexOf(item) === self.lastIndexOf(item),
    )
  }, [pagination.current_page, pagination.last_page])

  const fetchProducts = async (page = 1) => {
    try {
      setLoading(true)
      setError('')

      const params: Record<string, string | number | undefined> = {
        page,
        per_page: 12,
        search: searchQuery,
        category: selectedCategory || undefined,
        sort: sortBy === 'recent' ? undefined : sortBy,
      }

      const response = await API.get('/products', { params })
      setProducts(response.data.data || [])
      setPagination({
        current_page: response.data.current_page || 1,
        last_page: response.data.last_page || 1,
        per_page: response.data.per_page || 12,
        total: response.data.total || 0,
      })
    } catch (err: any) {
      console.error('Error fetching products:', err)
      setError(err.response?.data?.message || 'Error al cargar los productos. Inténtalo de nuevo.')
    } finally {
      setLoading(false)
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await API.get('/categories')
      setCategories(response.data || [])
    } catch (err) {
      console.error('Error fetching categories:', err)
    }
  }

  useEffect(() => {
    const initialCategory = searchParams.get('category') || searchParams.get('category_id') || ''
    if (initialCategory) {
      setSelectedCategory(initialCategory)
    }
    fetchCategories()
    fetchProducts()
    return () => {
      if (searchTimeout.current) {
        window.clearTimeout(searchTimeout.current)
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    if (searchTimeout.current) {
      window.clearTimeout(searchTimeout.current)
    }
    searchTimeout.current = window.setTimeout(() => fetchProducts(1), 450)
    return () => {
      if (searchTimeout.current) {
        window.clearTimeout(searchTimeout.current)
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchQuery, selectedCategory, sortBy])

  const goToPage = (page: number) => {
    if (page >= 1 && page <= pagination.last_page) {
      fetchProducts(page)
    }
  }

  const handleAddToCart = (item: Product) => {
    alert(`Producto "${item.name}" agregado al carrito (funcionalidad pendiente)`)
  }

  const openQuickView = async (item: Product) => {
    setQuickViewOpen(true)
    setQuickViewLoading(true)
    setQuickViewError('')
    setQuickViewProduct(item)

    try {
      const response = await API.get(`/products/${item.id}`)
      setQuickViewProduct(response.data?.data || response.data || item)
    } catch (err: any) {
      console.error('quick view error', err)
      setQuickViewError(err.response?.data?.message || 'No se pudo cargar el detalle del producto.')
    } finally {
      setQuickViewLoading(false)
    }
  }

  const closeQuickView = () => {
    setQuickViewOpen(false)
    setQuickViewError('')
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-semibold text-white">Productos</h1>
        <p className="text-sm text-white/60">Descubre los mejores repuestos para moto.</p>
      </div>

      <GlassCard className="flex flex-col gap-4 md:flex-row md:items-center">
        <div className="flex-1">
          <Input
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            type="text"
            placeholder="Buscar productos..."
          />
        </div>

        <div className="w-full md:w-56">
          <Select value={selectedCategory} onChange={(e) => setSelectedCategory(e.target.value)}>
            <option value="">Todas las categorías</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </Select>
        </div>

        <div className="w-full md:w-56">
          <Select value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
            <option value="recent">Más recientes</option>
            <option value="price_asc">Precio: menor a mayor</option>
            <option value="price_desc">Precio: mayor a menor</option>
          </Select>
        </div>
      </GlassCard>

      {loading && (
        <div className="flex justify-center">
          <div className="h-10 w-10 animate-spin rounded-full border-2 border-white/20 border-t-brand-500" />
        </div>
      )}

      {!loading && error && (
        <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">
          {error}
        </GlassCard>
      )}

      {!loading && !error && (
        <div className="space-y-6">
          <p className="text-sm text-white/60">
            Mostrando {products.length} de {pagination.total} productos
          </p>

          {products.length === 0 ? (
            <GlassCard className="text-center text-white/60">No se encontraron productos.</GlassCard>
          ) : (
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {products.map((product) => (
                <ProductCard
                  key={product.id}
                  product={product}
                  onAdd={handleAddToCart}
                  onImageClick={openQuickView}
                />
              ))}
            </div>
          )}

          {pagination.last_page > 1 && (
            <div className="flex justify-center">
              <nav className="inline-flex items-center gap-2">
                <button
                  onClick={() => goToPage(pagination.current_page - 1)}
                  disabled={pagination.current_page === 1}
                  className={buttonVariants('ghost')}
                >
                  Anterior
                </button>

                {visiblePages.map((page) => (
                  <button
                    key={String(page)}
                    onClick={() => (typeof page === 'number' ? goToPage(page) : null)}
                    className={
                      typeof page === 'number'
                        ? `px-4 py-2 rounded-xl text-sm font-medium border border-white/10 ${
                            page === pagination.current_page ? 'bg-white/10 text-white' : 'text-white/60 hover:text-white'
                          }`
                        : 'px-3 py-2 text-white/40'
                    }
                    disabled={typeof page !== 'number'}
                  >
                    {page}
                  </button>
                ))}

                <button
                  onClick={() => goToPage(pagination.current_page + 1)}
                  disabled={pagination.current_page === pagination.last_page}
                  className={buttonVariants('ghost')}
                >
                  Siguiente
                </button>
              </nav>
            </div>
          )}
        </div>
      )}

      <ProductQuickViewModal
        open={quickViewOpen}
        product={quickViewProduct}
        loading={quickViewLoading}
        error={quickViewError}
        onClose={closeQuickView}
        onAdd={handleAddToCart}
      />
    </div>
  )
}
