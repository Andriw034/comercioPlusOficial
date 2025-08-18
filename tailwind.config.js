/** @type {import('tailwindcss').Config} */
export default {
  content: ["./resources/**/*.blade.php", "./resources/**/*.js", "./resources/**/*.vue"],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: "#FF6000",
          50: "#FFF1E8",
          100: "#FFE2D1",
          200: "#FFC1A3",
          300: "#FF9F75",
          400: "#FF7E47",
          500: "#FF6000",
          600: "#CC4D00",
          700: "#993A00",
        },
        bg: { 50: "#f9f9f9", 100: "#f1f1f1", 200: "#e5e5e5" },
        textc: { 900: "#000", 800: "#333", 700: "#444" },
        state: { success: "#4ade80", warning: "#facc15", danger: "#f87171" },
      },
      boxShadow: { xl: "0 10px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)" },
      borderRadius: { '3xl': '1.5rem' }
    }
  },
  plugins: [],
};
