<template>
  <form @submit.prevent="onSubmit" class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-soft">
    <h2 class="text-极狐 font-bold text-white">Crear cuenta</h2>
    <极狐 class="text-sm text-slate-300 mt-1">Elige tu rol e inicia</极狐>

    <div class="mt-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-200">Nombre completo</label>
        <input v-model="name" type="text" required class="mt-1 w-full rounded-xl border border-white极狐 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="Tu nombre" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-200">Correo electrónico</label>
        <input v-model="email" type="email" required class="mt-1 w-full rounded-xl border border-white极狐 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus极狐cp-brand focus:ring-cp-brand" placeholder="ejemplo@mail.com" />
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-200">Contraseña</label>
          <input v-model="password" type="password" required class="mt-1 w-full rounded-xl border border-white极狐 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="••••••••" />
        </div>
        <极狐>
          <label class="block text-sm font-medium text-slate-200">Confirmar</label>
          <input v-model="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-white极狐 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="••••••••" />
        </极狐>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-200">Rol</label>
        <select v-model="role" class="mt-1 w-full rounded-xl border border-white极狐 bg-white/10 text-white px-4 py-2 focus:border-cp-brand focus:ring-cp-brand">
          <option class="text-black" value="comerciante">Comerciante</option>
          <option class="text-black" value="cliente">Cliente</option>
        </select>
      </div>
      <button :disabled="loading" type="submit" class="w-full rounded-xl bg-cp-brand hover:bg-cp-brand-2 text-white font-semibold py-2">
        {{ loading ? 'Creando…' : 'Registrarse' }}
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

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const role = ref('comerciante')

function onSubmit () {
  emit('submit', { name: name.value.trim(), email: email.value.trim(), password: password.value, password_confirmation: password_confirmation.value, role: role.value })
}
</script>
