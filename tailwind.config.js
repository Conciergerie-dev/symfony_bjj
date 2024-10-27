/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      fontFamily: {
        title: ['Archivo', 'sans-serif'],
        body: ['Roboto Mono', 'sans-serif'],
      },
    },
  },
  corePlugins: {
    aspectRatio: false,
  },
  plugins: [
      require('@tailwindcss/forms'),
      require('@tailwindcss/aspect-ratio'),
  ],
}
