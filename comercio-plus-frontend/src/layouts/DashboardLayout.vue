<template>
  <div class="min-h-screen bg-ink text-slate-50 relative">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -left-10 top-10 w-72 h-72 bg-brand-500/15 blur-3xl rounded-full" />
      <div class="absolute right-0 top-32 w-64 h-64 bg-cyan-400/10 blur-3xl rounded-full" />
    </div>

    <header class="sticky top-0 z-20 border-b border-white/5 bg-panel/80 backdrop-blur-xl">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <router-link to="/" class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold">CP</span>
          <div class="leading-tight">
            <p class="font-semibold text-white">ComercioPlus</p>
            <p class="text-xs text-muted">Panel del comerciante</p>
          </div>
        </router-link>
        <div class="flex items-center gap-3 text-sm">
          <router-link to="/stores" class="btn-ghost hidden sm:inline-flex">Ver tiendas</router-link>
          <router-link to="/products" class="btn-ghost hidden sm:inline-flex">Productos</router-link>
          <slot name="actions" />
        </div>
      </div>
    </header>

    <main class="relative z-10 max-w-7xl mx-auto px-4 py-10">
      <slot />
    </main>
  </div>
</template>
