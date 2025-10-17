<template>
  <p :class="descriptionClass" v-bind="$attrs">
    <slot />
  </p>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
})

const descriptionClass = computed(() => {
  return `text-sm text-muted-foreground ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
