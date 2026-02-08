import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',

  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.tsx',
    './resources/js/**/*.ts',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },

      colors: {
        // Paleta base ComercioPlus
        comercioplus: {
          DEFAULT: '#FF6A00',
          50: '#FFF5EC',
          100: '#FFEAD9',
          200: '#FFD5B8',
          300: '#FFC096',
          400: '#FFA169',
          500: '#FF823D',
          600: '#FF6A00',
          700: '#E65700',
          800: '#B34400',
          900: '#803100',
        },

        // UI semántica
        'cp-bg': '#FDFDFD',
        'cp-surface': '#FFFFFF',
        'cp-text': '#1F2937',
        'cp-sub': '#6B7280',
      },

      borderRadius: {
        'lg-16': '16px',
        'xl-20': '20px',
        'xxl-24': '24px',
      },

      boxShadow: {
        'cp-card': '0px 4px 16px rgba(0, 0, 0, 0.05)',
      },
    },
  },

  plugins: [forms, typography],
}
