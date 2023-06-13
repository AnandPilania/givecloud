module.exports = function (api) {
  const isDevelopment = api.env() === 'development'
  const enableReactRefreshWebpack = isDevelopment && !!process.env.ENABLE_REACT_REFRESH_WEBPACK

  return {
    presets: [
      [
        '@babel/preset-env',
        {
          useBuiltIns: 'usage',
          corejs: 3,
        },
      ],
      [
        '@babel/preset-react',
        {
          development: isDevelopment,
          runtime: 'automatic',
        },
      ],
    ],
    plugins: [
      enableReactRefreshWebpack && 'react-refresh/babel',
      '@babel/plugin-proposal-class-properties',
      '@babel/plugin-proposal-logical-assignment-operators',
      '@babel/plugin-proposal-object-rest-spread',
      '@babel/plugin-transform-runtime',
    ].filter(Boolean),
  }
}
