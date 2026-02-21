import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import { Icon } from '@/components/Icon'

interface Product {
  id: string
  name: string
  price: number
  stock: number
  category: string
  status: 'active' | 'inactive'
  image?: string
}

// Datos mock - reemplazar con API real
const mockProducts: Product[] = [
  {
    id: '1',
    name: 'Jarrones artesanales',
    price: 45990,
    stock: 25,
    category: 'Decoración',
    status: 'active',
  },
  {
    id: '2',
    name: 'Textiles andinos',
    price: 89990,
    stock: 12,
    category: 'Textiles',
    status: 'active',
  },
  {
    id: '3',
    name: 'Joyería tradicional',
    price: 32990,
    stock: 0,
    category: 'Joyería',
    status: 'inactive',
  },
]

export default function ProductList() {
  const navigate = useNavigate()
  const [products, setProducts] = useState<Product[]>(mockProducts)
  const [searchQuery, setSearchQuery] = useState('')

  const handleDelete = async (id: string) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) {
      return
    }

    try {
      // TODO: Conectar con API real
      // await fetch(`/api/products/${id}`, { method: 'DELETE' })

      setProducts(products.filter((p) => p.id !== id))
      alert('Producto eliminado exitosamente')
    } catch (error) {
      console.error('Error al eliminar producto:', error)
      alert('Error al eliminar el producto')
    }
  }

  const filteredProducts = products.filter((product) =>
    product.name.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-display-sm text-slate-950">Productos</h1>
          <p className="text-body text-slate-600">Gestiona tu catálogo de productos</p>
        </div>
        <Button
          variant="primary"
          onClick={() => navigate('/dashboard/products/create')}
          className="bg-comercioplus-600 hover:bg-comercioplus-700"
          icon={<Icon name="plus" size={20} />}
        >
          Crear producto
        </Button>
      </div>

      {/* Filtros */}
      <Card variant="glass" padding="md">
        <div className="flex gap-4">
          <div className="relative flex-1">
            <Icon
              name="search"
              size={20}
              className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
            />
            <input
              type="text"
              placeholder="Buscar productos..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="input-dark w-full pl-12"
              spellCheck={false}
            />
          </div>
        </div>
      </Card>

      {/* Lista de productos */}
      {filteredProducts.length === 0 ? (
        <Card variant="bordered" padding="xl" className="text-center">
          <div className="mb-4 flex justify-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
              <Icon name="package" size={32} className="text-slate-400" />
            </div>
          </div>
          <p className="text-body-lg text-slate-600">
            {searchQuery ? 'No se encontraron productos' : 'No tienes productos aún'}
          </p>
          {!searchQuery && (
            <Button
              variant="primary"
              onClick={() => navigate('/dashboard/products/create')}
              className="mt-4"
              icon={<Icon name="plus" size={20} />}
            >
              Crear tu primer producto
            </Button>
          )}
        </Card>
      ) : (
        <div className="grid grid-cols-1 gap-4">
          {filteredProducts.map((product) => (
            <Card key={product.id} variant="glass" padding="md" hoverable>
              <div className="flex flex-wrap items-center gap-4">
                {/* Imagen placeholder */}
                <div className="flex h-20 w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-comercioplus-400 to-comercioplus-600">
                  <Icon name="image" size={32} className="text-white" />
                </div>

                {/* Info */}
                <div className="flex-1">
                  <h3 className="mb-1 text-h3 text-slate-950">{product.name}</h3>
                  <div className="flex flex-wrap items-center gap-2 text-body-sm text-slate-600">
                    <span className="flex items-center gap-1 font-semibold text-comercioplus-600">
                      <Icon name="dollar" size={16} />
                      {product.price.toLocaleString('es-CL')}
                    </span>
                    <span>•</span>
                    <span className="flex items-center gap-1">
                      <Icon name="package" size={16} />
                      Stock: {product.stock}
                    </span>
                    <span>•</span>
                    <span className="flex items-center gap-1">
                      <Icon name="tag" size={16} />
                      {product.category}
                    </span>
                  </div>
                </div>

                {/* Estado */}
                <div className="flex items-center gap-2">
                  <Badge variant={product.status === 'active' ? 'success' : 'neutral'}>
                    {product.status === 'active' ? 'Activo' : 'Inactivo'}
                  </Badge>
                  {product.stock === 0 && <Badge variant="warning">Sin stock</Badge>}
                </div>

                {/* Acciones */}
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => navigate(`/dashboard/products/${product.id}/edit`)}
                    icon={<Icon name="edit" size={16} />}
                  >
                    Editar
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDelete(product.id)}
                    className="border-danger text-danger hover:bg-danger hover:text-white"
                    icon={<Icon name="trash" size={16} />}
                  >
                    Eliminar
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>
      )}

      {/* Estadísticas */}
      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        <Card variant="glass" padding="md">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-comercioplus-100">
              <Icon name="package" size={20} className="text-comercioplus-600" />
            </div>
            <div>
              <p className="text-caption uppercase tracking-wide text-slate-500">
                Total productos
              </p>
              <p className="text-h2 text-slate-950">{products.length}</p>
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="md">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
              <Icon name="check" size={20} className="text-green-600" />
            </div>
            <div>
              <p className="text-caption uppercase tracking-wide text-slate-500">
                Productos activos
              </p>
              <p className="text-h2 text-slate-950">
                {products.filter((p) => p.status === 'active').length}
              </p>
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="md">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
              <Icon name="alert" size={20} className="text-amber-600" />
            </div>
            <div>
              <p className="text-caption uppercase tracking-wide text-slate-500">Sin stock</p>
              <p className="text-h2 text-slate-950">
                {products.filter((p) => p.stock === 0).length}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  )
}
