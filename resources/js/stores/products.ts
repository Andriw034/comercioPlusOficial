import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Product, Category, Store } from '../types'

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([])
  const categories = ref<Category[]>([])
  const stores = ref<Store[]>([])
  const loading = ref(false)
  const selectedCategory = ref<string>('')

  const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value
    return products.value.filter(product =>
      product.category_id === parseInt(selectedCategory.value)
    )
  })

  const fetchProducts = async () => {
    loading.value = true
    try {
      const response = await fetch('/api/products')
      if (!response.ok) throw new Error('Failed to fetch products')
      const data = await response.json()
      products.value = data.data || data
    } catch (error) {
      console.error('Error fetching products:', error)
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await fetch('/api/categories')
      if (!response.ok) throw new Error('Failed to fetch categories')
      const data = await response.json()
      categories.value = data.data || data
    } catch (error) {
      console.error('Error fetching categories:', error)
    }
  }

  const fetchStores = async () => {
    try {
      const response = await fetch('/api/stores')
      if (!response.ok) throw new Error('Failed to fetch stores')
      const data = await response.json()
      stores.value = data.data || data
    } catch (error) {
      console.error('Error fetching stores:', error)
    }
  }

  const getProductById = (id: number) => {
    return products.value.find(product => product.id === id)
  }

  const getCategoryById = (id: number) => {
    return categories.value.find(category => category.id === id)
  }

  const getStoreById = (id: number) => {
    return stores.value.find(store => store.id === id)
  }

  return {
    products,
    categories,
    stores,
    loading,
    selectedCategory,
    filteredProducts,
    fetchProducts,
    fetchCategories,
    fetchStores,
    getProductById,
    getCategoryById,
    getStoreById
  }
})
