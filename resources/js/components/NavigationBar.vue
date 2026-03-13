<template>
  <nav class="bg-jbl-dark shadow-lg border-b border-jbl-gray/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <router-link to="/" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-jbl-orange rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            </div>
            <span class="text-xl font-bold text-white font-jbl">ComercioPlus</span>
          </router-link>
          <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
            <router-link
              to="/dashboard"
              class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200"
              :class="$route.path === '/dashboard' ? 'border-jbl-orange text-jbl-orange' : 'border-transparent text-jbl-light-gray hover:border-jbl-orange hover:text-jbl-orange'"
            >
              Panel
            </router-link>
            <router-link
              to="/stores"
              class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200"
              :class="$route.path.startsWith('/stores') ? 'border-jbl-orange text-jbl-orange' : 'border-transparent text-jbl-light-gray hover:border-jbl-orange hover:text-jbl-orange'"
            >
              Tiendas
            </router-link>
          </div>
        </div>
        <div class="flex items-center space-x-6">
          <router-link to="/login" class="text-jbl-light-gray hover:text-white transition-colors duration-200 font-medium">Entrar</router-link>
          <router-link
            to="/register"
            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-semibold rounded-full shadow-lg text-white bg-jbl-orange hover:bg-orange-600 transform hover:scale-105 transition-all duration-200"
          >
            Crear cuenta
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
// No script logic needed for static navigation links
</script>

<style scoped>
/* Puedes agregar estilos adicionales aquí si es necesario */
</style>
