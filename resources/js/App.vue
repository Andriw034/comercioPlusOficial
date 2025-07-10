<template>
  <div class="min-h-screen flex flex-col">
    <Header @toggle-theme="toggleTheme" :mode="mode" />
    <main class="flex-1">
      <RouterView />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterView } from 'vue-router'
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'

const mode = ref('light')
const apply = (m) => {
  const html = document.documentElement
  if (m === 'dark') html.classList.add('dark')
  else html.classList.remove('dark')
  localStorage.setItem('theme', m)
  mode.value = m
}
const toggleTheme = () => apply(mode.value === 'dark' ? 'light' : 'dark')

onMounted(() => {
  const stored = localStorage.getItem('theme')
  apply(stored === 'dark' ? 'dark' : 'light')
})


createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
})

</script>

