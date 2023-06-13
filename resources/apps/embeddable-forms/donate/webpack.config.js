const path = require('path')
const { merge } = require('webpack-merge')
const baseConfig = require('../../webpack.config.base')
const buildModuleAliases = require('../../buildModuleAliases')

const config = merge(baseConfig({ devServerPort: 5552 }), {
  name: 'Embeddable Donation Form',
  entry: {
    app: path.resolve(__dirname, './index.jsx'),
  },
  output: {
    path: path.resolve(__dirname, '../../../../public/assets/apps/embeddable-form/donate'),
    publicPath: '/assets/apps/embeddable-form/donate/',
  },
  resolve: {
    alias: buildModuleAliases(__dirname),
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: new RegExp('(/node_modules/|../../../assets/sass/vendor/)'),
        },
      },
    },
  },
})

module.exports = config
