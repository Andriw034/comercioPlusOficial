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
          50: '#fff2eb',
          100: '#ffd8c2',
          200: '#ffbb94',
          300: '#ff9d66',
          400: '#ff8242',
          500: '#ff6b3d',
          600: '#e8582d',
          700: '#c94a24',
          800: '#a43d1c',
          900: '#7f2f15',
        },
        ink: '#0a0f1f',
        panel: '#0f172a',
        'panel-soft': '#111a2e',
        muted: '#8fa0b7',
        border: '#1f2937',
        success: '#10B981',
        warning: '#F59E0B',
        error: '#EF4444',
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
        sans: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 2px 8px rgba(10, 12, 16, 0.06)',
        medium: '0 4px 16px rgba(10, 12, 16, 0.08)',
        card: '0 18px 60px rgba(0, 0, 0, 0.20)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
