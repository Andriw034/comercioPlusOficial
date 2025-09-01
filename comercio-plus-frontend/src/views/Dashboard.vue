<template>
  <main class="min-h-screen bg-white text-cp-text">
    <div class="mx-auto max-w-7xl px-4 py-10">
      <h1 class="text-3xl font-extrabold">Panel de control</h1>
      <p class="text-slate-600 mt-1">Configura tu catálogo, productos y ajustes.</p>

      <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <RouterLink to="/dashboard/products" class="rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
          <h3 class="font-semibold">Productos</h3>
          <p class="text-sm text-slate-600 mt-1">Crea, edita y organiza tu inventario.</p>
        </RouterLink>
        
        <RouterLink to="/dashboard/categories" class="rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
          <h3 class="font-semibold">Categorías</h3>
          <p class="text-sm text-slate-600 mt-1">Organiza tus productos.</p>
        </RouterLink>
        
        <RouterLink to="/dashboard/settings" class="rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
          <h3 class="font-semibold">Configuración</h3>
          <p class="text-sm text-slate-600 mt-1">Logo, portada e información de tu tienda.</p>
        </RouterLink>
      </div>

      <!-- Sección de identidad visual de la tienda -->
      <div class="mt-10 rounded-2xl border border-slate-200 p-6">
        <h2 class="text-xl font-bold">Identidad visual de la tienda</h2>
        <p class="text-slate-600 text-sm">Aquí se mostrará el logo y portada de tu tienda una vez guardados.</p>
        
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="bg-slate-100 rounded-xl p-4 text-center">
            <div class="w-16 h-16 mx-auto bg-slate-200 rounded-xl flex items-center justify-center">
              <span class="text-slate-400 text-sm">Logo</span>
            </div>
            <p class="text-sm text-slate-600 mt-2">Logo de la tienda</p>
          </div>
          
          <div class="bg-slate-100 rounded-xl p-4 text-center">
            <div class="w-full h-16 bg-slate-200 rounded-xl flex items-center justify-center">
              <span class="text-slate-400 text-sm">Portada</span>
            </div>
            <p class="text-sm text-slate-600 mt-2">Imagen de portada</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
