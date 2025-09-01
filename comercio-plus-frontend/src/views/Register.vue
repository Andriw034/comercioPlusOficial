<template>
  <main class="min-h-screen bg-cp-bg text-white flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <RouterLink to="/" class="block text-center mb-8 font-extrabold text-2xl">ComercioPlus</RouterLink>
      <RegisterForm 
        :loading="loading" 
        :error="error" 
        @submit="onRegister" 
      />
      <p class="text-center text-sm text-slate-300 mt-6">
        ¿Ya tienes cuenta? 
        <RouterLink to="/login" class="text-cp-brand hover:text-cp-brand-2 font-medium">Entrar</RouterLink>
      </p>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import RegisterForm from '../components/RegisterForm.vue'
import { apiRegister } from '../lib/api'
import { saveAuth, primaryRole } from '../lib/auth'

const router = useRouter()
const loading = ref(false)
const error = ref('')

async function onRegister (payload) {
  loading.value = true
  error.value = ''
  try {
    const { token, user } = await apiRegister(payload)
    saveAuth(token, user)
    
    const role = primaryRole(user)
    if (role === 'comerciante') {
      router.push({ name: 'onboarding-store' })
    } else {
      router.push({ name: 'dashboard' })
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al registrarse'
  } finally {
    loading.value = false
  }
}
</script>
