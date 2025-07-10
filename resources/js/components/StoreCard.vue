<template>
  <article class="bg-white dark:bg-neutral-900 rounded-xl shadow hover:shadow-lg transition overflow-hidden border border-neutral-200 dark:border-neutral-800 p-3 flex items-center gap-3">
    <div class="w-12 h-12 rounded-xl bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
      <img v-if="logo" :src="logo" :alt="`Logo ${name}`" class="w-full h-full object-cover" loading="lazy" />
      <div v-else class="w-full h-full grid place-items-center text-neutral-500">🏪</div>
    </div>
    <div class="flex-1 min-w-0">
      <h3 class="font-medium truncate">{{ name }}</h3>
      <p class="text-sm text-neutral-600 dark:text-neutral-300 truncate">{{ category }}</p>
    </div>
    <span v-if="rating" class="text-sm text-neutral-600 dark:text-neutral-300">★ {{ Number(rating).toFixed(1) }}</span>
  </article>
</template>
<script setup>
defineProps({ name: String, logo: String, rating: [String, Number], category: String })
</script>

