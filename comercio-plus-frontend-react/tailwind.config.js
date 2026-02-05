/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './app/**/*.{js,ts,jsx,tsx}',
    './components/**/*.{js,ts,jsx,tsx}',
    './lib/**/*.{js,ts,jsx,tsx}',
    './types/**/*.{js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#fff4ec',
          100: '#ffe1cc',
          200: '#ffc7a0',
          300: '#ffa16b',
          400: '#ff7a33',
          500: '#ff6600',
          600: '#e05a00',
          700: '#b84800',
          800: '#8a3600',
          900: '#5c2400',
        },
        ink: '#0b1220',
        panel: '#111827',
        'panel-soft': '#1f2937',
        bg: {
          1: 'var(--cp-bg1)',
          2: 'var(--cp-bg2)',
        },
        surface: 'var(--cp-surface)',
        border: 'var(--cp-border)',
        text: 'var(--cp-text)',
        muted: 'var(--cp-muted)',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
        sans: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 12px 30px rgba(0, 0, 0, 0.18)',
        medium: '0 16px 40px rgba(0, 0, 0, 0.24)',
        card: '0 24px 60px rgba(0, 0, 0, 0.28)',
        glass: '0 24px 60px rgba(0, 0, 0, 0.35)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
