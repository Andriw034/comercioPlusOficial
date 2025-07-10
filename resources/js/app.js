// resources/js/app.js
import { createApp, defineComponent, computed, reactive } from 'vue'
import '../css/app.css'

/* ==========
   1) HERO mínimo (si existe #app, como en welcome.blade)
========== */
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

/* ==========
   2) Catálogo (demo Vue en Blade) — se monta en #vue-catalog
========== */
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
