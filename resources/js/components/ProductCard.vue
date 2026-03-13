<template>
  <article class="group rounded-2xl border border-border/60 bg-white dark:bg-neutral-900 shadow-sm hover:shadow-md transition p-3">
    <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-800">
      <img v-if="image" :src="image" :alt="name" class="w-full h-full object-cover" />
      <div v-else class="w-full h-full grid place-items-center text-neutral-500">Sin imagen</div>

      <div class="absolute top-3 left-3 flex gap-2">
        <span v-if="outOfStock" class="px-2 py-0.5 text-xs rounded-full bg-neutral-900/80 text-white">Sin stock</span>
        <span v-if="discount" class="px-2 py-0.5 text-xs rounded-full bg-primary text-white">-{{ discount }}%</span>
      </div>

      <label class="absolute top-3 right-3 inline-flex items-center gap-2">
        <input type="checkbox" class="size-4 accent-primary rounded">
      </label>
    </div>

    <div class="mt-3 space-y-1">
      <h3 class="font-medium text-neutral-900 dark:text-neutral-100 line-clamp-2">{{ name }}</h3>
      <div class="text-sm text-neutral-600 dark:text-neutral-400 flex items-center gap-2">
        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ formattedPrice }}</span>
        <span>·</span>
        <span>★ {{ formattedRating }}</span>
        <span>·</span>
        <span>{{ stock }} u</span>
      </div>
      <div class="flex items-center gap-2 pt-2">
        <button class="px-3 py-1.5 rounded-lg bg-primary text-white hover:opacity-90">Editar</button>
        <button class="px-3 py-1.5 rounded-lg border border-border/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">Ver</button>
        <button class="ml-auto px-2 py-1 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800">⋮</button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  name: String,
  price: Number,
  stock: Number,
  image: String,
  rating: Number,
  discount: Number,
  outOfStock: Boolean,
})

const formattedPrice = computed(() =>
  props.price != null ? `$${props.price.toLocaleString('es-CO')}` : '—'
)
const formattedRating = computed(() =>
  typeof props.rating === 'number' ? props.rating.toFixed(1) : '—'
)
</script>
