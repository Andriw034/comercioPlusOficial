import { createContext, useContext, useState, useEffect, type ReactNode } from 'react'

interface CartItem {
  id: string
  productId: string
  name: string
  price: number
  quantity: number
  image: string
  seller: string
  storeId: string
}

interface CartContextType {
  items: CartItem[]
  addToCart: (product: any) => void
  removeFromCart: (productId: string) => void
  updateQuantity: (productId: string, quantity: number) => void
  clearCart: () => void
  totalItems: number
  totalPrice: number
}

const CartContext = createContext<CartContextType | undefined>(undefined)

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([])

  // Cargar carrito desde localStorage al iniciar
  useEffect(() => {
    const savedCart = localStorage.getItem('cart')
    if (savedCart) {
      try {
        setItems(JSON.parse(savedCart))
      } catch (error) {
        console.error('Error loading cart:', error)
      }
    }
  }, [])

  // Guardar carrito en localStorage cuando cambie
  useEffect(() => {
    localStorage.setItem('cart', JSON.stringify(items))
  }, [items])

  const addToCart = (product: any) => {
    setItems((currentItems) => {
      // Verificar si el producto ya está en el carrito
      const existingItem = currentItems.find((item) => item.productId === product.id)

      if (existingItem) {
        // Incrementar cantidad
        return currentItems.map((item) =>
          item.productId === product.id
            ? { ...item, quantity: item.quantity + 1 }
            : item
        )
      } else {
        // Agregar nuevo producto
        return [
          ...currentItems,
          {
            id: `${product.id}-${Date.now()}`,
            productId: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            image: product.image,
            seller: product.seller,
            storeId: product.storeId,
          },
        ]
      }
    })
  }

  const removeFromCart = (productId: string) => {
    setItems((currentItems) => currentItems.filter((item) => item.productId !== productId))
  }

  const updateQuantity = (productId: string, quantity: number) => {
    if (quantity <= 0) {
      removeFromCart(productId)
      return
    }

    setItems((currentItems) =>
      currentItems.map((item) =>
        item.productId === productId ? { ...item, quantity } : item
      )
    )
  }

  const clearCart = () => {
    setItems([])
  }

  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0)
  const totalPrice = items.reduce((sum, item) => sum + item.price * item.quantity, 0)

  return (
    <CartContext.Provider
      value={{
        items,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        totalItems,
        totalPrice,
      }}
    >
      {children}
    </CartContext.Provider>
  )
}

export function useCart() {
  const context = useContext(CartContext)
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider')
  }
  return context
}
