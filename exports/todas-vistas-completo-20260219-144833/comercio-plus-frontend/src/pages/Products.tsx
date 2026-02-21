import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Button from '@/components/Button'
import Header from '@/components/Header'
import Input from '@/components/Input'
import ProductCard from '@/components/ProductCard'
import type { Product } from '@/types'

const mockProducts: Product[] = [
  { id: '1', name: 'Jarrones Artesanales', price: 45990, stock: 25, category: 'Decoración' },
  { id: '2', name: 'Textiles Andinos', price: 89990, stock: 12, category: 'Textiles' },
  { id: '3', name: 'Joyería Tradicional', price: 32990, stock: 45, category: 'Joyería' },
  { id: '4', name: 'Decoración Única', price: 67990, stock: 8, category: 'Decoración' },
  { id: '5', name: 'Artesanía en Madera', price: 54990, stock: 18, category: 'Madera' },
  { id: '6', name: 'Cerámica Pintada', price: 39990, stock: 30, category: 'Cerámica' },
  { id: '7', name: 'Tejidos a Mano', price: 72990, stock: 15, category: 'Textiles' },
  { id: '8', name: 'Aretes de Plata', price: 28990, stock: 50, category: 'Joyería' },
]

const categories = ['Todos', 'Decoración', 'Textiles', 'Joyería', 'Madera', 'Cerámica']
const sortOptions = ['Más Recientes', 'Precio: Menor a Mayor', 'Precio: Mayor a Menor']

export default function Products() {
  const navigate = useNavigate()
  const [selectedCategory, setSelectedCategory] = useState('Todos')
  const [selectedSort, setSelectedSort] = useState(sortOptions[0])
  const [query, setQuery] = useState('')

  const filtered = useMemo(() => {
    let data = mockProducts.filter((product) =>
      product.name.toLowerCase().includes(query.toLowerCase()),
    )
    if (selectedCategory !== 'Todos') {
      data = data.filter((product) => product.category === selectedCategory)
    }

    if (selectedSort === 'Precio: Menor a Mayor') {
      data = [...data].sort((a, b) => a.price - b.price)
    }
    if (selectedSort === 'Precio: Mayor a Menor') {
      data = [...data].sort((a, b) => b.price - a.price)
    }

    return data
  }, [query, selectedCategory, selectedSort])

  return (
    <div className="min-h-screen bg-dark-50">
      <Header
        links={[
          { label: 'Tiendas', href: '/' },
          { label: 'Productos', href: '/products', active: true },
          { label: 'Cómo Funciona', href: '/how-it-works' },
        ]}
      />

      <main className="max-w-7xl mx-auto px-6 lg:px-10 py-10">
        <div className="flex flex-col gap-6 mb-10">
          <div>
            <h1 className="text-h1 mb-2">Productos</h1>
            <p className="text-body text-dark-600">
              Explora el catálogo completo de productos disponibles.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Input
              placeholder="Buscar producto..."
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              fullWidth
            />

            <select
              value={selectedCategory}
              onChange={(event) => setSelectedCategory(event.target.value)}
              className="input-dark"
            >
              {categories.map((category) => (
                <option key={category} value={category}>
                  {category}
                </option>
              ))}
            </select>

            <select
              value={selectedSort}
              onChange={(event) => setSelectedSort(event.target.value)}
              className="input-dark"
            >
              {sortOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>

            <Button variant="outline" onClick={() => { setQuery(''); setSelectedCategory('Todos'); setSelectedSort(sortOptions[0]) }}>
              Limpiar filtros
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {filtered.map((product) => (
            <ProductCard
              key={product.id}
              {...product}
              onClick={() => navigate(`/products/${product.id}`)}
              onAddToCart={() => alert(`${product.name} agregado al carrito`)}
            />
          ))}
        </div>
      </main>
    </div>
  )
}
