import { ref, reactive } from 'vue'
import { api } from '@/api/client'

export function useProducts() {
  const loading = ref(false)
  const error   = ref(null)
  const items   = ref([])
  const meta    = reactive({ current_page: 1, per_page: 12, total: 0, last_page: 1 })

  const filters = reactive({
    q: '',
    category_id: null,
    offer: null, // 1 | 0 | null
    min_price: null,
    max_price: null,
    sort: 'newest',
  })

  async function fetch(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params = { ...filters, page, per_page: meta.per_page }
      Object.keys(params).forEach(k => (params[k] === null || params[k] === '') && delete params[k])
      const { data } = await api.get('/api/products', { params })
      items.value = data.data
      Object.assign(meta, data.meta)
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  }

  return { items, meta, filters, loading, error, fetch }
}

