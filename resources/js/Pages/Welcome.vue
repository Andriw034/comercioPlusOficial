<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'

const menu = ref(false)
const y = ref(0)
const onScroll = () => { y.value = window.scrollY || 0 }

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div class="min-h-screen text-white bg-black">
    <!-- NAVBAR sticky -->
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-black/40 backdrop-blur-md">
      <nav class="mx-auto max-w-7xl h-16 px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="h-8 w-8 rounded-full bg-orange-500 shadow-[0_0_32px_rgba(255,98,0,.4)]"></span>
          <span class="font-semibold tracking-wide">Comercio<span class="text-orange-500">Plus</span></span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm">
          <a href="#inicio" class="hover:text-orange-400">Inicio</a>
          <a href="#categorias" class="hover:text-orange-400">Categorías</a>
          <a href="#tiendas" class="hover:text-orange-400">Tiendas</a>
          <a href="#perfil" class="hover:text-orange-400">Perfil</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <Link href="/login" class="px-4 py-2 rounded-xl border border-white/15 hover:bg-white/5 text-sm">Entrar</Link>
          <Link href="/register" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-400 text-sm font-medium shadow-lg shadow-orange-500/20">Crear cuenta</Link>
        </div>

        <button class="md:hidden p-2" @click="menu=!menu" aria-label="Abrir menú">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
          </svg>
        </button>
      </nav>

      <transition name="fade">
        <div v-if="menu" class="md:hidden px-6 py-4 bg-black/90 border-t border-white/10">
          <div class="flex flex-col gap-3">
            <a href="#inicio" @click="menu=false" class="py-1 hover:text-orange-400">Inicio</a>
            <a href="#categorias" @click="menu=false" class="py-1 hover:text-orange-400">Categorías</a>
            <a href="#tiendas" @click="menu=false" class="py-1 hover:text-orange-400">Tiendas</a>
            <a href="#perfil" @click="menu=false" class="py-1 hover:text-orange-400">Perfil</a>
            <div class="pt-2 flex gap-2">
              <Link href="/login" class="flex-1 px-4 py-2 rounded-lg border border-white/15 text-center hover:bg-white/5">Entrar</Link>
              <Link href="/register" class="flex-1 px-4 py-2 rounded-lg bg-orange-500 text-center hover:bg-orange-400">Crear cuenta</Link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- HERO -->
    <section id="inicio" class="relative h-[92vh] w-full overflow-hidden bg-black">
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/10 to-black/80"></div>
      <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7e87?q=80&w=2400&auto=format&fit=crop" alt="Auto parts" class="absolute inset-0 w-full h-full object-cover" />

      <div class="relative z-10 max-w-7xl mx-auto h-full px-6 flex flex-col justify-center">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-[1.1] animate-rise">
          Tu vitrina de repuestos y <span class="text-orange-500">accesorios</span> en minutos
        </h1>
        <p class="mt-4 max-w-xl text-base md:text-lg opacity-90">
          Organiza en línea tu catálogo y vende a clientes locales. Crea tu tienda y empieza a generar ingresos hoy.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <Link href="/stores" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/25">Explora tiendas</Link>
          <Link href="/register" class="px-6 py-3 rounded-xl border border-white/15 hover:bg-white/5">Crea tu tienda</Link>
        </div>
      </div>
    </section>

    <!-- CATEGORÍAS -->
    <section id="categorias" class="py-20 bg-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Categorías populares</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">A</span>
            </div>
            <p class="text-sm">Accesorios</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">L</span>
            </div>
            <p class="text-sm">Llantas</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">F</span>
            </div>
            <p class="text-sm">Frenos</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">M</span>
            </div>
            <p class="text-sm">Motor</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">S</span>
            </div>
            <p class="text-sm">Suspensión</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">B</span>
            </div>
            <p class="text-sm">Baterías</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">L</span>
            </div>
            <p class="text-sm">Luces</p>
          </div>
          <div class="text-center p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <div class="w-12 h-12 mx-auto mb-2 bg-orange-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">H</span>
            </div>
            <p class="text-sm">Herramientas</p>
          </div>
        </div>
      </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section id="tiendas" class="py-20 bg-black">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Productos destacados</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 hover:border-white/20 transition">
            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=400&auto=format&fit=crop" alt="Producto 1" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h3 class="font-semibold">Filtro de Aire Premium</h3>
              <p class="text-sm opacity-80 mt-1">Compatible con Toyota</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-orange-500 font-bold">$25.00</span>
                <Link href="/products/1" class="text-orange-400 hover:text-orange-300">Ver</Link>
              </div>
            </div>
          </div>
          <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 hover:border-white/20 transition">
            <img src="https://images.unsplash.com/photo-1606761562877-0ca8c8d8a5a8?q=80&w=400&auto=format&fit=crop" alt="Producto 2" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h3 class="font-semibold">Bujías Iridium</h3>
              <p class="text-sm opacity-80 mt-1">Set de 4 para Honda</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-orange-500 font-bold">$45.00</span>
                <Link href="/products/2" class="text-orange-400 hover:text-orange-300">Ver</Link>
              </div>
            </div>
          </div>
          <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 hover:border-white/20 transition">
            <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7e87?q=80&w=400&auto=format&fit=crop" alt="Producto 3" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h3 class="font-semibold">Pastillas de Freno</h3>
              <p class="text-sm opacity-80 mt-1">Cerámicas para Ford</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-orange-500 font-bold">$60.00</span>
                <Link href="/products/3" class="text-orange-400 hover:text-orange-300">Ver</Link>
              </div>
            </div>
          </div>
          <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 hover:border-white/20 transition">
            <img src="https://images.unsplash.com/photo-1583121274602-3e2820c69888?q=80&w=400&auto=format&fit=crop" alt="Producto 4" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h3 class="font-semibold">Batería AGM</h3>
              <p class="text-sm opacity-80 mt-1">Alta durabilidad</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-orange-500 font-bold">$120.00</span>
                <Link href="/products/4" class="text-orange-400 hover:text-orange-300">Ver</Link>
              </div>
            </div>
          </div>
        </div>
        <div class="text-center mt-12">
          <Link href="/products" class="px-8 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/25">Ver más productos</Link>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="perfil" class="py-20 bg-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">¿Listo para vender?</h2>
        <p class="text-lg opacity-80 mb-8 max-w-2xl mx-auto">Únete a cientos de vendedores que ya están creciendo su negocio con ComercioPlus.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link href="/register" class="px-8 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/25">Crear tu tienda gratis</Link>
          <Link href="/stores" class="px-8 py-3 rounded-xl border border-white/15 hover:bg-white/5">Explorar tiendas</Link>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-white/10 bg-black py-8">
      <div class="max-w-7xl mx-auto px-6 text-center text-sm opacity-80">
        © {{ new Date().getFullYear() }} ComercioPlus. Todos los derechos reservados. | Términos | Privacidad | Contacto
      </div>
    </footer>
  </div>
</template>

<style>
html { scroll-behavior: smooth; }

@keyframes rise { 0%{transform:translateY(12px); opacity:0} 100%{transform:translateY(0); opacity:1} }

.animate-rise { animation: rise .6s ease-out both; }
</style>
