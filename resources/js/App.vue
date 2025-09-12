<template>
  <div class="min-h-screen flex flex-col">
    <Header @toggle-theme="toggleTheme" :mode="mode" />
    <main class="flex-1">
      <slot />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
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
</script>

