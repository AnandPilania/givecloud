module.exports = {
  syntax: 'postcss-scss',
  plugins: [
    require('postcss-import'),
    'tailwindcss/nesting',
    require('tailwindcss'),
    require('autoprefixer'),
  ]
};
