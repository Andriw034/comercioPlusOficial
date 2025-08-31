
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { db } from "@/lib/firebase";
import type { Product } from "@/lib/schemas/product";
import type { Store } from "@/lib/schemas/store";
import type { Category } from "@/lib/schemas/category";
import { collection, getDocs, query, where } from "firebase/firestore";
import { Bike, ChevronDown, Search, Star } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";

async function getStore(slug: string): Promise<Store | null> {
    const storesRef = collection(db, "stores");
    const q = query(storesRef, where("slug", "==", slug));
    const querySnapshot = await getDocs(q);
    if (querySnapshot.empty) {
        return null;
    }
    const storeDoc = querySnapshot.docs[0];
    return { id: storeDoc.id, ...storeDoc.data() } as Store;
}

async function getProducts(storeId: string): Promise<Product[]> {
    const productsRef = collection(db, "products");
    const q = query(productsRef, where("storeId", "==", storeId));
    const querySnapshot = await getDocs(q);
    return querySnapshot.docs.map(doc => ({ id: doc.id, ...doc.data() } as Product));
}

async function getCategories(): Promise<Category[]> {
    const categoriesRef = collection(db, "categories");
    const querySnapshot = await getDocs(categoriesRef);
    return querySnapshot.docs.map(doc => ({ id: doc.id, ...doc.data() } as Category));
}


export default async function StorePage({ params }: { params: { slug: string } }) {
  const store = await getStore(params.slug);

  if (!store) {
    notFound();
  }

  const products = await getProducts(store.id);
  const categories = await getCategories();
  const categoryMap = new Map(categories.map(cat => [cat.id, cat.name]));


  return (
    <div>
      <header className="relative mb-8">
        <div className="h-40 md:h-56 w-full bg-gradient-to-r from-primary to-accent">
          <Image 
            src={store.cover ?? "https://picsum.photos/1600/400"} 
            alt="Cover image" 
            fill
            className="object-cover"
            data-ai-hint="motorcycle road"
          />
        </div>
        <div className="absolute inset-0 bg-black/30"></div>
        <div className="container absolute inset-0 flex items-end p-4 md:p-8">
          <div className="flex items-center gap-4 bg-background/85 backdrop-blur px-4 py-3 rounded-2xl shadow-lg">
            <div className="h-16 w-16 rounded-lg bg-white p-1 flex items-center justify-center">
              {store.logo ? (
                 <Image src={store.logo} width={64} height={64} alt={store.name} className="rounded-md object-cover"/>
              ) : (
                <Bike className="h-12 w-12 text-primary" />
              )}
            </div>
            <div>
              <h1 className="text-xl md:text-3xl font-bold font-headline">{store.name}</h1>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                <span>{store.averageRating?.toFixed(1) ?? 'N/A'}</span> 
              </div>
            </div>
          </div>
        </div>
      </header>

      <div className="container">
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
          {products.map(product => (
            <Card key={product.id} className="overflow-hidden group">
              <Link href={`/products/${product.id}`}>
                <div className="aspect-square overflow-hidden">
                  <Image
                    src={product.image ?? "https://picsum.photos/400/400"}
                    width={400}
                    height={400}
                    alt={product.name}
                    data-ai-hint="motorcycle part"
                    className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
              </Link>
              <CardContent className="p-4">
                <p className="text-muted-foreground text-sm">{categoryMap.get(product.categoryId) ?? product.categoryId}</p>
                <h3 className="font-semibold text-lg truncate">{product.name}</h3>
                <div className="flex items-center justify-between mt-4">
                  <p className="font-bold text-xl">${product.price.toLocaleString('es-CO')}</p>
                  <Button size="sm" className="bg-gradient-to-r from-primary to-accent text-primary-foreground">
                    Agregar
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}
