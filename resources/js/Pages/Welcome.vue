<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="bg-cp-bg text-cp-text">
    <!-- Navbar -->
    <header class="bg-cp-surface/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-200">
      <nav class="container mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="h-8 w-8 rounded-full bg-comercioplus shadow-[0_0_32px_rgba(255,106,0,.4)]"></span>
          <span class="font-semibold tracking-wide">Comercio<span class="text-comercioplus">Plus</span></span>
        </div>
        <div class="hidden md:flex items-center gap-8 text-sm">
            <!-- Navigation links can be added here -->
        </div>
        <div class="flex items-center gap-3">
          <Link href="/login" class="px-4 py-2 rounded-lg-16 border border-gray-200 hover:bg-gray-50 text-sm font-medium">Entrar</Link>
          <Link href="/register" class="px-4 py-2 rounded-lg-16 bg-comercioplus text-white hover:bg-comercioplus-600 text-sm font-medium shadow-lg shadow-comercioplus/20">Crear cuenta</Link>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <section class="bg-cp-bg">
      <div class="container mx-auto px-6 py-20 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Card central -->
          <div class="bg-cp-surface rounded-xxl-24 shadow-cp-card p-10">
            <h1 class="text-4xl lg:text-5xl font-semibold text-cp-text leading-tight">
              Tu negocio empieza con ComercioPlus
            </h1>
            <p class="mt-4 text-lg text-cp-sub">
              Prueba gratis durante 14 días — crea tu tienda, añade productos y comienza a vender.
            </p>
            <form class="mt-6 flex flex-col sm:flex-row gap-3">
              <input
                aria-label="email"
                type="email"
                placeholder="Tu correo electrónico"
                class="flex-1 h-14 rounded-lg border border-gray-200 px-4 focus:outline-none focus:ring-2 focus:ring-comercioplus"
              />
              <button class="h-14 px-6 rounded-lg bg-comercioplus text-white font-medium">
                Iniciar prueba
              </button>
            </form>
            <div class="mt-4 flex items-center gap-4 text-sm text-cp-sub">
              <span>Ejemplos: </span>
              <div class="flex gap-3">
                <!-- Placeholder images -->
                <div class="h-6 w-16 bg-gray-200 rounded"></div>
                <div class="h-6 w-16 bg-gray-200 rounded"></div>
                <div class="h-6 w-16 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
          <!-- Collage -->
          <div class="relative">
            <img src="/images/hero-moto.jpg" alt="Collage" class="rounded-xxl-24 shadow-cp-card w-full object-cover">
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-cp-surface border-t border-gray-200">
      <div class="container mx-auto px-6 py-8 text-center text-sm text-cp-sub">
        © {{ new Date().getFullYear() }} ComercioPlus. Todos los derechos reservados.
      </div>
    </footer>
  </div>
</template>
