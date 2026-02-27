import { createContext, useContext, useEffect, useState, type ReactNode } from 'react'

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

interface CartProductInput {
  id: string | number
  name: string
  price: number
  image: string
  seller: string
  storeId: string | number
}

interface CartContextType {
  items: CartItem[]
  addToCart: (product: CartProductInput) => void
  removeFromCart: (productId: string) => void
  updateQuantity: (productId: string, quantity: number) => void
  clearCart: () => void
  totalItems: number
  totalPrice: number
}

const CART_STORAGE_KEY = 'cart'
const CartContext = createContext<CartContextType | undefined>(undefined)

const toSafeNumber = (value: unknown): number => {
  const parsed = Number(value)
  if (Number.isFinite(parsed) && parsed >= 0) return parsed
  return 0
}

const sanitizeCartItem = (value: unknown): CartItem | null => {
  if (!value || typeof value !== 'object') return null

  const item = value as Record<string, unknown>
  const productId = String(item.productId || item.id || '').trim()
  if (!productId) return null

  const quantity = Math.max(1, Math.floor(toSafeNumber(item.quantity || 1)))

  return {
    id: String(item.id || `${productId}-legacy`),
    productId,
    name: String(item.name || 'Producto'),
    price: toSafeNumber(item.price),
    quantity,
    image: String(item.image || ''),
    seller: String(item.seller || 'ComercioPlus'),
    storeId: String(item.storeId || ''),
  }
}

const readStoredCart = (): CartItem[] => {
  if (typeof window === 'undefined') return []

  try {
    const raw = localStorage.getItem(CART_STORAGE_KEY)
    if (!raw) return []

    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []

    return parsed.map(sanitizeCartItem).filter((item): item is CartItem => item !== null)
  } catch (error) {
    console.error('Error loading cart:', error)
    return []
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<CartItem[]>(readStoredCart)

  useEffect(() => {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items))
    } catch (error) {
      console.error('Error saving cart:', error)
    }
  }, [items])

  const addToCart = (product: CartProductInput) => {
    const productId = String(product.id || '').trim()
    if (!productId) return

    setItems((currentItems) => {
      const existingItem = currentItems.find((item) => item.productId === productId)

      if (existingItem) {
        return currentItems.map((item) =>
          item.productId === productId
            ? { ...item, quantity: item.quantity + 1 }
            : item,
        )
      }

      return [
        ...currentItems,
        {
          id: `${productId}-${Date.now()}`,
          productId,
          name: String(product.name || 'Producto'),
          price: toSafeNumber(product.price),
          quantity: 1,
          image: String(product.image || ''),
          seller: String(product.seller || 'ComercioPlus'),
          storeId: String(product.storeId || ''),
        },
      ]
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
        item.productId === productId ? { ...item, quantity } : item,
      ),
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
