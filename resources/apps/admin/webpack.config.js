const path = require('path')
const { merge } = require('webpack-merge')
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin')
const baseConfig = require('../webpack.config.base')
const buildModuleAliases = require('../buildModuleAliases')

const config = merge(baseConfig({ devServerPort: 5550 }), {
  context: __dirname,
  name: 'Admin',
  entry: {
    app: path.resolve(__dirname, './index.tsx'),
  },
  output: {
    path: path.resolve(__dirname, '../../../public/jpanel/assets/apps/admin'),
    publicPath: '/jpanel/assets/apps/admin/',
  },
  resolve: {
    alias: buildModuleAliases(__dirname),
  },
  optimization: {
    splitChunks: false,
  },
  plugins: [
    new ForkTsCheckerWebpackPlugin({
      async: process.env.NODE_ENV !== 'production',
      typescript: {
        configFile: './tsconfig.json',
        memoryLimit: 4096,
      },
    }),
  ],
})

module.exports = config
