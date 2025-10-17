<template>
  <div :class="themeClass">
    <slot />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const theme = ref('light')
const themeClass = ref('')

const setTheme = (newTheme) => {
  theme.value = newTheme
  themeClass.value = newTheme === 'dark' ? 'dark' : ''
  localStorage.setItem('theme', newTheme)
  document.documentElement.classList.toggle('dark', newTheme === 'dark')
}

const toggleTheme = () => {
  setTheme(theme.value === 'light' ? 'dark' : 'light')
}

const getSystemTheme = () => {
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

onMounted(() => {
  // Cargar tema desde localStorage o usar el del sistema
  const savedTheme = localStorage.getItem('theme')
  const initialTheme = savedTheme || getSystemTheme()
  setTheme(initialTheme)

  // Escuchar cambios en el tema del sistema
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
      setTheme(e.matches ? 'dark' : 'light')
    }
  })
})

// Exponer funciones para que otros componentes puedan usarlas
defineExpose({
  theme,
  toggleTheme,
  setTheme
})
</script>

<style scoped>
/* Estilos específicos para el ThemeProvider */
</style>
