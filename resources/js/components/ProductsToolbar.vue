<template>
  <div class="flex flex-wrap items-center gap-2 justify-between">
    <div class="flex items-center gap-2">
      <h1 class="text-2xl font-bold">Productos</h1>
      <select class="rounded-lg border border-border/70 bg-white dark:bg-neutral-900 px-2 py-1 text-sm">
        <option>Tienda principal</option>
        <option>Tienda B</option>
      </select>
    </div>

    <div class="flex flex-1 justify-center">
      <input
        type="search"
        placeholder="Buscar nombre, SKU, categoría…"
        class="w-full max-w-xl rounded-full border border-border/70 bg-white dark:bg-neutral-900 px-4 py-2"
        v-model="q"
      />
    </div>

    <div class="flex items-center gap-2">
      <button class="px-3 py-2 rounded-lg bg-primary text-white">+ Nuevo</button>
      <button class="px-3 py-2 rounded-lg border border-border/70">Importar</button>
      <button class="px-3 py-2 rounded-lg border border-border/70">Exportar</button>
      <div class="h-6 w-px bg-border/80"></div>
      <button class="px-2 py-2 rounded-lg border border-border/70">☰</button>
      <button class="px-2 py-2 rounded-lg border border-border/70">▥</button>
      <button class="px-2 py-2 rounded-lg border border-border/70">Densidad</button>
    </div>
  </div>

  <div class="mt-3 flex flex-wrap items-center gap-2">
    <button v-for="c in chips" :key="c"
      class="px-3 py-1.5 rounded-full border border-border/70 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm">
      {{ c }}
    </button>

    <div class="ml-auto flex items-center gap-2">
      <label class="text-sm">Ordenar:</label>
      <select class="rounded-lg border border-border/70 bg-white dark:bg-neutral-900 px-2 py-1 text-sm">
        <option>Más vendidos</option>
        <option>Precio ↑</option>
        <option>Precio ↓</option>
        <option>Actualizado ↓</option>
      </select>
      <button @click="$emit('open-filters')" class="px-3 py-1.5 rounded-lg border border-border/70">
        ⚙ Filtros
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const q = ref('')
const chips = ['Todos', 'Activos', 'Sin stock', 'Ocultos', 'En oferta']
</script>

