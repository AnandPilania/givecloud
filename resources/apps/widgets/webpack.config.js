const path = require('path')
const { merge } = require('webpack-merge')
const baseConfig = require('../webpack.config.base')

const config = merge(baseConfig(), {
  name: 'Widgets',
  entry: {
    widgets: path.resolve(__dirname, './index.js'),
  },
  output: {
    library: {
      name: 'GivecloudWidgetApi',
      type: 'window',
      export: 'default',
    },
    filename: '[name].js',
    chunkFilename: '[name].js',
    path: path.resolve(__dirname, '../../../public/v1'),
    publicPath: '/v1/',
  },
  resolve: {
    alias: {
      '@core': path.resolve(__dirname, '../core/src/'),
      '@/components': path.resolve(__dirname, './components/'),
      '@/utils': path.resolve(__dirname, './utils/'),
    },
  },
  module: {
    rules: [
      {
        test: /\.svg$/,
        type: 'asset/source',
      },
    ],
  },
  optimization: {
    splitChunks: false,
  },
})

module.exports = config
