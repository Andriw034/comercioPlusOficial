// resources/js/welcome-demo-supabase.js
document.addEventListener('DOMContentLoaded', () => {
  const CLAVE_PEXELS = 'RcWPHKzcM1v3IlZ2NwHxDtO5kkym5qi59yiPrYRTHTEpmGLRjdkcjWYe'
  const URL_FAVORITOS = 'https://68dff0d693207c4b47933ecb.mockapi.io/api/v1/favoritos'

  let queryActual = 'motorcycle parts'
  const urlFotos = (q = queryActual, limit = 3) =>
    `https://api.pexels.com/v1/search?query=${encodeURIComponent(q)}&per_page=${limit}`

  const dom = {
    lista: document.getElementById('lista'),
    favoritos: document.getElementById('favoritos'),
    input: document.getElementById('busqueda'),
    btnBuscar: document.getElementById('btnBuscar'),
    btnClearFav: document.getElementById('btnClearFav'),
    error: document.getElementById('error'),
  }

  if (!dom.lista) return

  const placeholder =
    document.documentElement.dataset.placeholder || '/images/placeholder.jpg'
  const state = {
    catalogo: [],
    favoritos: [],
  }

  const log = (...args) => console.log('[DEMO]', ...args)
  const warn = (...args) => console.warn('[DEMO]', ...args)
  const fail = (ctx, e) => console.error('[DEMO]', ctx, e)

  const setError = (msg = '') => {
    if (dom.error) dom.error.textContent = msg
  }

  const renderCatalogo = (items) => {
    if (!items.length) {
      dom.lista.innerHTML = `<div class="cp-preview-empty">${dom.lista.dataset.empty || 'Sin resultados'}</div>`
      return
    }

    dom.lista.innerHTML = items
      .map(
        (item) => `
        <article class="cp-card cp-card-demo" data-id="${item.id}">
          <img class="cp-card-demo__image"
               src="${item.image_url}"
               alt="${item.name}"
               loading="lazy"
               onerror="this.src='${placeholder}'">
          <button class="cp-card__chip cp-card__chip--floating js-add-fav"
                  type="button"
                  data-url="${item.image_url}"
                  title="Agregar a favoritos">👍</button>
        </article>`
      )
      .join('')

    dom.lista.querySelectorAll('.js-add-fav').forEach((btn) => {
      btn.addEventListener('click', () => guardarFavorito(btn.dataset.url))
    })
  }

  const renderFavoritos = (items) => {
    if (!items.length) {
      dom.favoritos.innerHTML = `<div class="cp-preview-empty">${dom.favoritos.dataset.empty || 'Sin favoritos'}</div>`
      return
    }

    dom.favoritos.innerHTML = items
      .map(
        (fav) => `
        <article class="cp-card cp-card-demo" data-id="${fav.id}">
          <img class="cp-card-demo__image"
               src="${fav.image_url || placeholder}"
               alt="${fav.title || 'favorito'}"
               loading="lazy"
               onerror="this.src='${placeholder}'">
          <button class="cp-card__chip cp-card__chip--floating cp-card__chip--remove js-remove-fav"
                  type="button"
                  data-id="${fav.id}"
                  title="Quitar favorito">🗑️</button>
        </article>`
      )
      .join('')

    dom.favoritos.querySelectorAll('.js-remove-fav').forEach((btn) => {
      btn.addEventListener('click', () => eliminarFavorito(btn.dataset.id))
    })
  }

  const normalizarFotos = (photos = []) =>
    photos.slice(0, 3).map((photo, index) => ({
      id: photo.id || `pexels-${index}`,
      name: photo.alt || `Repuesto ${index + 1}`,
      image_url:
        photo.src?.large ||
        photo.src?.medium ||
        photo.src?.original ||
        placeholder,
      price: 25000 * (index + 2),
    }))

  const cargarMotos = async () => {
    log('GET Pexels →', urlFotos(queryActual))
    try {
      setError('Cargando demo...')
      const resp = await fetch(urlFotos(queryActual), {
        headers: { Authorization: CLAVE_PEXELS },
      })
      const body = await resp.json().catch(() => ({}))

      if (!resp.ok) {
        setError(`Error ${resp.status}: ${body?.message || 'sin detalle'}`)
        return
      }

      const fotos = normalizarFotos(body.photos || [])
      if (!fotos.length) {
        setError('Sin resultados para esta búsqueda.')
        dom.lista.innerHTML = ''
        return
      }

      state.catalogo = fotos
      renderCatalogo(fotos)
      setError('')
    } catch (e) {
      fail('cargarMotos', e)
      setError('Error cargando imágenes: ' + e.message)
    }
  }

  const listarFavoritos = async () => {
    log('GET favoritos →', URL_FAVORITOS)
    try {
      const resp = await fetch(URL_FAVORITOS)
      const data = await resp.json().catch(() => [])

      if (!resp.ok) {
        setError(`Error ${resp.status}: ${data?.message || ''}`)
        return
      }

      state.favoritos = data || []
      renderFavoritos(state.favoritos)
    } catch (e) {
      fail('listarFavoritos', e)
      setError('Error cargando favoritos: ' + e.message)
    }
  }

  const guardarFavorito = async (imageUrl) => {
    if (!imageUrl) return
    log('POST favorito →', imageUrl)
    try {
      const resp = await fetch(URL_FAVORITOS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image_url: imageUrl, title: queryActual }),
      })
      const data = await resp.json().catch(() => ({}))

      if (resp.status !== 200 && resp.status !== 201) {
        setError(`Error ${resp.status}: ${data?.message || ''}`)
        return
      }

      await listarFavoritos()
    } catch (e) {
      fail('guardarFavorito', e)
      setError('Error guardando favorito: ' + e.message)
    }
  }

  const eliminarFavorito = async (id) => {
    if (!id) return
    log('DELETE favorito id=', id)
    try {
      const resp = await fetch(`${URL_FAVORITOS}/${id}`, { method: 'DELETE' })
      const data = await resp.json().catch(() => ({}))

      if (resp.status !== 200 && resp.status !== 204) {
        setError(`Error ${resp.status}: ${data?.message || ''}`)
        return
      }

      await listarFavoritos()
    } catch (e) {
      fail('eliminarFavorito', e)
      setError('Error al eliminar: ' + e.message)
    }
  }

  dom.btnBuscar?.addEventListener('click', () => {
    const q = dom.input?.value.trim()
    queryActual = q || 'motorcycle parts'
    cargarMotos()
  })

  dom.input?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault()
      queryActual = dom.input.value.trim() || 'motorcycle parts'
      cargarMotos()
    }
  })

  dom.btnClearFav?.addEventListener('click', async () => {
    if (!state.favoritos.length) {
      dom.favoritos.innerHTML = `<div class="cp-preview-empty">${dom.favoritos.dataset.empty || 'Sin favoritos'}</div>`
      return
    }
    const snapshot = [...state.favoritos]
    for (const fav of snapshot) {
      await eliminarFavorito(fav.id)
    }
  })

  window.addEventListener('error', (e) => fail('window.error', e.error || e.message))
  window.addEventListener('unhandledrejection', (e) => fail('unhandledrejection', e.reason))

  listarFavoritos()
  cargarMotos()
})
