<template>
  <div class="min-h-screen bg-mesh text-slate-50 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -left-24 top-10 w-72 h-72 bg-brand-500/20 blur-3xl rounded-full" />
      <div class="absolute right-0 top-32 w-80 h-80 bg-cyan-400/10 blur-3xl rounded-full" />
      <div class="absolute -right-10 bottom-10 w-64 h-64 bg-purple-500/10 blur-3xl rounded-full" />
    </div>

    <header class="sticky top-0 z-20 px-4 pt-4">
      <nav class="max-w-7xl mx-auto flex items-center justify-between gap-6 bg-white/5 border border-white/10 backdrop-blur-xl rounded-full px-5 py-3 shadow-soft">
        <router-link to="/" class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold shadow-soft">CP</span>
          <div class="leading-tight">
            <p class="font-semibold text-white">ComercioPlus</p>
            <p class="text-xs text-muted">Repuestos y tiendas confiables</p>
          </div>
        </router-link>

        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-200">
          <router-link class="hover:text-white" to="/">Inicio</router-link>
          <router-link class="hover:text-white" to="/products">Productos</router-link>
          <router-link class="hover:text-white" to="/stores">Tiendas</router-link>
          <router-link class="hover:text-white" to="/how-it-works">C&oacute;mo funciona</router-link>
        </div>

        <div class="flex items-center gap-3">
          <router-link v-if="isLogged" to="/dashboard" class="btn-ghost">Panel</router-link>
          <router-link v-else to="/login" class="btn-ghost">Entrar</router-link>
          <button
            v-if="isLogged"
            @click="logout"
            class="btn-primary"
          >
            Cerrar sesión
          </button>
          <router-link v-else to="/register" class="btn-primary">Vender en ComercioPlus</router-link>
        </div>
      </nav>
    </header>

    <main class="relative z-10 pt-10 pb-16">
      <slot />
    </main>

    <footer class="relative z-10 border-t border-white/10 bg-white/5 backdrop-blur-xl">
      <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-muted">
        <div class="flex items-center gap-2 text-slate-200">
          <span class="font-semibold text-white">ComercioPlus</span>
          <span class="text-muted">| Plataforma de repuestos y tiendas</span>
        </div>
        <div class="flex items-center gap-4">
          <router-link to="/products" class="hover:text-white">Productos</router-link>
          <router-link to="/stores" class="hover:text-white">Tiendas</router-link>
          <router-link to="/register" class="hover:text-white">Ser comerciante</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import API from '../services/api.js'

const router = useRouter()
const isLogged = computed(() => !!localStorage.getItem('token'))

const logout = async () => {
  try {
    await API.post('/logout')
  } catch (error) {
    console.warn('Logout error', error)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}
</script>
