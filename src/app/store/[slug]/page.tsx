import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { placeholderProducts } from '@/lib/placeholder-data'
import type { Store } from '@/lib/schemas/store'
import type { Category } from '@/lib/schemas/category'
import { Bike, Search, Star, MapPin } from 'lucide-react'
import Image from 'next/image'
import Link from 'next/link'
import { notFound } from 'next/navigation'


async function getStoreData(slug: string) {
  const store: Store = {
    id: 'mock-store-id',
    userId: 'mock-user-id',
    name: 'Moto Repuestos Pro',
    slug: 'moto-repuestos-pro',
    description: 'Los mejores repuestos para tu moto. Calidad y servicio garantizado. Años de experiencia en el sector.',
    address: 'Av. Principal 123, Ciudad Capital',
    mainCategory: 'Repuestos',
    logo: 'https://i.pravatar.cc/150?u=a042581f4e29026704d',
    cover: 'https://picsum.photos/1600/400',
    averageRating: 4.8,
    createdAt: new Date(),
    updatedAt: new Date(),
  }

  if (slug !== store.slug) {
    return null
  }

  const products = placeholderProducts

  const categories: Category[] = [
    { id: 'cascos', name: 'Cascos', slug: 'cascos' },
    { id: 'llantas', name: 'Llantas', slug: 'llantas' },
    { id: 'aceites', name: 'Aceites y lubricantes', slug: 'aceites' },
    { id: 'frenos', name: 'Frenos', slug: 'frenos' },
    { id: 'baterias', name: 'Baterías', slug: 'baterias' },
    { id: 'accesorios', name: 'Accesorios', slug: 'accesorios' },
  ]
    
  return { store, products, categories }
}

export default async function StorePage({ params }: { params: { slug: string } }) {
  const data = await getStoreData(params.slug)

  if (!data) {
    notFound()
  }

  const { store, products, categories } = data
  const categoryMap = new Map(categories.map(cat => [cat.id, cat.name]))


  return (
    <div className="bg-background">
      <section className="border-b">
        <div className="relative h-48 md:h-64 w-full">
          {store.cover ? (
            <Image 
              src={store.cover} 
              alt={`Portada de ${store.name}`}
              fill
              className="object-cover"
              data-ai-hint="motorcycle road"
              priority
            />
          ) : <div className="w-full h-full bg-muted"></div>}
          <div className="absolute inset-0 bg-gradient-to-t from-background via-background/80 to-black/20"></div>
        </div>
        <div className="container -mt-16 sm:-mt-20">
          <div className="flex flex-col sm:flex-row items-end gap-4 relative z-10">
            <div className="h-32 w-32 rounded-full bg-card p-1.5 flex-shrink-0 flex items-center justify-center border-4 border-background shadow-md">
              {store.logo ? (
                <Image src={store.logo} width={128} height={128} alt={`Logo de ${store.name}`} className="rounded-full object-cover"/>
              ) : (
                <Bike className="h-16 w-16 text-primary" />
              )}
            </div>
            <div className="flex-grow py-4">
              <div className="flex flex-col sm:flex-row justify-between items-start gap-2">
                <div>
                  <h1 className="text-3xl md:text-4xl font-bold">{store.name}</h1>
                  <p className="text-muted-foreground flex items-center gap-2 mt-1">
                    <MapPin className="w-4 h-4 text-primary" />
                    {store.address}
                  </p>
                </div>
                <div className="flex-shrink-0 mt-2 sm:mt-0">
                  <div className="flex items-center gap-2 text-sm bg-secondary backdrop-blur-sm px-3 py-1.5 rounded-full border">
                    <Star className="w-4 h-4 fill-primary text-primary" />
                    <span className="font-semibold text-secondary-foreground">{store.averageRating?.toFixed(1) ?? 'N/A'}</span> 
                    <span className="text-muted-foreground">(15 reseñas)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <main className="container pt-8 pb-16">
        <div className="flex flex-col md:flex-row gap-4 mb-8">
          <div className="relative flex-grow">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
            <Input placeholder="Buscar en la tienda..." className="pl-12 h-11 bg-card" />
          </div>
          <Select>
            <SelectTrigger className="w-full md:w-[200px] h-11 bg-card">
              <SelectValue placeholder="Categoría" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Todas las categorías</SelectItem>
              {categories.map(cat => <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>)}
            </SelectContent>
          </Select>
          <Select>
            <SelectTrigger className="w-full md:w-[200px] h-11 bg-card">
              <SelectValue placeholder="Ordenar por" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="popular">Más populares</SelectItem>
              <SelectItem value="price-asc">Precio: bajo a alto</SelectItem>
              <SelectItem value="price-desc">Precio: alto a bajo</SelectItem>
              <SelectItem value="newest">Más nuevos</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {products.length > 0 ? (
            products.map(product => (
              <Card key={product.id} className="overflow-hidden group transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                <Link href={`/products/${product.id}`}>
                  <div className="aspect-square overflow-hidden bg-card">
                    {product.image && (
                      <Image
                        src={product.image}
                        width={400}
                        height={400}
                        alt={product.name}
                        data-ai-hint="motorcycle part"
                        className="object-cover w-full h-full transition-transform duration-200 group-hover:scale-105"
                      />
                    )}
                  </div>
                </Link>
                <CardContent className="p-4">
                  <p className="text-muted-foreground text-sm">{product.category ?? product.categoryId}</p>
                  <h3 className="font-semibold text-lg truncate mt-1">
                    <Link href={`/products/${product.id}`} className="hover:text-primary transition-colors">{product.name}</Link>
                  </h3>
                  <div className="flex items-center justify-between mt-4">
                    <p className="font-bold text-xl">${product.price.toLocaleString('es-CO')}</p>
                    <Button size="sm">
                        Agregar
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))
          ) : (
            <div className="col-span-full text-center py-12 text-muted-foreground">
              <Bike className="mx-auto h-12 w-12" />
              <h3 className="mt-4 text-lg font-semibold">No hay productos todavía</h3>
              <p className="mt-1 text-sm">Este comerciante aún no ha añadido ningún producto a su tienda.</p>
            </div>
          )}
        </div>
      </main>
    </div>
  )
}
