<div id="app">
  <dashboard-vue></dashboard-vue>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://unpkg.com/vue-router@4/dist/vue-router.global.js"></script>

<script type="module">
  import Dashboard from '/src/components/Dashboard.vue'

  const app = Vue.createApp({})
  app.component('dashboard-vue', Dashboard)
  app.mount('#app')
</script>
