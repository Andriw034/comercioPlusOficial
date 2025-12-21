
'use client'

import { createContext, useContext, useState, ReactNode } from 'react'
import { placeholderProducts } from '@/lib/placeholder-data'
import type { Product } from '@/lib/schemas/product'

interface ProductsContextType {
  products: any[];
  addProduct: (product: any) => void;
}

const ProductsContext = createContext<ProductsContextType | undefined>(undefined)

// Creamos un tipo para el producto sin los campos que genera la base de datos
type CreateProduct = Omit<Product, 'id' | 'storeId' | 'userId' | 'averageRating' | 'ratings' | 'createdAt' | 'updatedAt'>;

export const ProductsProvider = ({ children }: { children: ReactNode }) => {
  const [products, setProducts] = useState<any[]>(placeholderProducts)

  const addProduct = (product: CreateProduct) => {
    const newProduct = {
      ...product,
      id: (products.length + 1).toString(), // ID simple para la simulación
      image: product.image || `https://picsum.photos/400/400?random=${products.length + 1}`,
      category: product.categoryId, // Mapeo simple para la vista
    }
    setProducts(prevProducts => [newProduct, ...prevProducts])
  }

  return (
    <ProductsContext.Provider value={{ products, addProduct }}>
      {children}
    </ProductsContext.Provider>
  )
}

export const useProducts = () => {
  const context = useContext(ProductsContext)
  if (context === undefined) {
    throw new Error('useProducts must be used within a ProductsProvider')
  }
  return context
}
