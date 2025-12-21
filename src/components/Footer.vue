<template>
  <footer class="border-t bg-card">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="py-8 text-sm text-muted-foreground flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <logo-component />
          <p>&copy; {{ currentYear }} ComercioPlus. Todos los derechos reservados.</p>
        </div>
        <div class="flex gap-4">
          <router-link to="/terms" class="hover:text-primary transition-colors">Términos</router-link>
          <router-link to="/privacy" class="hover:text-primary transition-colors">Privacidad</router-link>
          <router-link to="/contact" class="hover:text-primary transition-colors">Contacto</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'
import LogoComponent from './Logo.vue'

const currentYear = ref(new Date().getFullYear())
</script>

<style scoped>
/* Estilos específicos para el Footer */
</style>
