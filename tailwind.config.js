// Ubicación: raíz del proyecto Laravel (junto a package.json y vite.config.js)
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.vue',
    './resources/**/*.js',
    './resources/**/*.ts',
    './resources/**/*.tsx',
  ],
  theme: {
    extend: {
      colors: {
        'cp-primary': '#FF6000',
        'cp-primary-2': '#FF8A3D',
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
      },
      boxShadow: {
        soft: '0 8px 30px rgba(0,0,0,0.12)',
      },
    },
  },
  plugins: [forms, typography],
}
