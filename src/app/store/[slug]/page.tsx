import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { placeholderProducts } from "@/lib/placeholder-data";
import { Bike, ChevronDown, Search, Star } from "lucide-react";
import Image from "next/image";
import Link from "next/link";

export default function StorePage({ params }: { params: { slug: string } }) {
  const store = {
    name: "Moto Repuestos Pro",
    slug: "moto-repuestos-pro",
    rating: 4.8,
    reviews: 124,
  };

  const products = placeholderProducts;

  const categories = [
    "Cascos", "Llantas", "Aceites y lubricantes", "Frenos", "Baterías", "Accesorios/Iluminación"
  ];

  return (
    <div>
      <header className="relative mb-8">
        <div className="h-40 md:h-56 w-full bg-gradient-to-r from-primary to-accent">
          <Image 
            src="https://picsum.photos/1600/400" 
            alt="Cover image" 
            layout="fill" 
            objectFit="cover"
            data-ai-hint="motorcycle road"
          />
        </div>
        <div className="absolute inset-0 bg-black/30"></div>
        <div className="container absolute inset-0 flex items-end p-4 md:p-8">
          <div className="flex items-center gap-4 bg-background/85 backdrop-blur px-4 py-3 rounded-2xl shadow-lg">
            <div className="h-16 w-16 rounded-lg bg-white p-1 flex items-center justify-center">
              <Bike className="h-12 w-12 text-primary" />
            </div>
            <div>
              <h1 className="text-xl md:text-3xl font-bold font-headline">{store.name}</h1>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                <span>{store.rating}</span>
                <span>({store.reviews} reseñas)</span>
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
              {categories.map(cat => <SelectItem key={cat} value={cat.toLowerCase()}>{cat}</SelectItem>)}
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
                    src={product.image}
                    width={400}
                    height={400}
                    alt={product.name}
                    data-ai-hint={product.hint}
                    className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
              </Link>
              <CardContent className="p-4">
                <h3 className="font-semibold text-lg truncate">{product.name}</h3>
                <p className="text-muted-foreground text-sm">{product.category}</p>
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
