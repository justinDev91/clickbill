/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
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
        /* Leave as an example in case we want color variations */
        // darkBlue: {
        //   50: "#f5f7fa",
        //   100: "#e9eef5",
        // },
        yellow: "var(--primary-color)",
        gray: "var(--gray)",
        black: "var(--black)",
        white: "var(--white)",
      },
    },
  },
  plugins: [],
}

