<template>
  <main>
    <Hero />
    <section class="bg-white text-cp-text">
      <div class="mx-auto max-w-7xl px-4 py-16 grid gap-8 md:grid-cols-2">
        <LoginForm @submit="noop" />
        <RegisterForm @submit="noop" />
      </div>
    </section>
  </main>
</template>
<script setup>
import Hero from '../components/Hero.vue'
import LoginForm from '../components/LoginForm.vue'
import RegisterForm from '../components/RegisterForm.vue'
function noop(){} // solo UI de la home
</script>
