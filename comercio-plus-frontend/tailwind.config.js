/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        cplus: {
          orange: "#FF6000",
          orange2: "#FF7A2E",
          ink: "#0B0F1A",
          ink2: "#4B5563",
          cream: "#FFF6EF",
          card: "#FFFFFF",
          border: "#E5E7EB",
        },
      },
      boxShadow: {
        soft: "0 20px 40px rgba(0,0,0,.20)",
      }
    },
  },
  plugins: [],
}
