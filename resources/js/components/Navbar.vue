<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-xl border-b border-white/10 shadow-2xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo Section -->
        <RouterLink class="flex items-center space-x-3 group" to="/">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg">
            <span class="text-white font-bold text-lg">C</span>
          </div>
          <div class="flex flex-col">
            <span class="text-xl font-bold text-white group-hover:text-orange-400 transition-colors duration-300">ComercioPlus</span>
            <span class="text-xs text-gray-400">Repuestos & Accesorios</span>
          </div>
        </RouterLink>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-8">
          <RouterLink
            to="/products"
            class="flex items-center space-x-2 text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="font-medium">Productos</span>
          </RouterLink>

          <RouterLink
            to="/stores"
            class="flex items-center space-x-2 text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="font-medium">Tiendas</span>
          </RouterLink>

          <RouterLink
            to="/cart"
            class="flex items-center space-x-2 text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5 relative"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
            </svg>
            <span class="font-medium">Carrito</span>
            <span v-if="cartItemCount > 0" class="absolute -top-2 -right-2 bg-gradient-to-r from-orange-500 to-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
              {{ cartItemCount }}
            </span>
          </RouterLink>
        </div>

        <!-- User Actions -->
        <div class="flex items-center space-x-4">
          <!-- Theme Toggle -->
          <button
            @click="toggleTheme"
            class="hidden md:flex items-center space-x-2 text-gray-300 hover:text-orange-400 px-3 py-2 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5"
          >
            <svg v-if="dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <span class="text-sm font-medium">{{ dark ? 'Modo Claro' : 'Modo Oscuro' }}</span>
          </button>

          <!-- Auth Buttons -->
          <div class="flex items-center space-x-3">
            <RouterLink
              to="/login"
              class="text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-all duration-300 border border-white/10 hover:border-orange-400/30 hover:bg-white/5 font-medium"
            >
              Entrar
            </RouterLink>

            <RouterLink
              to="/register"
              class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-6 py-2 rounded-xl font-bold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-orange-500/25"
            >
              Crear cuenta
            </RouterLink>
          </div>

          <!-- Mobile Menu Button -->
          <button
            @click="toggleMobileMenu"
            class="md:hidden flex items-center justify-center w-10 h-10 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 text-white hover:bg-white/20 transition-all duration-300"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-if="mobileMenuOpen"
        class="md:hidden bg-black/95 backdrop-blur-xl border-t border-white/10 shadow-2xl"
        style="animation: slideDown 0.3s ease-out;"
      >
        <div class="px-4 py-6 space-y-4">
          <RouterLink
            to="/products"
            @click="closeMobileMenu"
            class="flex items-center space-x-3 text-gray-300 hover:text-orange-400 px-4 py-3 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="font-medium">Productos</span>
          </RouterLink>

          <RouterLink
            to="/stores"
            @click="closeMobileMenu"
            class="flex items-center space-x-3 text-gray-300 hover:text-orange-400 px-4 py-3 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="font-medium">Tiendas</span>
          </RouterLink>

          <RouterLink
            to="/cart"
            @click="closeMobileMenu"
            class="flex items-center space-x-3 text-gray-300 hover:text-orange-400 px-4 py-3 rounded-lg transition-all duration-300 border border-transparent hover:border-orange-400/30 hover:bg-white/5 relative"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
            </svg>
            <span class="font-medium">Carrito</span>
            <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-gradient-to-r from-orange-500 to-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
              {{ cartItemCount }}
            </span>
          </RouterLink>

          <div class="border-t border-white/10 pt-4 mt-6">
            <div class="flex flex-col space-y-3">
              <RouterLink
                to="/login"
                @click="closeMobileMenu"
                class="text-gray-300 hover:text-orange-400 px-4 py-3 rounded-lg transition-all duration-300 border border-white/10 hover:border-orange-400/30 hover:bg-white/5 font-medium text-center"
              >
                Entrar
              </RouterLink>

              <RouterLink
                to="/register"
                @click="closeMobileMenu"
                class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-4 py-3 rounded-xl font-bold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-orange-500/25 text-center"
              >
                Crear cuenta
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const dark = ref(false)
const mobileMenuOpen = ref(false)

// Mock cart item count - in a real app, this would come from a cart store
const cartItemCount = computed(() => {
  // This would be replaced with actual cart logic
  return 0
})

const toggleTheme = () => {
  dark.value = !dark.value
  document.documentElement.classList.toggle('dark', dark.value)
  localStorage.setItem('theme', dark.value ? 'dark' : 'light')
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark') {
    dark.value = true
    document.documentElement.classList.add('dark')
  }
})
</script>

<style scoped>
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
