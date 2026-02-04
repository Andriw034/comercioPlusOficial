<template>
  <div class="min-h-screen p-8 text-white">
    <p>Redirigiendo al gestor de tienda...</p>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const router = useRouter()
onMounted(() => router.replace({ name: 'dashboard-store' }))
</script>
