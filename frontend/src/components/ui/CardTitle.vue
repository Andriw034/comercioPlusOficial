<template>
  <h3 :class="titleClass" v-bind="$attrs">
    <slot />
  </h3>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
})

const titleClass = computed(() => {
  return `text-2xl font-semibold leading-none tracking-tight ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
