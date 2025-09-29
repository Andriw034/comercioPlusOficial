import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],
  server: {
    host: '127.0.0.1',
    port: 5175,
    strictPort: false,
    hmr: {
      host: '127.0.0.1',
      protocol: 'ws',
      overlay: false,
    },
    // Permitir conexiones desde cualquier origen para desarrollo
    cors: true,
    // Configuración adicional para asegurar que funcione con Laravel
    origin: 'http://127.0.0.1:5175',
  },
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
})
