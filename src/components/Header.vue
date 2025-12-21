<template>
  <header class="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur-sm">
    <div class="container h-16 flex items-center justify-between">
      <div class="flex items-center gap-6">
        <router-link to="/" class="flex items-center gap-2">
          <Logo />
        </router-link>
      </div>

      <nav class="hidden md:flex items-center gap-4">
        <router-link to="/dashboard" class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Panel</router-link>
        <router-link to="/#stores" class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Tiendas</router-link>
      </nav>

      <div class="flex items-center">
        <AuthWidget />
      </div>
    </div>
  </header>
</template>

<script setup>
import Logo from './Logo.vue'
import AuthWidget from './AuthWidget.vue'
</script>

<style scoped>
/* Puedes agregar estilos específicos para el Header aquí */
</style>
