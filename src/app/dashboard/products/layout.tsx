
"use client";

import { ProductsProvider } from "@/lib/contexts/products-context";
import { ReactNode } from "react";

export default function ProductsLayout({ children }: { children: ReactNode }) {
  return (
    <ProductsProvider>
        <div className="flex-1 space-y-4 p-4 md:p-8 pt-6">
            <div className="flex items-center justify-between space-y-2">
                <h2 className="text-3xl font-bold tracking-tight">Gestión de Productos</h2>
            </div>
            {children}
        </div>
    </ProductsProvider>
  );
}
