<template>
  <header class="sticky top-0 z-40 bg-white/70 dark:bg-neutral-900/60 backdrop-blur border-b border-neutral-200 dark:border-neutral-800">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      <RouterLink to="/" class="flex items-center gap-2">
        <div class="w-9 h-9 rounded-xl bg-primary/10 grid place-items-center">
          <span class="text-primary font-bold">CP</span>
        </div>
        <span class="font-bold">ComercioPlus</span>
      </RouterLink>
      <nav class="hidden md:flex items-center gap-6 text-sm text-neutral-700 dark:text-neutral-300">
        <RouterLink to="/stores" class="hover:text-neutral-900 dark:hover:text-white transition">Tiendas</RouterLink>
        <RouterLink to="/products" class="hover:text-neutral-900 dark:hover:text-white transition">Productos</RouterLink>
        <RouterLink to="/orders" class="hover:text-neutral-900 dark:hover:text-white transition">Órdenes</RouterLink>
      </nav>
      <div class="flex items-center gap-2">
        <button @click="$emit('toggle-theme')" class="px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition" aria-label="Cambiar tema">🌓</button>
        <RouterLink to="/login" class="px-4 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">Entrar</RouterLink>
        <RouterLink to="/register" class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary-600 transition">Crear cuenta</RouterLink>
      </div>
    </div>
  </header>
</template>

