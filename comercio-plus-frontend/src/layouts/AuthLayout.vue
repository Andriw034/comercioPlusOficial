<template>
  <div class="min-h-screen bg-mesh text-slate-50 relative flex items-center justify-center px-4 py-16 overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute left-10 top-16 w-64 h-64 bg-brand-500/15 blur-3xl rounded-full" />
      <div class="absolute right-8 bottom-10 w-72 h-72 bg-cyan-400/12 blur-3xl rounded-full" />
    </div>

    <div class="relative w-full max-w-lg glass rounded-3xl p-8 shadow-card">
      <div class="mb-6 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold shadow-soft">CP</span>
          <div class="leading-tight">
            <p class="font-semibold text-white">ComercioPlus</p>
            <p class="text-xs text-muted">Regresa a comprar o vender</p>
          </div>
        </router-link>
        <router-link to="/" class="text-sm text-slate-200 hover:text-white">Volver al inicio</router-link>
      </div>
      <slot />
    </div>
  </div>
</template>
