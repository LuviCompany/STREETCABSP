/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./index.html"],
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: "#0a1330",
          deep: "#0a1330",
          darker: "#070d1f",
        },
        accent: {
          DEFAULT: "#2563eb",
          light: "#3b82f6",
        },
        metal: "#c9d3e0",
      },
      fontFamily: {
        heading: ["'Space Grotesk'", "sans-serif"],
        sans: ["Inter", "sans-serif"],
      },
      maxWidth: {
        container: "1280px",
      },
    },
  },
  plugins: [],
};
