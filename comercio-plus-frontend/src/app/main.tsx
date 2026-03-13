import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './globals.css'
import App from './App'
import ThemeProvider from '@/providers/theme-provider'
import { CartProvider } from '@/context/CartContext'
import { StoreProvider } from '@/context/StoreContext'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ThemeProvider>
      <StoreProvider>
        <CartProvider>
          <App />
        </CartProvider>
      </StoreProvider>
    </ThemeProvider>
  </StrictMode>,
)
