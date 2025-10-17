<template>
  <div :class="contentClass" v-bind="$attrs">
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

const contentClass = computed(() => {
  return `p-6 pt-0 ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
