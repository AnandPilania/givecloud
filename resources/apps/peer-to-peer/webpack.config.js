const path = require('path')
const { mergeWithRules } = require('webpack-merge')
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin')
const baseConfig = require('../webpack.config.base')
const buildModuleAliases = require('../buildModuleAliases')

const merge = mergeWithRules({
  module: {
    rules: {
      test: 'match',
      use: 'prepend',
    },
  },
})

const config = merge(baseConfig({ devServerPort: 5553 }), {
  context: __dirname,
  name: 'Peer to Peer',
  entry: {
    app: path.resolve(__dirname, './index.tsx'),
  },
  output: {
    path: path.resolve(__dirname, '../../../public/assets/apps/peer-to-peer'),
    publicPath: '/assets/apps/peer-to-peer/',
  },
  resolve: {
    alias: {
      ...buildModuleAliases(__dirname),
      '@/router': path.resolve(__dirname, './router'),
    },
  },
  module: {
    rules: [
      {
        test: /screens\/PeerToPeer\/svgs\/.*\.svg$/,
        type: 'asset/resource',
        generator: {
          filename: 'images/avatars/[name][ext][query]',
        },
      },
    ],
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /node_modules/,
        },
      },
    },
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
