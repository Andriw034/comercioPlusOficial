import { useMemo } from 'react'
import { Link, useParams } from 'react-router-dom'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Header from '@/components/Header'
import type { Product } from '@/types'

const mockProducts: Product[] = [
  {
    id: '1',
    name: 'Jarrones Artesanales',
    price: 45990,
    stock: 25,
    category: 'Decoración',
    description: 'Piezas únicas hechas a mano por artesanos locales.',
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
        <Header links={[{ label: 'Productos', href: '/products', active: true }]} />
        <main className="max-w-7xl mx-auto px-6 lg:px-10 py-20">
          <Card className="text-center">
            <h2 className="text-h2 mb-3">Producto no encontrado</h2>
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
      <Header
        links={[
          { label: 'Tiendas', href: '/' },
          { label: 'Productos', href: '/products', active: true },
        ]}
      />

      <main className="max-w-7xl mx-auto px-6 lg:px-10 py-10">
        <div className="mb-6">
          <Link to="/products" className="text-body-sm text-dark-600 hover:text-primary">
            ← Volver a productos
          </Link>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
          <div className="rounded-md overflow-hidden h-[460px] bg-gradient-to-br from-primary to-secondary" />

          <Card>
            <div className="mb-4">
              <Badge variant="info">{product.category || 'General'}</Badge>
            </div>

            <h1 className="text-h1 mb-3">{product.name}</h1>
            <p className="text-3xl font-bold text-primary mb-4">
              ${product.price.toLocaleString('es-CL')}
            </p>
            <p className="text-body text-dark-600 mb-6">
              {product.description || 'Sin descripción'}
            </p>

            <div className="flex items-center gap-3 mb-8">
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
