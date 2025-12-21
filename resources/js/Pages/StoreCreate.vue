<template>
  <section class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Crear tienda</h1>
    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div>
        <label class="text-sm">Nombre</label>
        <input v-model="form.nombre_tienda" class="mt-1 w-full rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 px-3 py-2" required />
      </div>
      <div>
        <label class="text-sm">Slug</label>
        <input v-model="form.slug" class="mt-1 w-full rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 px-3 py-2" />
      </div>
      <div>
        <label class="text-sm">Descripción corta</label>
        <textarea v-model="form.short_description" class="mt-1 w-full rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 px-3 py-2"></textarea>
      </div>
      <div class="flex gap-2">
        <button class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary-600">Guardar</button>
        <RouterLink to="/stores" class="px-4 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800">Cancelar</RouterLink>
      </div>
    </form>
  </section>
</template>
<script setup>
import { reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
const router = useRouter()
const form = reactive({ nombre_tienda: '', slug: '', short_description: '' })
const submit = async () => {
  try {
    await fetch('/api/stores', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(form) })
    router.push('/stores')
  } catch {}
}
</script>

