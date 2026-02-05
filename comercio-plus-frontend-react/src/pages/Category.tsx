import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '../services/api'
import type { Category as CategoryType, Product } from '../types/api'

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
    <div className="min-h-screen bg-panel text-slate-50 p-6 rounded-3xl glass">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-3xl font-semibold text-white">{category?.name || 'Categoría'}</h1>
          <p className="text-sm text-muted mt-1">{category?.description || 'Productos agrupados por categoría'}</p>
        </div>
        <Link to="/products" className="btn-ghost">Ver todos los productos</Link>
      </div>

      {loading && <div className="text-center text-muted">Cargando productos...</div>}
      {!loading && error && <div className="text-center text-red-400">{error}</div>}

      {!loading && !error && (
        <div>
          {products.length ? (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {products.map((product) => (
                <div key={product.id} className="rounded-2xl bg-panel-soft border border-white/5 p-4 shadow-soft">
                  <div className="w-full h-44 rounded-xl bg-white/5 flex items-center justify-center mb-3">
                    {product.image_url ? (
                      <img src={product.image_url} alt={product.name} className="w-full h-full object-cover rounded-xl" />
                    ) : (
                      <span className="text-muted text-sm">Sin imagen</span>
                    )}
                  </div>
                  <h3 className="text-lg font-semibold text-white">{product.name}</h3>
                  <p className="text-sm text-muted line-clamp-2">{product.description || 'Sin descripción'}</p>
                  <div className="flex items-center justify-between mt-3">
                    <span className="text-brand-200 font-semibold">${product.price}</span>
                    <Link to={`/product/${product.id}`} className="text-sm text-brand-200 hover:text-white">Ver detalle</Link>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-muted">No hay productos en esta categoría.</div>
          )}
        </div>
      )}
    </div>
  )
}
