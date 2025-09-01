
<template>
  <button
    @click="goToProducts"
    :disabled="loading"
    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 shadow font-semibold transition"
  >
    <span v-if="loading">Redirigiendo...</span>
    <span v-else>Ir a productos</span>
  </button>
</template>

<script>
export default {
  data() {
    return { loading: false }
  },
  methods: {
    goToProducts() {
      this.loading = true;
      window.location.href = '/dashboard/product';
    }
  }
}
</script>
