<template>
  <div class="min-h-screen bg-[var(--cp-bg)] text-[var(--cp-ink)]">
    <AppNavbar />
    <router-view />
    <AppFooter />
  </div>
</template>

<script setup>
import AppNavbar from './components/AppNavbar.vue'
import AppFooter from './components/AppFooter.vue'
</script>
