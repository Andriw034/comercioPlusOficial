<template>
  <div :class="footerClass" v-bind="$attrs">
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

const footerClass = computed(() => {
  return `flex items-center p-6 pt-0 ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
