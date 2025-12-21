<template>
  <div id="app">
    <h1>ComercioPlus - Frontend independiente</h1>
    <!-- quitar v-if -->
    <router-view></router-view>

    <!-- Si quieres un mensaje cuando no hay rutas registradas, hazlo en una ruta "fallback" -->
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
/* estilos básicos */
#app { padding: 20px; font-family: Inter, system-ui, Arial; }
</style>
