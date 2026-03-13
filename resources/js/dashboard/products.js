(() => {
  const STORAGE_KEY = 'cp_products'

  const demoProducts = [
    {
      id: '1',
      name: 'Llantas',
      category: 'Llantas',
      price: 148.99,
      stock: 128,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-llanta.jpg',
      description: 'Llantas de alta calidad para motocicletas.',
      status: true,
    },
    {
      id: '2',
      name: 'Casco integral',
      category: 'Accesorios',
      price: 89.99,
      stock: 50,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-casco.jpg',
      description: 'Casco integral para máxima protección.',
      status: true,
    },
    {
      id: '3',
      name: 'Pastillas de freno',
      category: 'Frenos',
      price: 60.95,
      stock: 30,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-freno.jpg',
      description: 'Pastillas de freno de alto rendimiento.',
      status: true,
    },
    {
      id: '4',
      name: 'Cadena para moto',
      category: 'Transmisión',
      price: 79.90,
      stock: 15,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-cadena.jpg',
      description: 'Cadena resistente para motocicletas.',
      status: false,
    },
    {
      id: '5',
      name: 'Aceite para moto',
      category: 'Lubricantes',
      price: 32.99,
      stock: 100,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-aceite.jpg',
      description: 'Aceite lubricante de alta calidad.',
      status: false,
    },
    {
      id: '6',
      name: 'Aceite para motor',
      category: 'Lubricantes',
      price: 29.99,
      stock: 80,
      imageUrl: 'https://images.unsplash.com/photo-1616627891234-motor.jpg',
      description: 'Aceite para motor de alto rendimiento.',
      status: true,
    },
  ]

  let state = {
    products: [],
    filters: {
      search: '',
      category: '',
      activeOnly: false,
      sort: 'name_asc',
    },
    modalMode: 'create', // or 'edit'
    editingId: null,
  }

  // Load state from localStorage or demo data
  function loadState() {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
      try {
        state.products = JSON.parse(saved)
      } catch {
        state.products = [...demoProducts]
      }
    } else {
      state.products = [...demoProducts]
    }
  }

  // Save state to localStorage
  function saveState() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state.products))
  }

  // Render product cards list
  function renderList() {
    const container = document.getElementById('productsGrid')
    container.innerHTML = ''

    let filtered = state.products.filter(p => {
      const matchesSearch = p.name.toLowerCase().includes(state.filters.search.toLowerCase()) ||
        p.description.toLowerCase().includes(state.filters.search.toLowerCase())
      const matchesCategory = state.filters.category ? p.category === state.filters.category : true
      const matchesActive = state.filters.activeOnly ? p.status === true : true
      return matchesSearch && matchesCategory && matchesActive
    })

    // Sort
    filtered.sort((a, b) => {
      switch (state.filters.sort) {
      case 'name_asc': return a.name.localeCompare(b.name)
      case 'name_desc': return b.name.localeCompare(a.name)
      case 'price_asc': return a.price - b.price
      case 'price_desc': return b.price - a.price
      default: return 0
      }
    })

    if (filtered.length === 0) {
      container.innerHTML = '<p class="text-white/70 text-center col-span-full">No se encontraron productos.</p>'
      return
    }

    filtered.forEach(product => {
      const card = document.createElement('div')
      card.className = 'rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/[.07] transition p-4 flex flex-col'

      const img = document.createElement('img')
      img.src = product.imageUrl
      img.alt = product.name
      img.className = 'rounded-lg object-contain max-h-48 mb-4'
      card.appendChild(img)

      const name = document.createElement('h3')
      name.textContent = product.name
      name.className = 'text-lg font-semibold mb-1'
      card.appendChild(name)

      const price = document.createElement('p')
      price.textContent = `$${product.price.toFixed(2)}`
      price.className = 'text-sm text-white/70 mb-1'
      card.appendChild(price)

      const category = document.createElement('p')
      category.textContent = product.category
      category.className = 'text-sm text-white/50 mb-2'
      card.appendChild(category)

      const status = document.createElement('span')
      status.textContent = product.status ? 'Activo' : 'Inactivo'
      status.className = product.status
        ? 'inline-block bg-emerald-500/20 text-emerald-300 rounded-full px-3 py-1 text-xs font-semibold'
        : 'inline-block bg-rose-500/20 text-rose-300 rounded-full px-3 py-1 text-xs font-semibold'
      card.appendChild(status)

      const actions = document.createElement('div')
      actions.className = 'mt-auto flex gap-2'

      const editBtn = document.createElement('button')
      editBtn.textContent = 'Editar'
      editBtn.className = 'flex-1 bg-[#FF6000] text-black py-2 rounded-full font-semibold hover:bg-[#ff7a2e] transition'
      editBtn.addEventListener('click', () => openModal('edit', product.id))
      actions.appendChild(editBtn)

      const deleteBtn = document.createElement('button')
      deleteBtn.textContent = 'Eliminar'
      deleteBtn.className = 'flex-1 bg-red-600 text-white py-2 rounded-full font-semibold hover:bg-red-700 transition'
      deleteBtn.addEventListener('click', () => {
        if (confirm(`¿Está seguro de eliminar el producto "${product.name}"?`)) {
          handleDelete(product.id)
        }
      })
      actions.appendChild(deleteBtn)

      card.appendChild(actions)

      container.appendChild(card)
    })
  }

  // Open modal for create or edit
  function openModal(mode, id = null) {
    state.modalMode = mode
    state.editingId = id

    const modal = document.getElementById('productModal')
    const title = document.getElementById('modalTitle')
    const form = document.getElementById('productForm')
    const imagePreview = document.getElementById('imagePreview')

    title.textContent = mode === 'create' ? 'Crear producto' : 'Editar producto'

    // Reset form
    form.reset()
    clearValidationErrors()

    if (mode === 'edit' && id) {
      const product = state.products.find(p => p.id === id)
      if (product) {
        form.name.value = product.name
        form.category.value = product.category
        form.price.value = product.price
        form.stock.value = product.stock
        form.imageUrl.value = product.imageUrl
        form.description.value = product.description
        form.status.checked = product.status
        imagePreview.src = product.imageUrl
      }
    } else {
      imagePreview.src = ''
    }

    modal.classList.remove('hidden')
    modal.focus()
  }

  // Close modal
  function closeModal() {
    const modal = document.getElementById('productModal')
    modal.classList.add('hidden')
    state.editingId = null
  }

  // Validate form fields
  function validateForm() {
    const form = document.getElementById('productForm')
    let valid = true

    clearValidationErrors()

    if (!form.name.value.trim()) {
      showError('nameError')
      valid = false
    }
    if (!form.category.value) {
      showError('categoryError')
      valid = false
    }
    if (!form.price.value || isNaN(form.price.value) || Number(form.price.value) < 0) {
      showError('priceError')
      valid = false
    }
    if (!form.stock.value || isNaN(form.stock.value) || Number(form.stock.value) < 0) {
      showError('stockError')
      valid = false
    }
    if (form.imageUrl.value && !isValidUrl(form.imageUrl.value)) {
      showError('imageUrlError')
      valid = false
    }

    return valid
  }

  // Show validation error
  function showError(id) {
    const el = document.getElementById(id)
    if (el) el.classList.remove('hidden')
  }

  // Clear all validation errors
  function clearValidationErrors() {
    ['nameError', 'categoryError', 'priceError', 'stockError', 'imageUrlError'].forEach(id => {
      const el = document.getElementById(id)
      if (el) el.classList.add('hidden')
    })
  }

  // Check if URL is valid
  function isValidUrl(string) {
    try {
      new URL(string)
      return true
    } catch {
      return false
    }
  }

  // Handle create product
  function handleCreate() {
    if (!validateForm()) return

    const form = document.getElementById('productForm')
    const newProduct = {
      id: crypto.randomUUID(),
      name: form.name.value.trim(),
      category: form.category.value,
      price: parseFloat(form.price.value),
      stock: parseInt(form.stock.value, 10),
      imageUrl: form.imageUrl.value.trim(),
      description: form.description.value.trim(),
      status: form.status.checked,
    }

    state.products.push(newProduct)
    saveState()
    renderList()
    closeModal()
  }

  // Handle update product
  function handleUpdate() {
    if (!validateForm()) return

    const form = document.getElementById('productForm')
    const product = state.products.find(p => p.id === state.editingId)
    if (!product) return

    product.name = form.name.value.trim()
    product.category = form.category.value
    product.price = parseFloat(form.price.value)
    product.stock = parseInt(form.stock.value, 10)
    product.imageUrl = form.imageUrl.value.trim()
    product.description = form.description.value.trim()
    product.status = form.status.checked

    saveState()
    renderList()
    closeModal()
  }

  // Handle delete product
  function handleDelete(id) {
    state.products = state.products.filter(p => p.id !== id)
    saveState()
    renderList()
  }

  // Apply filters and render
  function applyFilters() {
    const searchInput = document.getElementById('searchInput')
    const categoryFilter = document.getElementById('categoryFilter')
    const activeOnlyToggle = document.getElementById('activeOnlyToggle')
    const sortSelect = document.getElementById('sortSelect')

    state.filters.search = searchInput.value.trim()
    state.filters.category = categoryFilter.value
    state.filters.activeOnly = activeOnlyToggle.checked
    state.filters.sort = sortSelect.value

    renderList()
  }

  // Bind event listeners
  function bindEvents() {
    document.getElementById('btnNewProduct').addEventListener('click', () => openModal('create'))
    document.getElementById('modalCloseBtn').addEventListener('click', closeModal)
    document.getElementById('cancelBtn').addEventListener('click', closeModal)

    document.getElementById('productForm').addEventListener('submit', e => {
      e.preventDefault()
      if (state.modalMode === 'create') {
        handleCreate()
      } else {
        handleUpdate()
      }
    })

    document.getElementById('imageUrl').addEventListener('input', e => {
      const url = e.target.value.trim()
      const preview = document.getElementById('imagePreview')
      if (isValidUrl(url)) {
        preview.src = url
      } else {
        preview.src = ''
      }
    })

    document.getElementById('searchInput').addEventListener('input', applyFilters)
    document.getElementById('categoryFilter').addEventListener('change', applyFilters)
    document.getElementById('activeOnlyToggle').addEventListener('change', applyFilters)
    document.getElementById('sortSelect').addEventListener('change', applyFilters)
  }

  // Initialize app
  function init() {
    loadState()
    bindEvents()
    renderList()
  }

  document.addEventListener('DOMContentLoaded', init)
})()
