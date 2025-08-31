
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { db } from "@/lib/firebase";
import { Product } from "@/lib/schemas/product";
import { collection, getDocs, query } from "firebase/firestore";
import Image from "next/image";
import Link from "next/link";
import { unstable_noStore as noStore } from 'next/cache';
import { Skeleton } from "@/components/ui/skeleton";

async function getAllProducts(): Promise<Product[]> {
    noStore();
    const productsRef = collection(db, "products");
    const q = query(productsRef);
    const querySnapshot = await getDocs(q);
    return querySnapshot.docs.map(doc => ({ id: doc.id, ...doc.data() } as Product));
}

export default async function ProductsPage() {
    const products = await getAllProducts();

    return (
        <div className="container py-12">
            <div className="mb-8">
                <h1 className="text-4xl font-extrabold tracking-tight">Catálogo de Productos</h1>
                <p className="text-muted-foreground mt-2">Explora todos los productos de nuestras tiendas.</p>
            </div>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                {products.length > 0 ? (
                    products.map(product => (
                        <Card key={product.id} className="overflow-hidden group">
                            <Link href={`/products/${product.id}`}>
                                <div className="aspect-square overflow-hidden bg-muted">
                                    <Image
                                        src={product.image ?? `https://picsum.photos/400/400?random=${product.id}`}
                                        width={400}
                                        height={400}
                                        alt={product.name}
                                        data-ai-hint="motorcycle part"
                                        className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                            </Link>
                            <CardContent className="p-4">
                                <h3 className="font-semibold text-lg truncate">
                                    <Link href={`/products/${product.id}`}>{product.name}</Link>
                                </h3>
                                <p className="text-muted-foreground text-sm">{product.categoryId}</p>
                                <div className="flex items-center justify-between mt-4">
                                    <p className="font-bold text-xl">${product.price.toLocaleString('es-CO')}</p>
                                    <Button size="sm" asChild>
                                        <Link href={`/products/${product.id}`}>Ver</Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))
                ) : (
                    Array.from({ length: 8 }).map((_, i) => (
                        <Card key={i}>
                             <Skeleton className="w-full h-48" />
                             <CardContent className="p-4 space-y-2">
                                <Skeleton className="w-3/4 h-6" />
                                <Skeleton className="w-1/2 h-4" />
                                <div className="flex justify-between items-center pt-2">
                                    <Skeleton className="w-1/3 h-8" />
                                    <Skeleton className="w-1/4 h-8" />
                                </div>
                             </CardContent>
                        </Card>
                    ))
                )}
                 {products.length === 0 && (
                    <div className="col-span-full text-center py-12 text-muted-foreground">
                        <p>No hay productos en el catálogo todavía.</p>
                    </div>
                 )}
            </div>
        </div>
    );
}
