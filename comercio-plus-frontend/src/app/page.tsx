import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import type { Category as CategoryType, Product } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import StatCard from '@/components/ui/StatCard'
import { buttonVariants } from '@/components/ui/button'
import ProductCard from '@/components/products/ProductCard'
import ProductQuickViewModal from '@/components/products/ProductQuickViewModal'
import { extractList } from '@/lib/api-response'

export default function Home() {
  const navigate = useNavigate()
  const [categories, setCategories] = useState<CategoryType[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [heroQuery, setHeroQuery] = useState('')
  const [quickViewOpen, setQuickViewOpen] = useState(false)
  const [quickViewLoading, setQuickViewLoading] = useState(false)
  const [quickViewError, setQuickViewError] = useState('')
  const [quickViewProduct, setQuickViewProduct] = useState<Product | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true)
        setError(null)

        const [categoriesResponse, productsResponse] = await Promise.all([
          API.get('/categories'),
          API.get('/products', { params: { per_page: 8, sort: 'recent' } }),
        ])

        setCategories(extractList<CategoryType>(categoriesResponse.data))
        setFeaturedProducts(extractList<Product>(productsResponse.data))
      } catch (err) {
        console.error('Home loading error:', err)
        setError('Error al cargar el contenido')
      } finally {
        setLoading(false)
      }
    }

    load()
  }, [])

  const visibleCategories = categories.slice(0, 6)

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

  const submitHeroSearch = (event: React.FormEvent) => {
    event.preventDefault()
    const query = heroQuery.trim()
    navigate(query ? `/products?q=${encodeURIComponent(query)}` : '/products')
  }

  return (
    <div className="space-y-6 lg:space-y-7">
      <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden min-h-[420px] sm:min-h-[520px] lg:min-h-[600px]">
        <div className="absolute inset-0">
          <img
            src="/images/hero-moto.jpg"
            alt=""
            className="h-full w-full object-cover object-center"
            loading="lazy"
            decoding="async"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-slate-950/75 via-slate-950/35 to-transparent dark:from-black/75 dark:via-black/40" />
        </div>

        <div className="relative mx-auto flex min-h-[420px] max-w-7xl items-center px-4 py-10 sm:min-h-[520px] sm:px-6 sm:py-12 lg:min-h-[600px] lg:px-8 lg:py-16">
          <div className="w-full">
          <div className="flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-[12px] font-semibold text-slate-900 dark:bg-white/10 dark:text-white">
              Envios rapidos
            </span>
            <span className="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-[12px] font-semibold text-slate-900 dark:bg-white/10 dark:text-white">
              Tiendas verificadas
            </span>
            <span className="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-[12px] font-semibold text-slate-900 dark:bg-white/10 dark:text-white">
              Pago seguro
            </span>
          </div>

          <div className="mt-5 max-w-2xl space-y-3">
            <h1 className="text-[30px] font-semibold leading-[1.12] text-white sm:text-[40px]">
              Compra repuestos para tu moto sin riesgos
            </h1>
            <p className="text-[14px] leading-[1.55] text-white/80 sm:text-[15px]">
              Encuentra repuestos, accesorios y mantenimiento en tiendas confiables. Compara, elige y recibe rapido.
            </p>
          </div>

          <form className="mt-6 max-w-xl" onSubmit={submitHeroSearch}>
            <div className="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/90 p-2 shadow-sm dark:bg-white/10">
              <input
                className="h-10 w-full bg-transparent px-3 text-[14px] text-slate-900 placeholder:text-slate-500 focus:outline-none dark:text-white dark:placeholder:text-white/50"
                placeholder="Buscar: casco, llanta, frenos..."
                value={heroQuery}
                onChange={(event) => setHeroQuery(event.target.value)}
                aria-label="Buscar repuestos"
              />
              <button
                type="submit"
                className="h-10 rounded-xl bg-brand-500 px-4 text-[13px] font-semibold text-white hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40"
              >
                Buscar
              </button>
            </div>
            <p className="mt-2 text-[12px] text-white/70">
              Ej: "Casco", "Kit arrastre", "Pastillas freno"
            </p>
          </form>

          <div className="mt-6 flex flex-wrap items-center gap-3">
            <Link
              to="/products"
              className="inline-flex h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-[13px] font-semibold text-white shadow-sm hover:bg-brand-600"
            >
              Explorar productos
            </Link>
            <Link
              to="/stores"
              className="inline-flex h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-[13px] font-semibold text-white hover:bg-white/15"
            >
              Ver tiendas
            </Link>
            <Link to="/register" className="text-[13px] font-semibold text-white/80 hover:text-white">
              Quiero vender
            </Link>
          </div>
          </div>
        </div>
      </section>

      <section className="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
        <StatCard label="Tiendas verificadas" value="150+" hint="Comerciantes activos" />
        <StatCard label="Productos disponibles" value="10K+" hint="Stock actualizado" />
        <StatCard label="Clientes satisfechos" value="5K+" hint="Compras seguras" />
      </section>

      <section className="space-y-4">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h2 className="section-heading">Productos destacados</h2>
            <p className="section-subtitle">Los productos mejor valorados por motociclistas.</p>
          </div>
          <Link to="/products" className={buttonVariants('ghost')}>Ver todos</Link>
        </div>

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
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {featuredProducts.map((product) => (
              <ProductCard
                key={product.id}
                product={product}
                onAdd={handleAddToCart}
                onImageClick={openQuickView}
              />
            ))}
          </div>
        )}
      </section>

      <section className="space-y-6">
        <div>
          <h2 className="section-heading">Categorias clave</h2>
          <p className="section-subtitle">Solo lo esencial para motociclistas.</p>
        </div>
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {visibleCategories.map((category) => (
            <GlassCard key={category.id} className="flex flex-col gap-4">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-slate-700 dark:bg-white/10 dark:text-white/80">
                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <div className="space-y-2">
                <h3 className="text-[20px] font-semibold text-slate-900 dark:text-white">{category.name}</h3>
                <p className="text-[14px] text-slate-600 dark:text-white/60">{category.description || 'Categoria disponible'}</p>
              </div>
              <Link to={`/products?category_id=${category.id}`} className={buttonVariants('ghost')}>
                Ver productos
              </Link>
            </GlassCard>
          ))}
        </div>
      </section>

      <section className="grid gap-6 lg:grid-cols-2">
        <GlassCard className="space-y-4">
          <Badge variant="neutral">Quienes somos</Badge>
          <h2 className="text-[24px] font-semibold text-slate-900 dark:text-white">
            La plataforma confiable para repuestos de moto
          </h2>
          <p className="text-[14px] text-slate-600 dark:text-white/70">
            Conectamos motociclistas con tiendas verificadas para que encuentres repuestos, accesorios y
            mantenimiento con total seguridad y rapidez.
          </p>
        </GlassCard>
        <GlassCard className="space-y-3">
          {[
            'Tiendas verificadas con informacion real y contacto directo.',
            'Categorias enfocadas 100% en motociclistas.',
            'Compra simple y rapida, sin procesos complejos.',
          ].map((item) => (
            <div key={item} className="flex items-start gap-3 text-[14px] text-slate-600 dark:text-white/70">
              <span className="mt-1 h-2.5 w-2.5 rounded-full bg-brand-500" />
              <span>{item}</span>
            </div>
          ))}
        </GlassCard>
      </section>

      <section>
        <GlassCard className="space-y-4 border-brand-500/30">
          <Badge variant="brand">Eres comerciante?</Badge>
          <h2 className="text-[24px] font-semibold text-slate-900 dark:text-white">
            Crea tu tienda y llega a miles de motociclistas
          </h2>
          <p className="text-[14px] text-slate-600 dark:text-white/70">
            Disena tu catalogo, recibe pedidos y administra todo desde un panel premium.
          </p>
          <Link to="/register" className={buttonVariants('primary')}>Crear tienda</Link>
        </GlassCard>
      </section>

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
