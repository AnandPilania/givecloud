const path = require('path')

module.exports = {
  syntax: 'postcss-scss',
  plugins: [
    require('postcss-import'),
    'tailwindcss/nesting',
    require('tailwindcss')(path.resolve(__dirname, 'tailwind.config.js')),
    require('autoprefixer'),
  ],
}
