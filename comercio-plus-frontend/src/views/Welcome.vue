<template>
  <section class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    <div class="space-y-5">
      <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
        Empieza gratis y comparte tu <span :style="{color:'var(--cp-primary)'}">catálogo</span> hoy.
      </h1>
      <p class="text-[var(--cp-ink-2)]">Publica tus productos, gestiona pedidos y comparte tu tienda en minutos.</p>
      <div class="flex gap-3">
        <router-link to="/register" class="btn btn-primary">Crear cuenta</router-link>
        <router-link to="/login" class="btn btn-secondary">Iniciar sesión</router-link>
      </div>
    </div>
    <div class="rounded-3xl h-72 md:h-96" :style="hero"></div>
  </section>
</template>

<script setup>
const hero = { background:'linear-gradient(135deg,var(--cp-primary),var(--cp-primary-2))' }
</script>
