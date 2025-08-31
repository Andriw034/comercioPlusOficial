
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import type { Product } from "@/lib/schemas/product";
import type { Store } from "@/lib/schemas/store";
import type { Category } from "@/lib/schemas/category";
import { Bike, Search, Star } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { placeholderProducts } from "@/lib/placeholder-data";

// Static categories to avoid Firestore query errors if collection doesn't exist
const categories: Category[] = [
    { id: "cascos", name: "Cascos", slug: 'cascos' },
    { id: "llantas", name: "Llantas", slug: 'llantas' },
    { id: "aceites", name: "Aceites y lubricantes", slug: 'aceites' },
    { id: "frenos", name: "Frenos", slug: 'frenos' },
    { id: "baterias", name: "Baterías", slug: 'baterias' },
    { id: "accesorios", name: "Accesorios", slug: 'accesorios' },
];

const getMockStore = (slug: string): Store => ({
    id: 'mock-store-id',
    userId: 'mock-user-id',
    name: `Tienda ${slug}`,
    slug: slug,
    description: 'Esta es una tienda de prueba con datos simulados para que puedas seguir desarrollando.',
    address: 'Avenida Siempre Viva 123',
    phone: '3001234567',
    openingHours: 'L-V 8am-6pm, S 9am-2pm',
    mainCategory: 'Repuestos',
    logo: 'https://picsum.photos/104/104?random=logo',
    cover: `https://picsum.photos/1280/320?random=${slug}`,
    status: 'active',
    averageRating: 4.7,
    createdAt: new Date(),
    updatedAt: new Date(),
});

const getMockProducts = (): Product[] => {
    return placeholderProducts.map(p => ({
        ...p,
        price: Number(p.price),
        stock: Number(p.stock),
        storeId: 'mock-store-id',
        userId: 'mock-user-id',
        offer: false,
        averageRating: 5,
        ratings: [],
    })) as Product[];
};


export default async function StorePage({ params }: { params: { slug: string } }) {
  const store = getMockStore(params.slug);

  if (!store) {
    notFound();
  }

  const products = getMockProducts();
  const categoryMap = new Map(categories.map(cat => [cat.id, cat.name]));


  return (
    <div>
      <header className="relative mb-16">
        <div className="h-48 md:h-64 w-full bg-gradient-to-r from-primary/20 to-accent/20">
          {store.cover && (
            <Image 
              src={store.cover} 
              alt={`Portada de ${store.name}`}
              fill
              className="object-cover"
              data-ai-hint="motorcycle road"
              priority
            />
          )}
        </div>
        <div className="absolute inset-0 bg-black/30"></div>
        <div className="container absolute -bottom-14 left-1/2 -translate-x-1/2">
           <div className="flex flex-col sm:flex-row items-center gap-4">
            <div className="h-28 w-28 rounded-full bg-white p-2 flex-shrink-0 flex items-center justify-center border-4 border-background shadow-lg -mt-12 sm:mt-0">
              {store.logo ? (
                 <Image src={store.logo} width={104} height={104} alt={`Logo de ${store.name}`} className="rounded-full object-cover"/>
              ) : (
                <Bike className="h-16 w-16 text-primary" />
              )}
            </div>
            <div className="flex-grow bg-card/80 backdrop-blur-sm p-4 rounded-xl shadow-md w-full">
                <div className="flex flex-col sm:flex-row justify-between items-center">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold font-headline">{store.name}</h1>
                        <p className="text-muted-foreground text-sm md:text-base truncate max-w-prose">{store.description}</p>
                    </div>
                    <div className="flex-shrink-0 mt-2 sm:mt-0">
                        <div className="flex items-center gap-2 text-sm text-muted-foreground bg-background/50 px-3 py-1.5 rounded-full border">
                            <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                            <span className="font-semibold">{store.averageRating?.toFixed(1) ?? 'N/A'}</span> 
                            <span className="text-xs">(0 reseñas)</span>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </header>

      <main className="container pt-12">
        <div className="flex flex-col md:flex-row gap-4 mb-8">
          <div className="relative flex-grow">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
            <Input placeholder="Buscar en la tienda..." className="pl-10" />
          </div>
          <Select>
            <SelectTrigger className="w-full md:w-[200px]">
              <SelectValue placeholder="Categoría" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Todas las categorías</SelectItem>
              {categories.map(cat => <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>)}
            </SelectContent>
          </Select>
          <Select>
            <SelectTrigger className="w-full md:w-[200px]">
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
                <Card key={product.id} className="overflow-hidden group">
                <Link href={`/products/${product.id}`}>
                    <div className="aspect-square overflow-hidden bg-muted">
                    {product.image && (
                        <Image
                        src={product.image}
                        width={400}
                        height={400}
                        alt={product.name}
                        data-ai-hint="motorcycle part"
                        className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                        />
                    )}
                    </div>
                </Link>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-sm">{categoryMap.get(product.categoryId) ?? product.categoryId}</p>
                    <h3 className="font-semibold text-lg truncate">
                         <Link href={`/products/${product.id}`}>{product.name}</Link>
                    </h3>
                    <div className="flex items-center justify-between mt-4">
                    <p className="font-bold text-xl">${product.price.toLocaleString('es-CO')}</p>
                    <Button size="sm" className="bg-gradient-to-r from-primary to-accent text-primary-foreground">
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
  );
}
