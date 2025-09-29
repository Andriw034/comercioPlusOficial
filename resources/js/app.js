// resources/js/app.js
import { createApp, defineComponent, computed, reactive } from 'vue'
import '../css/app.css'

/* ========== 1) HERO mínimo (si existe #app, como en welcome.blade) ========== */
const heroRoot = document.getElementById('app')
if (heroRoot) {
  const AppRoot = {
    template: `
      <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-cp-primary to-cp-primary-2 text-white">
        <div class="p-8 rounded-3xl shadow-xl bg-white/10 backdrop-blur-md">
          <h1 class="text-3xl font-bold mb-2">ComercioPlus</h1>
          <p class="opacity-90">Laravel + Vite + Vue + Tailwind funcionando ✅</p>
        </div>
      </div>
    `
  }
  createApp(AppRoot).mount('#app')
}

/* ========== 2) Catálogo (demo Vue en Blade) — se monta en #vue-catalog ========== */
const catalogRoot = document.getElementById('vue-catalog')
if (catalogRoot) {
  const CatalogDemo = defineComponent({
    name: 'CatalogDemo',
    setup() {
      const state = reactive({
        query: '',
        categoria: 'Todos',
        categorias: ['Todos','Frenos','Iluminación','Transmisión','Accesorios','Lubricantes','Llantas'],
        items: [
          { id: 1, nombre: 'Casco integral Pro', cat: 'Accesorios', precio: 280000, img: 'https://images.unsplash.com/photo-1542362567-b07e54358753?q=80&w=800&auto=format&fit=crop' },
          { id: 2, nombre: 'Pastillas de freno', cat: 'Frenos', precio: 45000, img: 'https://images.unsplash.com/photo-1526045478516-99145907023c?q=80&w=800&auto=format&fit=crop' },
          { id: 3, nombre: 'Cadena reforzada', cat: 'Transmisión', precio: 120000, img: 'https://images.unsplash.com/photo-1602320734573-1b57f5813996?q=80&w=800&auto=format&fit=crop' },
          { id: 4, nombre: 'Bombillo LED H4', cat: 'Iluminación', precio: 35000, img: 'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?q=80&w=800&auto=format&fit=crop' },
          { id: 5, nombre: 'Aceite sintético 10W40', cat: 'Lubricantes', precio: 58000, img: 'https://images.unsplash.com/photo-1589578527966-1e9b2ae05a0e?q=80&w=800&auto=format&fit=crop' },
          { id: 6, nombre: 'Llanta 130/70 R17', cat: 'Llantas', precio: 310000, img: 'https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=800&auto=format&fit=crop' },
        ]
      })

      const filtrados = computed(() => {
        return state.items.filter(it => {
          const pasaCat = state.categoria === 'Todos' || it.cat === state.categoria
          const pasaTxt = state.query.trim().length === 0 ||
                          it.nombre.toLowerCase().includes(state.query.toLowerCase())
          return pasaCat && pasaTxt
        })
      })

      return { state, filtrados }
    },
    template: `
      <div class="rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl p-6">
        <!-- filtros -->
        <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
          <div class="flex-1">
            <input v-model="state.query" type="search" placeholder="Buscar producto..."
              class="w-full rounded-xl bg-white/10 text-white placeholder-white/60 ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30" />
          </div>
          <div class="flex flex-wrap gap-2">
            <select v-model="state.categoria"
              class="rounded-xl bg-white/10 text-white ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30">
              <option v-for="c in state.categorias" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
        </div>

        <!-- grid -->
        <div class="mt-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="it in filtrados" :key="it.id"
               class="group rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5">
            <div class="aspect-[4/3] overflow-hidden">
              <img :src="it.img" :alt="it.nombre"
                   class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            </div>
            <div class="p-3">
              <div class="text-white/95 font-medium truncate">{{ it.nombre }}</div>
              <div class="text-white/70 text-xs">{{ it.cat }}</div>
              <div class="mt-2 flex items-center justify-between">
                <span class="text-white font-semibold">$ {{ new Intl.NumberFormat('es-CO').format(it.precio) }}</span>
                <button class="text-xs rounded-full px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white transition">
                  Agregar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- vacíos -->
        <div v-if="filtrados.length === 0" class="text-center text-white/80 py-10">
          No hay resultados para tu búsqueda.
        </div>
      </div>
    `
  })

  createApp(CatalogDemo).mount('#vue-catalog')
}

/* ========== 3) Wizard Perfil + Tienda + Dashboard (Vue) ========== */
console.log('[CP] app.js cargado — buscando #store-wizard...');
const wizardRoot = document.getElementById('store-wizard')
console.log('[CP] wizardRoot =', wizardRoot);

if (wizardRoot) {
  const StoreWizard = defineComponent({
    name: 'StoreWizard',
    setup() {
      const state = reactive({
        step: 'form', // 'form' | 'dashboard'
        nombre: '',
        descripcion: '',
        logoUrl: '',
        fondoUrl: '',
        error: '',
      })

      // Cargar imagen (logo / fondo)
      const onPick = (e, key) => {
        const file = e.target.files?.[0]
        if (!file) return
        const url = URL.createObjectURL(file)
        state[key] = url
      }

      const crearTienda = () => {
        state.error = ''
        const n = state.nombre.trim()
        if (!n) {
          state.error = 'Por favor ingresa el nombre de la tienda.'
          return
        }
        state.step = 'dashboard'
        // scroll arriba
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }

      const editar = () => (state.step = 'form')

      return { state, onPick, crearTienda, editar }
    },
    template: `
      <div>
        <!-- ======= FORMULARIO ======= -->
        <div v-if="state.step === 'form'"
             class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6 shadow-2xl">

          <h2 class="text-xl font-extrabold">🛍️ Crear Perfil & Tienda</h2>
          <p class="text-white/70 mt-1">Completa la información. Verás el logo y el fondo en el dashboard.</p>

          <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-white/80 mb-1">Nombre de la tienda</label>
              <input v-model="state.nombre" type="text" placeholder="Ej. ComercioPlus Motos"
                class="w-full rounded-xl bg-white/10 text-white placeholder-white/60 ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30" />
            </div>

            <div class="md:row-span-2">
              <label class="block text-sm text-white/80 mb-1">Descripción</label>
              <textarea v-model="state.descripcion" rows="4"
                placeholder="Cuenta qué vendes y tu propuesta de valor"
                class="w-full rounded-xl bg-white/10 text-white placeholder-white/60 ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30"></textarea>
            </div>

            <div>
              <label class="block text-sm text-white/80 mb-1">Logo</label>
              <div class="flex items-center gap-3">
                <input type="file" accept="image/*" @change="e => onPick(e, 'logoUrl')"
                       class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl
                              file:border-0 file:bg-orange-500 file:text-white hover:file:bg-orange-600" />
                <div v-if="state.logoUrl" class="h-16 w-16 rounded-xl overflow-hidden ring-1 ring-white/15">
                  <img :src="state.logoUrl" alt="logo" class="h-full w-full object-cover" />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm text-white/80 mb-1">Imagen de fondo</label>
              <div class="flex items-center gap-3">
                <input type="file" accept="image/*" @change="e => onPick(e, 'fondoUrl')"
                       class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl
                              file:border-0 file:bg-orange-500 file:text-white hover:file:bg-orange-600" />
                <div v-if="state.fondoUrl" class="h-16 w-28 rounded-xl overflow-hidden ring-1 ring-white/15">
                  <img :src="state.fondoUrl" alt="fondo" class="h-full w-full object-cover" />
                </div>
              </div>
            </div>
          </div>

          <p v-if="state.error" class="mt-3 text-red-400 text-sm">{{ state.error }}</p>

          <div class="mt-6">
            <button @click="crearTienda"
              class="rounded-2xl px-5 py-3 font-extrabold bg-orange-500 hover:bg-orange-600 text-black">
              Crear tienda
            </button>
          </div>
        </div>

        <!-- ======= DASHBOARD ======= -->
        <div v-else class="mt-6">
          <!-- Header con fondo + logo -->
          <div class="relative h-56 rounded-3xl overflow-hidden ring-1 ring-white/10">
            <div v-if="state.fondoUrl" class="absolute inset-0">
              <img :src="state.fondoUrl" class="h-full w-full object-cover" alt="cover" />
            </div>
            <div v-else class="absolute inset-0 bg-gradient-to-br from-slate-900 to-slate-800"></div>
            <div class="absolute inset-0 bg-black/35"></div>

            <div class="absolute left-5 bottom-5 flex items-center gap-4">
              <div class="h-16 w-16 rounded-2xl overflow-hidden ring-2 ring-black/70 bg-black/60 grid place-items-center">
                <img v-if="state.logoUrl" :src="state.logoUrl" class="h-full w-full object-cover" alt="logo" />
                <span v-else class="text-white/80 text-xs font-bold">LOGO</span>
              </div>
              <div>
                <div class="text-white text-lg font-extrabold">{{ state.nombre || 'Mi Tienda' }}</div>
                <div v-if="state.descripcion" class="text-white/80 text-sm max-w-lg">{{ state.descripcion }}</div>
              </div>
            </div>
          </div>

          <!-- Productos (placeholder) -->
          <div class="mt-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-extrabold">Productos</h3>
              <button @click="editar" class="text-sm text-white/80 hover:text-white underline underline-offset-4">
                Editar tienda
              </button>
            </div>

            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div v-for="i in 8" :key="i" class="rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5">
                <div class="aspect-[4/3] bg-white/10"></div>
                <div class="p-3">
                  <div class="text-white/95 font-medium truncate">Producto {{ i }}</div>
                  <div class="text-white/70 text-xs">Categoría</div>
                  <div class="mt-2 flex items-center justify-between">
                    <span class="text-white font-semibold">$ 99.900</span>
                    <button class="text-xs rounded-full px-3 py-1 bg-orange-500 hover:bg-orange-600 text-black font-bold">
                      Agregar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `
  })

  createApp(StoreWizard).mount('#store-wizard')
}
