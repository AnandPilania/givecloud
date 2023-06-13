const path = require('path')
const CaseSensitivePathsPlugin = require('case-sensitive-paths-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const webpack = require('webpack')

module.exports = {
  mode: process.env.NODE_ENV,
  stats: {
    hash: false,
    version: false,
    timings: false,
    children: false,
    errorDetails: false,
    entrypoints: false,
    performance: process.env.NODE_ENV === 'production',
    chunks: false,
    modules: false,
    reasons: false,
    source: false,
    publicPath: false,
    builtAt: false,
  },
  performance: {
    hints: false,
  },
  entry: path.resolve(__dirname, './src/index.js'),
  output: {
    library: {
      name: 'Givecloud',
      type: 'umd',
    },
    path: path.resolve(__dirname, '../../../public/assets/js'),
    filename: 'core.js',
    clean: true,
  },
  resolve: {
    alias: {
      '@core': path.resolve(__dirname, 'src/'),
    },
    mainFields: ['browser', 'module', 'main'],
    extensions: ['.js', '.json'],
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: [
              '@babel/plugin-proposal-class-properties',
              '@babel/plugin-proposal-object-rest-spread',
              '@babel/plugin-proposal-logical-assignment-operators',
              ['@babel/plugin-transform-runtime', { regenerator: true, corejs: 3 }],
            ],
          },
        },
      },
    ],
  },
  plugins: [new CaseSensitivePathsPlugin(), new webpack.ContextReplacementPlugin(/validatorjs[/\\]src[/\\]lang/, /en/)],
  optimization: {
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          output: { comments: /^\**!|@preserve|@license|@cc_on/i },
        },
      }),
    ],
  },
}
