<template>
  <div :class="headerClass" v-bind="$attrs">
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

const headerClass = computed(() => {
  return `flex flex-col space-y-1.5 p-6 ${props.class}`
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
