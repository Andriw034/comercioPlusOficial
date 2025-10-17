<template>
  <div :class="cardClass" v-bind="$attrs">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
})

const cardClass = computed(() => {
  return `rounded-lg border bg-card text-card-foreground shadow-sm ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
