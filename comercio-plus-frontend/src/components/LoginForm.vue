<template>
  <form @submit.prevent="onSubmit" class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-soft">
    <h2 class="text-2xl font-bold text-white">Iniciar sesión</h2>
    <p class="text-sm text-slate-300 mt-1">Accede a tu cuenta</p>

    <div class="mt-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-200">Correo electrónico</label>
        <input v-model="email" type="email" required class="mt-1 w-full rounded-xl border border-white/15 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="ejemplo@mail.com" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-200">Contraseña</label>
        <input v-model="password" type="password" required class="mt-1 w-full rounded-xl border border-white/15 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="••••••••" />
      </div>
      <button :disabled="loading" type="submit" class="w-full rounded-xl bg-cp-brand hover:bg-cp-brand-2 text-white font-semibold py-2">
        {{ loading ? 'Entrando…' : 'Entrar' }}
      </button>
      <p v-if="error" class="text-sm text-red-300 mt-2">{{ error }}</p>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
})
const emit = defineEmits(['submit'])

const email = ref('')
const password = ref('')

function onSubmit () {
  emit('submit', { email: email.value.trim(), password: password.value })
}
</script>
