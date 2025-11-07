// resources/js/welcome-demo-supabase.js
document.addEventListener('DOMContentLoaded', () => {
  const dom = {
    lista: document.getElementById('lista'),
    favoritos: document.getElementById('favoritos'),
    input: document.getElementById('busqueda'),
    btnBuscar: document.getElementById('btnBuscar'),
    btnClearFav: document.getElementById('btnClearFav'),
    error: document.getElementById('error'),
  }

  if (!dom.lista) return

  const fallbackImage =
    document.documentElement.dataset.placeholder ||
    '/images/placeholder.jpg'

  const state = {
    favorites: [],
    cache: new Map(),
  }

  const currency = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })

  const setError = (message = '') => {
    if (!dom.error) return
    dom.error.textContent = message
  }

  const parsePrice = (value) => {
    if (value === null || typeof value === 'undefined') return null
    const number = Number(value)
    if (!Number.isFinite(number)) return null
    return number >= 0 ? number : null
  }

  const normalizeItems = (items) =>
    items.map((item, index) => {
      const safeId = item?.id ?? `demo-${Date.now()}-${index}`
      return {
        id: String(safeId),
        name: item?.name ?? 'Producto demo',
        image_url: item?.image_url ?? null,
        price: parsePrice(item?.price ?? item?.price_cents ?? null),
      }
    })

  const renderList = (items, target, { allowFavorite = true } = {}) => {
    if (!target) return

    if (!items.length) {
      target.innerHTML = `<div class="cp-preview-empty">${target.dataset.empty || 'Sin datos disponibles'}</div>`
      return
    }

    target.innerHTML = items
      .map((item) => createCard(item, { allowFavorite }))
      .join('')
  }

  const createCard = (item, { allowFavorite }) => {
    const priceLabel =
      item.price != null ? currency.format(item.price) : 'Consultar'
    const favButton = allowFavorite
      ? `<button class="cp-card__chip js-add-fav" type="button" data-id="${item.id}" title="Agregar a favoritos">🔥</button>`
      : ''

    return `
      <article class="cp-card" data-id="${item.id}">
        <img class="cp-card__image"
             src="${item.image_url || fallbackImage}"
             alt="${item.name}"
             loading="lazy"
             onerror="this.src='${fallbackImage}'">
        <div class="cp-card__title" title="${item.name}">${item.name}</div>
        <div class="cp-card__actions">
          <span class="cp-card__price">${priceLabel}</span>
          ${favButton}
        </div>
      </article>
    `
  }

  const updateCache = (items) => {
    state.cache = new Map(items.map((item) => [item.id, item]))
  }

  const renderFavorites = () => {
    renderList(state.favorites, dom.favoritos, { allowFavorite: false })
  }

  const addFavorite = (item) => {
    state.favorites = [
      item,
      ...state.favorites.filter((fav) => fav.id !== item.id),
    ].slice(0, 4)
    renderFavorites()
  }

  const fetchDemoData = async (query = '', limit = 6) => {
    try {
      setError('Cargando demo…')
      const params = new URLSearchParams({ limit })
      if (query) params.append('q', query)
      const resp = await fetch(`/api/demo/supabase-images?${params.toString()}`)
      const payload = await resp.json()
      if (!resp.ok || payload.error) {
        throw new Error(payload.error || 'demo_http_error')
      }
      setError('')
      const items = Array.isArray(payload.data) ? payload.data : []
      return normalizeItems(items)
    } catch (error) {
      console.error('Error welcome demo:', error)
      setError('No pudimos cargar tu demo en este momento.')
      return []
    }
  }

  const hydrate = async (query = '') => {
    const data = await fetchDemoData(query)
    updateCache(data)
    renderList(data, dom.lista)
    if (!data.length) {
      setError('No encontramos productos para esta búsqueda.')
    } else {
      setError('')
    }
    if (!state.favorites.length && data.length) {
      state.favorites = data.slice(0, 3)
      renderFavorites()
    }
  }

  dom.btnBuscar?.addEventListener('click', () => {
    hydrate(dom.input?.value.trim() || '')
  })

  dom.input?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault()
      hydrate(dom.input.value.trim())
    }
  })

  dom.btnClearFav?.addEventListener('click', () => {
    state.favorites = []
    renderFavorites()
  })

  dom.lista.addEventListener('click', (event) => {
    const button = event.target.closest('.js-add-fav')
    if (!button) return
    const candidate = state.cache.get(button.dataset.id)
    if (candidate) addFavorite(candidate)
  })

  hydrate()
})
