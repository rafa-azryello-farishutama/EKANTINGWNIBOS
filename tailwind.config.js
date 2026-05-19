module.exports = {
  content: [
    "./*.php",
    "./pembeli/**/*.php",
    "./penjual/**/*.php",
    "./admin/**/*.php",
    "./apps/**/*.php",
    "./includes/**/*.php"
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "background": "#fafbf9",
        "primary": "#004900",
        "second-primary": "#f9f9fb",
        "input": "#f0f4f0",
        "text-1": "#191c1c",
        "text-2": "#4e5a48",
        "text-3": "#5e6659",
        "submit": "#005300"
      },
      keyframes: {
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(15px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        }
      },
      animation: {
        fadeInUp: 'fadeInUp 0.5s ease-out forwards'
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
