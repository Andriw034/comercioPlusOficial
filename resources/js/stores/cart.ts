import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { CartItem, Product } from '../types'

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])
  const loading = ref(false)

  const totalItems = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  const totalPrice = computed(() => {
    return items.value.reduce((total, item) => {
      return total + (item.product.price * item.quantity)
    }, 0)
  })

  const addToCart = (product: Product, quantity: number = 1) => {
    const existingItem = items.value.find(item => item.product_id === product.id)

    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({
        id: Date.now(), // Temporary ID for frontend
        product_id: product.id,
        quantity,
        product
      })
    }
  }

  const removeFromCart = (productId: number) => {
    const index = items.value.findIndex(item => item.product_id === productId)
    if (index > -1) {
      items.value.splice(index, 1)
    }
  }

  const updateQuantity = (productId: number, quantity: number) => {
    const item = items.value.find(item => item.product_id === productId)
    if (item) {
      if (quantity <= 0) {
        removeFromCart(productId)
      } else {
        item.quantity = quantity
      }
    }
  }

  const clearCart = () => {
    items.value = []
  }

  const isInCart = (productId: number) => {
    return items.value.some(item => item.product_id === productId)
  }

  const getItemQuantity = (productId: number) => {
    const item = items.value.find(item => item.product_id === productId)
    return item ? item.quantity : 0
  }

  return {
    items,
    loading,
    totalItems,
    totalPrice,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart,
    isInCart,
    getItemQuantity
  }
})
