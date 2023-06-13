const { merge } = require('webpack-merge')
const fs = require('fs')
const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin')
const baseConfig = require('../../webpack.config.base')

const isDevelopment = process.env.NODE_ENV !== 'production'
const usingWebpackServe = process.env.WEBPACK_SERVE === 'true'

// when running in Docker environment `config` will be root level directory
// but if running locally we can use relative path to back up to dev-environment directory
const nginxConfigPath = fs.existsSync('/config/nginx') ? '' : path.join(__dirname, '../../../..')

const buildConfig = ({ devServerPort } = {}) => {
  const useDevServer = usingWebpackServe && !!devServerPort

  const defaultSassLoaders = [
    {
      loader: 'css-loader',
      options: {
        modules: {
          localIdentName: isDevelopment ? '[name]_[local]_[hash:base64:6]' : '[hash:base64]',
        },
      },
    },
    'sass-loader',
    {
      loader: 'postcss-loader',
      options: {
        postcssOptions: {
          plugins: () => [require('autoprefixer')],
        },
      },
    },
  ]

  return merge(baseConfig, {
    ...(useDevServer && {
      devServer: {
        host: '0.0.0.0',
        port: devServerPort,
        server: {
          type: 'https',
          options: {
            key: fs.readFileSync(`${nginxConfigPath}/config/nginx/certs/givecloud.test/privkey.pem`),
            cert: fs.readFileSync(`${nginxConfigPath}/config/nginx/certs/givecloud.test/fullchain.pem`),
          },
        },
        headers: { 'Access-Control-Allow-Origin': '*' },
        allowedHosts: ['.givecloud.test'],
        hot: 'only',
        liveReload: false,
        devMiddleware: {
          writeToDisk: true,
        },
      },
    }),
    module: {
      rules: [
        {
          test: /\.scss$/,
          exclude: path.resolve(__dirname, '../../../assets/sass/app.scss'),
          oneOf: [
            {
              resourceQuery: /string-loader/,
              use: ['to-string-loader', ...defaultSassLoaders],
            },
            {
              resourceQuery: /style-loader/,
              use: ['style-loader', ...defaultSassLoaders],
            },
            {
              use: [MiniCssExtractPlugin.loader, ...defaultSassLoaders],
            },
          ],
        },
      ],
    },
    externals: {
      givecloud: 'Givecloud',
      google: 'google',
    },
    plugins: [useDevServer && new ReactRefreshWebpackPlugin(), ...baseConfig.plugins].filter(Boolean),
  })
}

module.exports = buildConfig
