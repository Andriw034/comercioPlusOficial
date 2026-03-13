<template>
  <div :class="['min-h-screen bg-neutral-50 text-neutral-900', { 'dark bg-[#0d0f12] text-neutral-100': dark }]">
    <aside class="fixed inset-y-0 left-0 w-72 bg-white/90 border-r border-border backdrop-blur dark:bg-[#0f1217]">
      <div class="px-5 py-4 flex items-center gap-3 border-b border-border/70">
        <div class="w-8 h-8 rounded-xl bg-primary/15 grid place-items-center">
          <span class="text-primary font-extrabold">CP</span>
        </div>
        <span class="font-bold">ComercioPlus</span>
      </div>
      <nav class="p-3 space-y-1 text-sm">
        <RouterLink class="navlink" to="/dashboard">Dashboard</RouterLink>
        <RouterLink class="navlink" to="/orders">Órdenes</RouterLink>
        <RouterLink class="navlink" to="/products">Productos</RouterLink>
        <RouterLink class="navlink" to="/categories">Categorías</RouterLink>
        <RouterLink class="navlink" to="/customers">Clientes</RouterLink>
        <RouterLink class="navlink" to="/discounts">Descuentos</RouterLink>
        <RouterLink class="navlink" to="/settings">Ajustes</RouterLink>
      </nav>
      <div class="absolute bottom-0 left-0 right-0 border-t border-border/70 p-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-neutral-200 overflow-hidden"></div>
          <span class="text-sm">Tú</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="px-2 py-1 rounded-lg border border-border" @click="toggleTheme">{{ dark ? '☀︎' : '🌙' }}</button>
          <a href="/logout" class="px-2 py-1 rounded-lg border border-border">Salir</a>
        </div>
      </div>
    </aside>

    <div class="pl-72">
      <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-border dark:bg-[#0f1217]">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
          <slot name="title"><h1 class="font-semibold">Panel</h1></slot>
          <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 rounded-xl border border-border hover:bg-neutral-100 dark:hover:bg-white/10"
                    @click="toggleTheme">{{ dark ? 'Claro' : 'Oscuro' }}</button>
            <div class="w-8 h-8 rounded-full bg-neutral-200 overflow-hidden"></div>
          </div>
        </div>
      </header>

      <main class="max-w-7xl mx-auto px-4 py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const dark = ref(false)
onMounted(() => {
  dark.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', dark.value)
})
function toggleTheme(){
  dark.value = !dark.value
  document.documentElement.classList.toggle('dark', dark.value)
  localStorage.setItem('theme', dark.value ? 'dark' : 'light')
}
</script>

<style scoped>
.navlink{ display:block; padding:10px 12px; border-radius:12px; }
.navlink.router-link-active{ background:rgba(255,122,61,.08); color:#FF7A3D; font-weight:600; }
</style>

