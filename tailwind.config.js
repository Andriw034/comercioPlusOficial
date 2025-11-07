// tailwind.config.js
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{js,ts,vue}',
  ],
  theme: {
    extend: {
      colors: {
        'cp-primary': '#FF6000',
        'cp-primary-2': '#FF8A3D',
      },
      boxShadow: {
        cp: '0 10px 30px rgba(255, 96, 0, 0.15)',
      },
    },
  },
  plugins: [],
}
