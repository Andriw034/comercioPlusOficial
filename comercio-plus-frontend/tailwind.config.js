/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#FF7A3D',
        secondary: '#5B6871',
        surface: '#FFF0E8',
        'surface-dark': '#FFE4D1',
        text: '#1B1F23',
        'text-light': '#5B6871',
        border: '#E7E2DE',
        success: '#10B981',
        warning: '#F59E0B',
        error: '#EF4444',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 2px 8px rgba(10, 12, 16, 0.06)',
        medium: '0 4px 16px rgba(10, 12, 16, 0.08)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
