/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#ff9800",
        "primary-light": "#ffb74d",
      },
    },
  },
  plugins: [],
}
