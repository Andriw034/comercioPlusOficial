<template>
  <header class="sticky top-0 z-40 backdrop-blur bg-white/90 border-b border-[var(--cp-border)]">
    <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="h-16 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-2xl" :style="logoBg"></div>
          <span class="text-lg font-extrabold">Comercio<span :style="{color:'var(--cp-primary)'}">Plus</span></span>
        </router-link>

        <div class="hidden md:flex items-center gap-6 text-sm">
          <router-link to="/" class="hover:text-[var(--cp-primary)]">Inicio</router-link>
          <router-link v-if="authed" to="/products" class="hover:text-[var(--cp-primary)]">Productos</router-link>
          <router-link v-if="authed" to="/settings/theme" class="hover:text-[var(--cp-primary)]">Tema</router-link>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <router-link v-if="!authed" to="/login" class="btn btn-secondary">Entrar</router-link>
          <router-link v-if="!authed" to="/register" class="btn btn-primary">Crear cuenta</router-link>
          <button v-if="authed" @click="logout" class="btn btn-secondary">Cerrar sesión</button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import api from '../lib/api'
import { session } from '../stores/session'
const authed = !!session.user
const logoBg = { background:'linear-gradient(135deg,var(--cp-primary),var(--cp-primary-2))' }
async function logout(){
  try{ await api.post('/logout'); }catch{}
  localStorage.removeItem('token')
  location.href = '/login'
}
</script>
