<template>
  <main class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Tema de la tienda</h1>

    <form @submit.prevent="save" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div><label class="text-sm">Primario</label><input v-model="primary_color" class="input"></div>
        <div><label class="text-sm">Texto</label><input v-model="text_color" class="input"></div>
        <div><label class="text-sm">Botón</label><input v-model="button_color" class="input"></div>
        <div><label class="text-sm">Fondo</label><input v-model="background_color" class="input"></div>
      </div>

      <div class="rounded-2xl border border-[var(--cp-border)] p-6 bg-white">
        <p class="mb-2 text-sm text-[var(--cp-ink-2)]">Preview vivo</p>
        <div class="rounded-xl p-6" :style="previewBg">
          <div class="bg-white/85 backdrop-blur rounded-lg p-4 inline-flex items-center gap-3">
            <div class="h-10 w-10 rounded-lg bg-white border"></div>
            <div class="font-bold">Tu tienda</div>
            <button type="button" class="ml-4 btn btn-primary">Botón ejemplo</button>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <router-link to="/products" class="btn btn-secondary">Cancelar</router-link>
        <button class="btn btn-primary">Guardar</button>
      </div>
      <p v-if="msg" class="text-green-700 mt-2">{{ msg }}</p>
    </form>
  </main>
</template>

<script setup>
import { reactive, computed, watchEffect, ref } from 'vue'
import { session, applyThemeFromStore } from '../stores/session'
import api from '../lib/api'

const form = reactive({
  primary_color: session.store?.primary_color || '#FF6A2E',
  text_color: session.store?.text_color || '#0F172A',
  button_color: session.store?.button_color || '#FF6A2E',
  background_color: session.store?.background_color || '#FFF7F2'
})
const { primary_color, text_color, button_color, background_color } = form
const msg = ref('')

const previewBg = computed(()=>({ background: `linear-gradient(135deg, ${primary_color} , ${button_color})` }))

watchEffect(()=>{
  applyThemeFromStore(form) // actualiza variables CSS en vivo
})

async function save(){
  if(!session.store){ msg.value = 'No hay tienda'; return }
  try{
    await api.post('/api/v1/stores/'+session.store.id+'/theme', form)
    msg.value = 'Tema guardado'
  }catch(e){ msg.value = 'No se pudo guardar' }
}
</script>
