<template>
  <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="space-y-5">
      <h1 class="text-3xl font-extrabold">Crea tu cuenta</h1>
      <form @submit.prevent="register" class="space-y-4">
        <div><label class="text-sm">Nombre</label><input v-model="name" class="input" required></div>
        <div><label class="text-sm">Correo</label><input v-model="email" type="email" class="input" required></div>
        <div><label class="text-sm">Contraseña</label><input v-model="password" type="password" class="input" required></div>
        <div><label class="text-sm">Confirmar contraseña</label><input v-model="password_confirmation" type="password" class="input" required></div>
        <div>
          <label class="text-sm">Rol</label>
          <select v-model="role" class="input">
            <option value="comerciante">Comerciante (admin)</option>
            <option value="cliente">Cliente</option>
          </select>
        </div>
        <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Creando...' : 'Crear cuenta' }}</button>
        <p class="text-sm text-center">¿Ya tienes cuenta? <router-link to="/login" class="hover:text-[var(--cp-primary)]">Inicia sesión</router-link></p>
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      </form>
    </div>
    <div class="rounded-3xl min-h-80" :style="hero"></div>
  </div>
</template>

<script setup>
import api from '../lib/api'
import { fetchMe } from '../stores/session'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const name = ref(''); const email = ref(''); const password = ref(''); const password_confirmation = ref('')
const role = ref('comerciante'); const loading = ref(false); const error = ref('')
const hero = { background:'linear-gradient(135deg,var(--cp-primary),var(--cp-primary-2))' }
const router = useRouter()

async function register(){
  loading.value = true; error.value = ''
  try{
    await api.post('/api/register', { name: name.value, email: email.value, password: password.value, password_confirmation: password_confirmation.value, role: role.value })
    // opcional: backend puede devolver token
    await fetchMe()
    router.push({ name: 'store.create' })
  }catch(e){
    error.value = e?.response?.data?.message || 'No se pudo registrar'
  }finally{ loading.value = false }
}
</script>
