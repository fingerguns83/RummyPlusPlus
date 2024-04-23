/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
    "./*.js"
  ],
  theme: {
    extend: {
      gridTemplateColumns: {
        '13': 'repeat(13, minmax(0, 1fr))',
        '15': 'repeat(15, minmax(0, 1fr))',
        '17': 'repeat(17, minmax(0, 1fr))',
        '20': 'repeat(20, minmax(0, 1fr))',
      }
    },
    fontFamily: {
      'fancy': ['Frauces', 'serif']
    }
  },
  plugins: [],
}

