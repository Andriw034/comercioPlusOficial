import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Category as CategoryType, Product } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import { buttonVariants } from '@/components/ui/button'

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
        setError('Error al cargar la categoria')
      } finally {
        setLoading(false)
      }
    }

    loadCategory()
  }, [id])

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-[30px] font-semibold leading-[1.12] text-slate-900 dark:text-white sm:text-[34px]">{category?.name || 'Categoria'}</h1>
          <p className="text-[13px] text-slate-600 dark:text-white/60">{category?.description || 'Productos agrupados por categoria'}</p>
        </div>
        <Link to="/products" className={buttonVariants('ghost')}>Ver todos los productos</Link>
      </div>

      {loading && <div className="text-center text-[13px] text-slate-600 dark:text-white/60">Cargando productos...</div>}
      {!loading && error && <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">{error}</GlassCard>}

      {!loading && !error && (
        <div>
          {products.length ? (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {products.map((product) => (
                <GlassCard key={product.id} className="flex flex-col gap-4">
                  <div className="aspect-[4/3] overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5">
                    {product.image_url ? (
                      <img
                        src={product.image_url}
                        alt={product.name}
                        className="h-full w-full object-cover"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-slate-500 dark:text-white/50">Sin imagen</div>
                    )}
                  </div>
                  <div className="space-y-2">
                    <h3 className="text-[18px] font-semibold text-slate-900 dark:text-white">{product.name}</h3>
                    <p className="line-clamp-2 text-[13px] text-slate-600 dark:text-white/60">{product.description || 'Sin descripcion'}</p>
                  </div>
                  <div className="flex items-center justify-between text-[13px]">
                    <span className="font-semibold text-brand-600 dark:text-brand-200">${product.price}</span>
                    <Link to={`/product/${product.id}`} className={buttonVariants('ghost')}>
                      Ver detalle
                    </Link>
                  </div>
                </GlassCard>
              ))}
            </div>
          ) : (
            <GlassCard className="text-[13px] text-slate-600 dark:text-white/60">No hay productos en esta categoria.</GlassCard>
          )}
        </div>
      )}
    </div>
  )
}