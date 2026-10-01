/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        primary: '#2874f0',
        secondary: '#fb641b',
        success: '#388e3c',
        danger: '#ff3f3f'
      }
    }
  },
  plugins: []
}
