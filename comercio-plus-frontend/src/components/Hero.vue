<template>
  <section class="bg-cp-bg text-white">
    <div class="mx-auto max-w-7xl px-6 py-16 grid gap-6 md:grid-cols-2 items-center">
      <div>
        <h1 class="text-4xl font-extrabold leading-tight">Impulsa tu tienda con ComercioPlus</h1>
        <p class="text-slate-300 mt-3">Crea tu catálogo, gestiona productos y comparte tu identidad visual.</p>
        <div class="mt-6 flex gap-4">
          <RouterLink to="/register" class="px-6 py-3 rounded-xl bg-cp-brand hover:bg-cp-brand-2 text-white font-semibold shadow-soft">Crear mi cuenta</RouterLink>
          <RouterLink to="/login" class="px-6 py-3 rounded-xl border border-white/20 text-white/90 hover:bg-white/10">Entrar</RouterLink>
        </div>
      </div>
      <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-soft">
        <p class="text-slate-200">Bienvenido. Regístrate como comerciante para crear tu tienda.</p>
      </div>
    </div>
  </section>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
