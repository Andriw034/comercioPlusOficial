import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },
      colors: {
        // Paleta base de ComercioPlus
        comercioplus: {
          DEFAULT: '#FF6A00', // Naranja principal
          '50': '#FFF5EC',
          '100': '#FFEAD9',
          '200': '#FFD5B8',
          '300': '#FFC096',
          '400': '#FFA169',
          '500': '#FF823D',
          '600': '#FF6A00', // Base
          '700': '#E65700',
          '800': '#B34400',
          '900': '#803100',
        },
        // Colores semánticos de la UI
        'cp-bg': '#FDFDFD',        // Fondo principal de la app
        'cp-surface': '#FFFFFF',  // Fondo para tarjetas, modales, etc.
        'cp-text': '#1F2937',     // Color de texto principal
        'cp-sub': '#6B7280',     // Color de texto secundario (subtítulos, placeholders)
      },
      borderRadius: {
        'lg-16': '16px',          // Botones, inputs
        'xl-20': '20px',          // Tarjetas pequeñas
        'xxl-24': '24px',         // Tarjetas grandes
      },
      boxShadow: {
        'cp-card': '0px 4px 16px rgba(0, 0, 0, 0.05)', // Sombra para tarjetas
      }
    },
  },

  plugins: [forms, typography],
};
