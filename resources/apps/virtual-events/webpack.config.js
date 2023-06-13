const path = require('path')
const { merge } = require('webpack-merge')
const baseConfig = require('../webpack.config.base')
const buildModuleAliases = require('../buildModuleAliases')

const config = merge(baseConfig({ devServerPort: 5554 }), {
  name: 'Virtual Events',
  entry: {
    app: path.resolve(__dirname, './index.jsx'),
  },
  output: {
    path: path.resolve(__dirname, '../../../public/assets/apps/virtual-events'),
    publicPath: '/assets/apps/virtual-events/',
  },
  resolve: {
    alias: buildModuleAliases(__dirname),
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: new RegExp('(/node_modules/|../../assets/sass/vendor/)'),
        },
      },
    },
  },
})

module.exports = config
