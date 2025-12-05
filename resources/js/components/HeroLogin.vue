<template>
  <section class="min-h-screen grid lg:grid-cols-[420px_1fr]">
    <!-- Panel izquierdo (login) -->
    <aside class="bg-[#F3E9E2]/70 backdrop-blur p-8 lg:p-10 flex flex-col justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-wide">COMERCIOPLUS</h1>

        <div class="mt-10 mx-auto w-28 h-28 rounded-full bg-white/70 border border-black/5 grid place-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="8" r="4" />
            <path d="M20 20a8 8 0 1 0-16 0" />
          </svg>
        </div>

        <form class="mt-8 space-y-3" @submit.prevent="submit">
          <label class="block">
            <span class="sr-only">Usuario o correo</span>
            <input v-model="form.email" type="email" placeholder="Usuario o correo"
                   class="w-full rounded-xl border border-black/10 bg-white/70 px-4 py-3 outline-none focus:ring-2 focus:ring-orange-400" />
          </label>

          <label class="block">
            <span class="sr-only">Contraseña</span>
            <input v-model="form.password" type="password" placeholder="Contraseña"
                   class="w-full rounded-xl border border-black/10 bg-white/70 px-4 py-3 outline-none focus:ring-2 focus:ring-orange-400" />
          </label>

          <button type="submit"
                  class="w-full mt-2 rounded-xl px-4 py-3 font-medium text-white bg-orange-500 hover:bg-orange-600 transition">
            Iniciar sesión
          </button>

          <div class="flex items-center justify-between text-sm text-stone-700 mt-2">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" v-model="form.remember" class="rounded border-stone-300" />
              Recordarme
            </label>
            <a href="#" class="underline hover:no-underline">¿Olvidaste tu contraseña?</a>
          </div>
        </form>
      </div>

      <div class="mt-10 flex items-center gap-2 justify-center">
        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
        <span class="w-2 h-2 rounded-full bg-stone-300"></span>
        <span class="w-2 h-2 rounded-full bg-stone-300"></span>
      </div>
    </aside>

    <!-- Panel derecho (hero) -->
    <main class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-[#F07A3C]/20 via-[#6B3A7C]/30 to-[#1C1230]"></div>
      <div class="absolute -inset-40 opacity-60"
           style="background: radial-gradient(60rem 30rem at 40% 40%, rgba(240,122,60,.35), transparent 60%),
                  radial-gradient(40rem 20rem at 70% 60%, rgba(255,160,90,.25), transparent 60%);">
      </div>

      <div class="relative z-10 h-full flex flex-col justify-center px-6 lg:px-16 py-16 text-white">
        <nav class="flex items-center justify-end gap-8 text-sm/none opacity-90">
          <a href="#" class="hover:opacity-100">Funciones</a>
          <a href="#" class="hover:opacity-100">Precios</a>
          <a href="#" class="hover:opacity-100">Demo</a>
          <a href="#" class="ml-6 inline-flex items-center rounded-full px-4 py-2 bg-white/10 border border-white/20 hover:bg-white/20">
            Crear cuenta
          </a>
        </nav>

        <div class="mt-16 lg:mt-24 max-w-3xl">
          <h2 class="text-4xl lg:text-5xl font-semibold tracking-tight">
            Empieza gratis y comparte tu catálogo hoy
          </h2>
          <p class="mt-4 text-white/90 max-w-xl">
            Configura tu tienda, sube tu logo y elige tus colores en minutos.
          </p>
          <div class="mt-8 flex flex-wrap gap-3">
            <a href="/stores/create"
               class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white px-5 py-3 transition">Crear tienda</a>
            <a href="/demo"
               class="rounded-xl border border-white/30 hover:bg-white/10 px-5 py-3 transition">Ver demo</a>
            <button @click="toggleTheme"
               class="rounded-xl border border-white/30 hover:bg-white/10 px-5 py-3 transition">
               {{ dark ? 'Modo claro' : 'Modo oscuro' }}
            </button>
          </div>
        </div>
      </div>
    </main>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'

const form = reactive({ email: '', password: '', remember: false })
const dark = ref(false)

const submit = () => {
  // Aquí puedes integrar Axios hacia tu endpoint de login de Laravel
  // axios.post('/login', {...form})
  alert('Demo: submit login')
}

const toggleTheme = () => {
  dark.value = !dark.value
  document.documentElement.classList.toggle('dark', dark.value)
}

onMounted(() => {
  // Si quieres arrancar respetando preferencia del SO:
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  if (prefersDark) toggleTheme()
})
</script>

<style scoped>
/* evita FOUC de {{}} si algún día renderizas texto reactivo dentro de Blade */
[v-cloak] { display: none; }
</style>
