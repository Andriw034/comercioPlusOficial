import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    host: '127.0.0.1',   // bind local loopback para evitar problemas de red
    port: 3000,          // puerto dev deseado
    strictPort: false,
    hmr: {
      // fuerza al cliente HMR a conectar al host/puerto correctos
      host: '127.0.0.1',
      port: 3000
    },
    proxy: {
      // proxy sólo en desarrollo para evitar CORS cuando llames a /api/foo
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
        ws: true
      }
    }
  }
})
