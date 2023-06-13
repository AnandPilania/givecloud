
const defaultTheme = require('tailwindcss/defaultTheme');
const { tailwindcssOriginSafelist } = require('@headlessui-float/react')

module.exports = {
  corePlugins: {
    preflight: false, // @see: https://github.com/tailwindlabs/tailwindcss/issues/6602
    aspectRatio: false,
  },
  content: [
    // This is a pretty broad content pool, fine for now
    // since we aren't applying to any of our legacy styling
    // only new Tailwind styles that are introduced
    './resources/assets/**/*.{html,js,jsx,vue}',
    './resources/apps/**/*.{js,jsx}',
    './resources/views/**/*.{blade.php,html.php}',
  ],
  safelist: [
    // safelisting sizes used by
    // the <x-icons/> blade components
    'w-5',
    'h-5',
    'w-6',
    'h-6',
    ...tailwindcssOriginSafelist
  ],
  theme: {
    extend: {
      colors: { 
        theme: {
          DEFAULT: 'var(--theme--colour)',
          'light': 'var(--theme--colour-light)',
          'dark': 'var(--theme--colour-dark)'
        },
        gcp: {
          50: '#FDF6F7', 100: '#FFEEF0', 200: '#FFBACB', 300: '#FF90B8', 400: '#FF6FB0',
          500: '#F056AD', 600: '#E05097', 700: '#D04A83', 800: '#C04470', 900: '#B03E5E'
        },
        gcb: {
          50: '#F4FBFF', 100: '#F3F9FD', 200: '#E1F1FB', 300: '#CEE9F8', 400: '#AAD8F3',
          500: '#85C7EE', 600: '#78B3D6', 700: '#50778F', 800: '#3C5A6B', 900: '#283C47',
        },
        grey: {
          50: '#F2F2F2', 100: '#E0E0E0', 200: '#BDBDBD', 300: '#828282', 400: '#4F4F4F',
          500: '#333333'
        },
        brand: {
          blue: '#0066FF', teal: '#42CFFC', purple: '#8065EA', pink: '#FC58AF',
          red: '#E0514B', orange: '#E59145', yellow: '#F2C94C', green: '#18A586'
        },
        poppin:  { light: '#defdd6', DEFAULT: '#60f056', dark: '#36712f' },
        danger:  { light: '#ffd4d3', DEFAULT: '#fe3b59', dark: '#79262e' },
        warning: { light: '#fff0d2', DEFAULT: '#ffc548', dark: '#785d29' },
        success: { light: '#e2f6d7', DEFAULT: '#82d761', dark: '#416632' },
      },
      fontFamily: {
        sans: ['Nunito Sans', 'Inter', ...defaultTheme.fontFamily.sans],
        poppins: ['Poppins', 'sans-serif', ...defaultTheme.fontFamily.sans],
        inter: ['Inter', 'sans-serif', ...defaultTheme.fontFamily.sans]
      },
      fontSize: {
        xxs: '.625rem'
      },

      boxShadow: {
        'outline-gcp': '0 0 0 3px rgba(255, 144, 184, 0.45)',
        'outline-gcb': '0 0 0 3px rgba(206, 233, 248, 0.45)',
        'outline-gca': '0px 3px 10px rgba(0, 0, 0, 0.15)',
      },
      margin: {
        '1.5': '0.375rem',
      },
      maxHeight: {
        '112': '28rem',
      },
      opacity: {
        15: '0.15',
        90: '0.90',
      },
      screens: {
        'mdlg': {'min': '768px', 'max': '1024px'},
        'xs': '420px',
        "sm-h": { raw: "(min-height: 470px)" },
        "md-h": { raw: "(min-height: 580px)" },

      },
      transitionProperty: {
        'height': 'height',
        'margin': 'margin',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
    require('@tailwindcss/aspect-ratio'),
  ],
};
