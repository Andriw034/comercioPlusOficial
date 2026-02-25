import { useMemo } from 'react'
import { Link, useParams } from 'react-router-dom'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import type { Product } from '@/types'

const mockProducts: Product[] = [
  {
    id: '1',
    name: 'Jarrones Artesanales',
    price: 45990,
    stock: 25,
    category: 'Decoracion',
    description: 'Piezas unicas hechas a mano por artesanos locales.',
  },
  {
    id: '2',
    name: 'Textiles Andinos',
    price: 89990,
    stock: 12,
    category: 'Textiles',
    description: 'Textiles premium con fibras naturales y acabados finos.',
  },
]

export default function ProductDetail() {
  const { id } = useParams()
  const product = useMemo(() => mockProducts.find((item) => item.id === id), [id])

  if (!product) {
    return (
      <div className="min-h-screen bg-dark-50">
        <main className="mx-auto max-w-7xl px-6 py-20 lg:px-10">
          <Card className="text-center">
            <h2 className="mb-3 text-h2">Producto no encontrado</h2>
            <Link to="/products">
              <Button variant="primary">Volver a productos</Button>
            </Link>
          </Card>
        </main>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-dark-50">
      <main className="mx-auto max-w-7xl px-6 py-10 lg:px-10">
        <div className="mb-6 flex flex-wrap items-center gap-2 text-sm">
          <Link to="/stores" className="font-medium text-slate-600 transition hover:text-slate-900">
            Tiendas
          </Link>
          <span className="text-slate-400">/</span>
          <Link to="/products" className="font-medium text-slate-600 transition hover:text-slate-900">
            Productos
          </Link>
          <span className="text-slate-400">/</span>
          <span className="font-semibold text-slate-900">Detalle</span>
        </div>

        <div className="mb-6">
          <Link to="/products" className="text-body-sm text-dark-600 hover:text-primary">
            ← Volver a productos
          </Link>
        </div>

        <div className="grid grid-cols-1 gap-10 lg:grid-cols-2">
          <div className="h-[460px] overflow-hidden rounded-md bg-gradient-to-br from-primary to-secondary" />

          <Card>
            <div className="mb-4">
              <Badge variant="info">{product.category || 'General'}</Badge>
            </div>

            <h1 className="mb-3 text-h1">{product.name}</h1>
            <p className="mb-4 text-3xl font-bold text-primary">${product.price.toLocaleString('es-CO')}</p>
            <p className="mb-6 text-body text-dark-600">{product.description || 'Sin descripcion'}</p>

            <div className="mb-8 flex items-center gap-3">
              <Badge variant={product.stock && product.stock > 0 ? 'success' : 'danger'}>
                {product.stock && product.stock > 0 ? `Stock: ${product.stock}` : 'Sin stock'}
              </Badge>
            </div>

            <div className="flex gap-3">
              <Button variant="primary">Agregar al carrito</Button>
              <Button variant="outline">Guardar</Button>
            </div>
          </Card>
        </div>
      </main>
    </div>
  )
}
