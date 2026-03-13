import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react'

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

export interface CartProductInput {
  id: string | number
  name: string
  price: number
  image: string
  seller: string
  storeId: string | number
}

type CartStore = {
  storeId: string
  storeName: string
  storeSlug: string
  items: CartItem[]
}

interface CartContextType {
  cart: CartStore | null
  items: CartItem[]
  totalItems: number
  totalPrice: number
  storeId: string | null
  storeName: string | null
  addToCart: (product: CartProductInput, storeId: string, storeName: string, storeSlug: string) => void
  removeFromCart: (productId: string) => void
  updateQuantity: (productId: string, quantity: number) => void
  clearCart: () => void
  switchStore: (storeId: string, storeName: string, storeSlug: string) => void
  switchStoreAndAdd: (product: CartProductInput, storeId: string, storeName: string, storeSlug: string) => void
}

const CART_STORAGE_KEY_PREFIX = 'cart'
const USER_STORAGE_KEY = 'user'
const GUEST_SCOPE_STORAGE_KEY = 'cart_guest_scope'
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

const getGuestScope = (): string => {
  if (typeof window === 'undefined') return 'guest-server'

  const existing = sessionStorage.getItem(GUEST_SCOPE_STORAGE_KEY)
  if (existing && existing.trim().length > 0) return existing

  const generated =
    typeof crypto !== 'undefined' && 'randomUUID' in crypto
      ? crypto.randomUUID()
      : `guest-${Date.now()}-${Math.floor(Math.random() * 1000)}`
  sessionStorage.setItem(GUEST_SCOPE_STORAGE_KEY, generated)
  return generated
}

const resolveCartStorageKey = (): string => {
  if (typeof window === 'undefined') return `${CART_STORAGE_KEY_PREFIX}:guest-server`

  const rawUser = sessionStorage.getItem(USER_STORAGE_KEY) || localStorage.getItem(USER_STORAGE_KEY)
  if (rawUser) {
    try {
      const parsed = JSON.parse(rawUser) as { id?: unknown }
      const userId = Number(parsed?.id)
      if (Number.isFinite(userId) && userId > 0) {
        return `${CART_STORAGE_KEY_PREFIX}:user:${userId}`
      }
    } catch {
      // Fall back to guest scope.
    }
  }

  return `${CART_STORAGE_KEY_PREFIX}:guest:${getGuestScope()}`
}

const readStoredCart = (storageKey: string): CartStore | null => {
  if (typeof window === 'undefined') return null

  try {
    const raw = localStorage.getItem(storageKey)
    if (!raw) return null

    const parsed = JSON.parse(raw)

    // Old format was an array - discard silently
    if (Array.isArray(parsed)) return null

    if (!parsed || typeof parsed !== 'object') return null
    if (!parsed.storeId || !parsed.storeName) return null

    const items = Array.isArray(parsed.items)
      ? (parsed.items.map(sanitizeCartItem).filter(Boolean) as CartItem[])
      : []

    return {
      storeId: String(parsed.storeId),
      storeName: String(parsed.storeName),
      storeSlug: String(parsed.storeSlug || ''),
      items,
    }
  } catch {
    return null
  }
}

const makeCartItem = (product: CartProductInput, storeId: string): CartItem => ({
  id: `${String(product.id)}-${Date.now()}`,
  productId: String(product.id || '').trim(),
  name: String(product.name || 'Producto'),
  price: toSafeNumber(product.price),
  quantity: 1,
  image: String(product.image || ''),
  seller: String(product.seller || 'ComercioPlus'),
  storeId: String(product.storeId || storeId),
})

export function CartProvider({ children }: { children: ReactNode }) {
  const [storageKey, setStorageKey] = useState<string>(resolveCartStorageKey)
  const [cart, setCart] = useState<CartStore | null>(() => readStoredCart(resolveCartStorageKey()))

  useEffect(() => {
    const refreshCartScope = () => {
      const nextKey = resolveCartStorageKey()
      setStorageKey(nextKey)
      setCart(readStoredCart(nextKey))
    }

    refreshCartScope()
    window.addEventListener('auth:session-changed', refreshCartScope as EventListener)
    window.addEventListener('storage', refreshCartScope)

    return () => {
      window.removeEventListener('auth:session-changed', refreshCartScope as EventListener)
      window.removeEventListener('storage', refreshCartScope)
    }
  }, [])

  useEffect(() => {
    try {
      if (cart === null) {
        localStorage.removeItem(storageKey)
        return
      }
      localStorage.setItem(storageKey, JSON.stringify(cart))
    } catch (error) {
      console.error('Error saving cart:', error)
    }
  }, [cart, storageKey])

  const addToCart = useCallback(
    (product: CartProductInput, storeId: string, storeName: string, storeSlug: string) => {
      const productId = String(product.id || '').trim()
      if (!productId) return

      // No active cart - create one with this store
      if (cart === null) {
        setCart({
          storeId,
          storeName,
          storeSlug,
          items: [makeCartItem(product, storeId)],
        })
        return
      }

      // Same store - add or increment
      if (cart.storeId === storeId) {
        setCart((prev) => {
          if (!prev) return { storeId, storeName, storeSlug, items: [makeCartItem(product, storeId)] }
          const existing = prev.items.find((i) => i.productId === productId)
          return {
            ...prev,
            items: existing
              ? prev.items.map((i) =>
                  i.productId === productId ? { ...i, quantity: i.quantity + 1 } : i,
                )
              : [...prev.items, makeCartItem(product, storeId)],
          }
        })
        return
      }

      // Different store - dispatch conflict event, do NOT modify cart
      window.dispatchEvent(
        new CustomEvent('cart:store-conflict', {
          detail: {
            newStoreId: storeId,
            newStoreName: storeName,
            newStoreSlug: storeSlug,
            pendingItem: product,
          },
        }),
      )
    },
    [cart],
  )

  const removeFromCart = useCallback((productId: string) => {
    setCart((prev) => {
      if (!prev) return null
      const newItems = prev.items.filter((i) => i.productId !== productId)
      return newItems.length === 0 ? null : { ...prev, items: newItems }
    })
  }, [])

  const updateQuantity = useCallback(
    (productId: string, quantity: number) => {
      if (quantity <= 0) {
        removeFromCart(productId)
        return
      }
      setCart((prev) => {
        if (!prev) return null
        return {
          ...prev,
          items: prev.items.map((i) => (i.productId === productId ? { ...i, quantity } : i)),
        }
      })
    },
    [removeFromCart],
  )

  const clearCart = useCallback(() => {
    setCart(null)
  }, [])

  const switchStore = useCallback((storeId: string, storeName: string, storeSlug: string) => {
    setCart({ storeId, storeName, storeSlug, items: [] })
  }, [])

  // Atomic: clears previous cart and adds item for new store in one setState call
  const switchStoreAndAdd = useCallback(
    (product: CartProductInput, storeId: string, storeName: string, storeSlug: string) => {
      const productId = String(product.id || '').trim()
      if (!productId) return
      setCart({
        storeId,
        storeName,
        storeSlug,
        items: [makeCartItem(product, storeId)],
      })
    },
    [],
  )

  const items = cart?.items ?? []
  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0)
  const totalPrice = items.reduce((sum, item) => sum + item.price * item.quantity, 0)
  const storeId = cart?.storeId ?? null
  const storeName = cart?.storeName ?? null

  return (
    <CartContext.Provider
      value={{
        cart,
        items,
        totalItems,
        totalPrice,
        storeId,
        storeName,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        switchStore,
        switchStoreAndAdd,
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
