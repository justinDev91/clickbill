const Color = require('color');
const darken = (clr, val) => Color(clr).darken(val).rgb().string();

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',

  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./src/Form/*.php",
  ],
  theme: {
    screens: {
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      '2xl': '1536px',
    },
    extend: {
      colors: {
        amberYellow: "#F6C31E",
        amberYellowDarker: darken('#F6C31E', 0.2),
      },
      fontFamily: {
        'sans': ['Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

