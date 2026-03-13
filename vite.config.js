import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  // Frontend oficial: comercio-plus-frontend/
  // El stack Laravel + Vue queda archivado en vite.legacy.config.js
  root: 'comercio-plus-frontend',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./comercio-plus-frontend/src', import.meta.url)),
    },
  },
})
