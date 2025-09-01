<template>
  <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
      <RouterLink to="/" class="font-extrabold text-xl tracking-tight">ComercioPlus</RouterLink>
      <div class="hidden md:flex items-center gap-6">
        <RouterLink class="text-sm text-slate-700 hover:text-black" to="/">Inicio</RouterLink>
        <RouterLink class="text-sm text-slate-700 hover:text-black" to="/dashboard">Panel</RouterLink>
      </div>
      <div class="flex items-center gap-3">
        <RouterLink to="/register" class="px-4 py-2 rounded-xl bg-cp-brand hover:bg-cp-brand-2 text-white font-semibold shadow-soft">Crear cuenta</RouterLink>
        <RouterLink to="/login" class="hidden sm:inline text-sm text-slate-700 hover:text-black">Entrar</RouterLink>
      </div>
    </div>
  </nav>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
