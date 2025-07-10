<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'

// Cambia esta imagen por la tuya (ideal 2400x1200 o más)
const HERO_URL = 'https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=2400&auto=format&fit=crop'

const menu = ref(false)
const y = ref(0)
const onScroll = () => { y.value = window.scrollY || 0 }

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
 <div class="p-6 rounded-3xl bg-cp-primary text-white">Tailwind OK</div>

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
          <a href="#funciones" class="hover:text-orange-400">Funciones</a>
          <a href="#tiendas" class="hover:text-orange-400">Tiendas</a>
          <a href="#contacto" class="hover:text-orange-400">Contacto</a>
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
            <a href="#funciones" @click="menu=false" class="py-1 hover:text-orange-400">Funciones</a>
            <a href="#tiendas" @click="menu=false" class="py-1 hover:text-orange-400">Tiendas</a>
            <a href="#contacto" @click="menu=false" class="py-1 hover:text-orange-400">Contacto</a>
            <div class="pt-2 flex gap-2">
              <Link href="/login" class="flex-1 px-4 py-2 rounded-lg border border-white/15 text-center hover:bg-white/5">Entrar</Link>
              <Link href="/register" class="flex-1 px-4 py-2 rounded-lg bg-orange-500 text-center hover:bg-orange-400">Crear cuenta</Link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- HERO full-width con parallax -->
    <section id="inicio" class="relative h-[92vh] w-full overflow-hidden">
      <div
        class="absolute inset-0 will-change-transform"
        :style="{
          backgroundImage: `url(${HERO_URL})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          transform: `translateY(${y * 0.18}px) scale(1.04)`
        }"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/10 to-black/80"></div>

      <div class="relative z-10 max-w-7xl mx-auto h-full px-6 flex flex-col justify-center">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-[1.1] animate-rise">
          Vende más con <span class="text-orange-500">ComercioPlus</span>
        </h1>
        <p class="mt-4 max-w-xl text-base md:text-lg opacity-90">
          Crea tu catálogo, gestiona inventario y cobra de forma segura desde un panel simple.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <Link href="/register" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/25">Empezar gratis</Link>
          <a href="#funciones" class="px-6 py-3 rounded-xl border border-white/15 hover:bg-white/5">Ver funciones</a>
        </div>
      </div>

      <!-- halos decorativos -->
      <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-orange-500/15 blur-3xl animate-float"></div>
      <div class="pointer-events-none absolute -bottom-24 -right-16 h-96 w-96 rounded-full bg-fuchsia-500/10 blur-3xl animate-float-delayed"></div>
    </section>

    <!-- FUNCIONES -->
    <section id="funciones" class="relative py-20 bg-gradient-to-b from-black to-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold">Todo lo que necesitas</h2>
        <p class="mt-2 opacity-80">Herramientas para vender sin complicaciones.</p>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Catálogo</div>
            <div class="mt-2 text-xl font-semibold">Productos y categorías</div>
            <p class="mt-2 text-sm opacity-80">Organiza repuestos por marcas y variantes.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Pagos</div>
            <div class="mt-2 text-xl font-semibold">Cobros seguros</div>
            <p class="mt-2 text-sm opacity-80">Conecta tu pasarela favorita y cobra fácil.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Logística</div>
            <div class="mt-2 text-xl font-semibold">Envíos claros</div>
            <p class="mt-2 text-sm opacity-80">Precios y tiempos de entrega sin sorpresas.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Analítica</div>
            <div class="mt-2 text-xl font-semibold">Métricas en vivo</div>
            <p class="mt-2 text-sm opacity-80">Ventas, vistas y conversión en un vistazo.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SHOWCASE TIENDAS (scroll horizontal) -->
    <section id="tiendas" class="py-20 bg-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-3xl md:text-4xl font-bold">Tiendas destacadas</h2>
          <Link href="/stores" class="hidden sm:inline-flex px-5 py-2 rounded-xl border border-white/15 hover:bg-white/5">Ver todas</Link>
        </div>

        <div class="mt-8 overflow-x-auto no-scrollbar">
          <div class="flex gap-6 min-w-max">
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s1/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Moto Center Pro</div>
                <p class="text-sm opacity-80 mt-1">Rendimiento y estética premium.</p>
              </div>
            </div>
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s2/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Racing Parts</div>
                <p class="text-sm opacity-80 mt-1">Componentes de competición.</p>
              </div>
            </div>
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s3/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Full Repuestos</div>
                <p class="text-sm opacity-80 mt-1">Todo en un solo lugar.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 sm:hidden">
          <Link href="/stores" class="inline-flex px-5 py-2 rounded-xl border border-white/15 hover:bg-white/5">Ver todas</Link>
        </div>
      </div>
    </section>

    <!-- CONTACTO / CTA -->
    <section id="contacto" class="py-20 bg-black">
      <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-[1.2fr_.8fr] gap-10 items-center">
        <div>
          <h3 class="text-2xl md:text-3xl font-bold">¿Listo para empezar?</h3>
          <p class="mt-2 opacity-80 max-w-xl">Crea tu cuenta, configura tu marca y comparte tu catálogo hoy mismo.</p>
          <div class="mt-6 flex flex-wrap gap-3">
            <Link href="/register" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/20">Crear tienda</Link>
            <a href="#inicio" class="px-6 py-3 rounded-xl border border-white/15 hover:bg-white/5">Volver arriba</a>
          </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
          <div class="h-56 rounded-xl bg-[url('https://picsum.photos/seed/dashboard/1200/800')] bg-cover bg-center"></div>
          <div class="mt-4 text-sm opacity-80">Panel simple y poderoso para administrar todo.</div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-white/10 bg-black">
      <div class="max-w-7xl mx-auto px-6 py-10 text-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="opacity-80">© {{ new Date().getFullYear() }} ComercioPlus. Todos los derechos reservados.</div>
        <div class="flex items-center gap-6">
          <a href="#" class="hover:text-orange-400">Términos</a>
          <a href="#" class="hover:text-orange-400">Privacidad</a>
          <a href="#" class="hover:text-orange-400">Contacto</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
html { scroll-behavior: smooth; }

/* Animaciones suaves */
@keyframes float { 0%{transform:translateY(0)} 50%{transform:translateY(-12px)} 100%{transform:translateY(0)} }
@keyframes floatDelayed { 0%{transform:translateY(0)} 50%{transform:translateY(10px)} 100%{transform:translateY(0)} }
@keyframes rise { 0%{transform:translateY(12px); opacity:0} 100%{transform:translateY(0); opacity:1} }

.animate-float { animation: float 7s ease-in-out infinite; }
.animate-float-delayed { animation: floatDelayed 9s ease-in-out infinite; }
.animate-rise { animation: rise .6s ease-out both; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
