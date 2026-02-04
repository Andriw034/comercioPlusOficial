<template>
  <component :is="layoutComponent">
    <router-view />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import PublicLayout from './layouts/PublicLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'
import DashboardLayout from './layouts/DashboardLayout.vue'

const route = useRoute()
const layouts = {
  public: PublicLayout,
  auth: AuthLayout,
  dashboard: DashboardLayout
}

const layoutComponent = computed(() => layouts[route.meta.layout] || PublicLayout)
</script>
