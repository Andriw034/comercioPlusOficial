/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#EE471B',
        secondary: '#2A2A2A',
        danger: '#C20000',
        light: '#FFFFFF',
        dark: '#0F172A',
        border: '#374151',
        text: '#E7DBCB',
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
