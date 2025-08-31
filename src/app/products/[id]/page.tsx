
import { Button } from "@/components/ui/button";
import { Product } from "@/lib/schemas/product";
import { Store } from "@/lib/schemas/store";
import { ShoppingCart, Star } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { doc, getDoc } from "firebase/firestore";
import { db } from "@/lib/firebase";


async function getProduct(id: string): Promise<{ product: Product, store: Store } | null> {
    const productRef = doc(db, "products", id);
    const productSnap = await getDoc(productRef);

    if (!productSnap.exists()) {
      return null;
    }

    const product = { id: productSnap.id, ...productSnap.data() } as Product;

    const storeRef = doc(db, "stores", product.storeId);
    const storeSnap = await getDoc(storeRef);

    if (!storeSnap.exists()) {
        // Handle case where store might not exist, though it should
        return null;
    }

    const store = { id: storeSnap.id, ...storeSnap.data() } as Store;

    return { product, store };
}


export default async function ProductDetailsPage({ params }: { params: { id:string } }) {

    const data = await getProduct(params.id);

    if (!data) {
        notFound();
    }

    const { product, store } = data;

    return (
        <div className="container mx-auto max-w-6xl py-12">
            <div className="grid md:grid-cols-2 gap-12 items-start">
                <div className="bg-muted rounded-2xl p-4">
                    <Image 
                        src={product.image ?? "https://picsum.photos/600/600"}
                        width={600}
                        height={600}
                        alt={product.name}
                        data-ai-hint="motorcycle part"
                        className="w-full h-auto aspect-square object-cover rounded-xl"
                    />
                </div>
                <div className="flex flex-col gap-4">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            Vendido por <Link href={`/store/${store.slug}`} className="text-primary hover:underline font-medium">{store.name}</Link>
                        </p>
                        <h1 className="text-4xl font-extrabold tracking-tight mt-1">{product.name}</h1>
                    </div>

                    <div className="flex items-center gap-2">
                        <div className="flex items-center gap-1">
                            <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                            <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                            <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                            <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                            <Star className="w-5 h-5 fill-muted stroke-muted-foreground" />
                        </div>
                        <span className="text-sm text-muted-foreground">(12 reseñas)</span>
                    </div>

                    <p className="text-3xl font-bold">${product.price.toLocaleString('es-CO')}</p>
                    
                    <p className="text-muted-foreground leading-relaxed">{product.description}</p>
                    
                    <div className="flex items-center gap-4">
                        <Button size="lg" className="w-full bg-gradient-to-r from-primary to-accent text-primary-foreground shadow-lg hover:shadow-xl transition-shadow">
                            <ShoppingCart className="mr-2" />
                            Agregar al carrito
                        </Button>
                    </div>

                    <div className="border-t pt-4 text-sm text-muted-foreground">
                        <p><span className="font-semibold">Categoría:</span> {product.categoryId}</p>
                        <p><span className="font-semibold">Unidades disponibles:</span> {product.stock}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
