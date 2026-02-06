import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import type { Category as CategoryType, Product } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import StatCard from '@/components/ui/StatCard'
import { buttonVariants } from '@/components/ui/button'

export default function Home() {
  const [categories, setCategories] = useState<CategoryType[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [heroImages, setHeroImages] = useState<string[]>([])
  const [heroIndex, setHeroIndex] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true)
        setError(null)

        const [categoriesResponse, productsResponse, heroResponse] = await Promise.all([
          API.get('/categories'),
          API.get('/products', { params: { per_page: 8, sort: 'recent' } }),
          API.get('/hero-images'),
        ])

        setCategories(categoriesResponse.data || [])
        setFeaturedProducts(productsResponse.data?.data || [])
        setHeroImages(heroResponse.data?.images || [])
      } catch (err) {
        console.error('Home loading error:', err)
        setError('Error al cargar el contenido')
      } finally {
        setLoading(false)
      }
    }

    load()
  }, [])

  useEffect(() => {
    if (heroImages.length <= 1) return
    const interval = window.setInterval(() => {
      setHeroIndex((prev) => (prev + 1) % heroImages.length)
    }, 6500)
    return () => window.clearInterval(interval)
  }, [heroImages])

  const heroImage = heroImages[heroIndex]
  const visibleCategories = categories.slice(0, 6)

  return (
    <div className="space-y-10">
      <section className="relative">
        <GlassCard className="relative w-full overflow-hidden p-8 sm:p-12 lg:p-14 min-h-[360px] sm:min-h-[420px] lg:min-h-[460px] flex items-center">
          {heroImage && (
            <img
              src={heroImage}
              alt="Hero"
              className="absolute inset-0 h-full w-full object-cover opacity-25"
            />
          )}
          <div className="absolute inset-0 bg-gradient-to-b from-slate-900/45 via-slate-900/20 to-slate-900/60" />
          <div className="relative max-w-4xl">
            <Badge variant="brand">ComercioPlus</Badge>
            <h1 className="mt-4 text-4xl sm:text-5xl font-semibold text-white">
              Bienvenido a ComercioPlus
            </h1>
            <p className="mt-4 text-base sm:text-lg text-white/70">
              La plataforma premium para repuestos de moto. Encuentra todo lo que necesitas con tiendas confiables y
              envíos rápidos.
            </p>
            <div className="mt-6 flex flex-col sm:flex-row gap-3">
              <Link to="/products" className={buttonVariants('primary')}>Explorar productos</Link>
              <Link to="/stores" className={buttonVariants('secondary')}>Ver tiendas</Link>
            </div>
          </div>
        </GlassCard>
      </section>

      <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <StatCard label="Tiendas verificadas" value="150+" hint="Comerciantes activos" />
        <StatCard label="Productos disponibles" value="10K+" hint="Stock actualizado" />
        <StatCard label="Clientes satisfechos" value="5K+" hint="Compras seguras" />
      </section>

      <section className="space-y-6">
        <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
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
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {featuredProducts.map((product) => (
              <GlassCard key={product.id} className="flex flex-col gap-4">
                <div className="aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                  <img
                    src={product.image_url || '/placeholder-product.png'}
                    alt={product.name}
                    className="h-full w-full object-cover"
                  />
                </div>
                <div className="space-y-2">
                  <h3 className="text-sm font-semibold text-white">{product.name}</h3>
                  <p className="text-lg font-semibold text-brand-200">${product.price}</p>
                  <div className="flex items-center gap-2 text-xs text-white/50">
                    <span>{product.rating || '0.0'}</span>
                    <span>•</span>
                    <span>{product.reviews_count || 0} reseñas</span>
                  </div>
                </div>
                <Link to={`/product/${product.id}`} className={buttonVariants('secondary')}>Ver producto</Link>
              </GlassCard>
            ))}
          </div>
        )}
      </section>

      <section className="space-y-6">
        <div>
          <h2 className="section-heading">Categorías clave</h2>
          <p className="section-subtitle">Solo lo esencial para motociclistas.</p>
        </div>
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {visibleCategories.map((category) => (
            <GlassCard key={category.id} className="flex flex-col gap-4">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white/80">
                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <div className="space-y-2">
                <h3 className="text-lg font-semibold text-white">{category.name}</h3>
                <p className="text-sm text-white/60">{category.description}</p>
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
          <Badge variant="neutral">Quiénes somos</Badge>
          <h2 className="text-2xl font-semibold text-white">La plataforma confiable para repuestos de moto</h2>
          <p className="text-sm text-white/60">
            Conectamos motociclistas con tiendas verificadas para que encuentres repuestos, accesorios y
            mantenimiento con total seguridad y rapidez.
          </p>
        </GlassCard>
        <GlassCard className="space-y-3">
          {[
            'Tiendas verificadas con información real y contacto directo.',
            'Categorías enfocadas 100% en motociclistas.',
            'Compra simple y rápida, sin procesos complejos.',
          ].map((item) => (
            <div key={item} className="flex items-start gap-3 text-sm text-white/70">
              <span className="mt-1 h-2.5 w-2.5 rounded-full bg-brand-500" />
              <span>{item}</span>
            </div>
          ))}
        </GlassCard>
      </section>

      <section>
        <GlassCard className="border-brand-500/30 space-y-4">
          <Badge variant="brand">¿Eres comerciante?</Badge>
          <h2 className="text-2xl font-semibold text-white">Crea tu tienda y llega a miles de motociclistas</h2>
          <p className="text-sm text-white/60">
            Diseña tu catálogo, recibe pedidos y administra todo desde un panel premium.
          </p>
          <Link to="/register" className={buttonVariants('primary')}>Crear tienda</Link>
        </GlassCard>
      </section>
    </div>
  )
}
