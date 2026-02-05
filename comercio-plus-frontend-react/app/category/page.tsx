import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Category as CategoryType, Product } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import { buttonVariants } from '@/components/ui/Button'

export default function Category() {
  const { id } = useParams()
  const [category, setCategory] = useState<CategoryType | null>(null)
  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const loadCategory = async () => {
      try {
        const [categoryRes, productsRes] = await Promise.all([
          API.get(`/categories/${id}`),
          API.get('/products', { params: { category: id, per_page: 20 } }),
        ])
        setCategory(categoryRes.data)
        setProducts(productsRes.data.data || [])
      } catch (err) {
        console.error(err)
        setError('Error al cargar la categoría')
      } finally {
        setLoading(false)
      }
    }

    loadCategory()
  }, [id])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
          <h1 className="text-3xl font-semibold text-white">{category?.name || 'Categoría'}</h1>
          <p className="text-sm text-white/60">{category?.description || 'Productos agrupados por categoría'}</p>
        </div>
        <Link to="/products" className={buttonVariants('ghost')}>Ver todos los productos</Link>
      </div>

      {loading && <div className="text-center text-white/60">Cargando productos...</div>}
      {!loading && error && <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">{error}</GlassCard>}

      {!loading && !error && (
        <div>
          {products.length ? (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {products.map((product) => (
                <GlassCard key={product.id} className="flex flex-col gap-4">
                  <div className="aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                    {product.image_url ? (
                      <img src={product.image_url} alt={product.name} className="h-full w-full object-cover" />
                    ) : (
                      <div className="h-full w-full flex items-center justify-center text-white/50">Sin imagen</div>
                    )}
                  </div>
                  <div className="space-y-2">
                    <h3 className="text-lg font-semibold text-white">{product.name}</h3>
                    <p className="text-sm text-white/60 line-clamp-2">{product.description || 'Sin descripción'}</p>
                  </div>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-brand-200 font-semibold">${product.price}</span>
                    <Link to={`/product/${product.id}`} className={buttonVariants('ghost')}>
                      Ver detalle
                    </Link>
                  </div>
                </GlassCard>
              ))}
            </div>
          ) : (
            <GlassCard className="text-white/60">No hay productos en esta categoría.</GlassCard>
          )}
        </div>
      )}
    </div>
  )
}
