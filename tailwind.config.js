/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50:  "#FFF0E6",
          100: "#FFE0CC",
          200: "#FFC199",
          300: "#FFA266",
          400: "#FF8333",
          500: "#FF6000", // NARANJA COMERCIO+
          600: "#E05500",
          700: "#B34400",
          800: "#803200",
          900: "#4D1E00"
        },
        accent: {
          500: "#6D28D9", // morado que combina con naranja
          600: "#5B21B6",
          700: "#4C1D95"
        },
        bg: {
          50: "#F9FAFB",
          100: "#F3F4F6",
          200: "#E5E7EB"
        },
        textc: {
          900: "#0F172A",
          800: "#1F2937",
          700: "#374151"
        }
      },
      boxShadow: {
        card: "0 20px 60px -15px rgba(0,0,0,.35)"
      }
    },
  },
  plugins: [],
};
