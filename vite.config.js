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
    port: 5175,        // puerto alterno para evitar conflictos
    strictPort: false, // si 5175 está ocupado, elige otro libre
    hmr: {
      host: '127.0.0.1',
      protocol: 'ws',
      overlay: false,
    },
  },
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
})
