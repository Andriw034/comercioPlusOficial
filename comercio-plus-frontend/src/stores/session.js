import { reactive } from 'vue'
import api from '../lib/api'

export const session = reactive({
  user: null,
  store: null,
  loading: false,
  error: null
})

export async function fetchMe() {
  session.loading = true
  try {
    // ajusta al endpoint real de tu backend
    const { data } = await api.get('/api/v1/me')
    const me = data?.data || data
    session.user = me
    session.store = me?.store || null
    applyThemeFromStore(session.store)
  } catch (e) {
    session.error = e?.response?.data?.message || 'No autenticado'
  } finally { session.loading = false }
}

export function isComerciante() {
  const u = session.user
  if (!u) return false
  if (Array.isArray(u.roles)) {
    return u.roles.map(r => (r?.name ?? r).toString().toLowerCase()).includes('comerciante')
  }
  return (u?.role?.toLowerCase?.() === 'comerciante')
}

export function applyThemeFromStore(store) {
  const r = document.documentElement
  if (!store) return
  if (store.primary_color)  r.style.setProperty('--cp-primary', store.primary_color)
  if (store.text_color)     r.style.setProperty('--cp-ink', store.text_color)
  if (store.button_color)   r.style.setProperty('--cp-primary-2', store.button_color)
  if (store.background_color) r.style.setProperty('--cp-bg', store.background_color)
}
