const path = require('path')
const { merge } = require('webpack-merge')
const baseConfig = require('../webpack.config.base')

const config = merge(baseConfig({ devServerPort: 5551 }), {
  name: 'Donation Forms',
  entry: {
    app: path.resolve(__dirname, './index.jsx'),
  },
  output: {
    path: path.resolve(__dirname, '../../../public/assets/apps/donation-forms'),
    publicPath: '/assets/apps/donation-forms/',
  },
  resolve: {
    alias: {
      '@/aerosol': path.resolve(__dirname, '../aerosol'),
      '@/atoms': path.resolve(__dirname, 'atoms'),
      '@/components': path.resolve(__dirname, 'components'),
      '@/constants': path.resolve(__dirname, 'constants'),
      '@/context': path.resolve(__dirname, 'context'),
      '@/hooks': path.resolve(__dirname, 'hooks'),
      '@/routes': path.resolve(__dirname, 'routes.jsx'),
      '@/shared': path.resolve(__dirname, '../shared'),
      '@/screens': path.resolve(__dirname, 'screens'),
      '@/templates': path.resolve(__dirname, 'templates'),
      '@/utilities': path.resolve(__dirname, 'utilities'),
    },
  },
  module: {
    rules: [
      {
        test: /\.mp3$/,
        type: 'asset/resource',
        generator: {
          filename: 'audio/[hash][ext][query]',
        },
      },
    ],
  },
})

module.exports = config
