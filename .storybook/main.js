const buildModuleAliases = require("../resources/apps/buildModuleAliases");
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  "stories": [
    "../resources/apps/admin/**/**/*.stories.@(js|jsx|ts|tsx)",
    "../resources/apps/aerosol/**/**/*.stories.@(js|jsx|ts|tsx)",
    "../resources/apps/peer-to-peer/**/**/*.stories.@(js|jsx|ts|tsx)"
  ],
  "addons": [
    "storybook-color-picker",
    "@storybook/addon-a11y",
    "@storybook/addon-links", 
    "@storybook/addon-essentials", 
    "@storybook/addon-interactions", 
    {
      name: '@storybook/addon-postcss',
      options: {
        postcssLoaderOptions: {
          implementation: require('postcss'),
        },
      },
    },
  ],
  "framework": "@storybook/react",
  core: {
    builder: "webpack5",
    options: {
      fsCache: true,
    },
  },
  webpackFinal: (config) => {
    config.plugins.push(new MiniCssExtractPlugin());
    config.module.rules.push({
      test: /\.scss$/,
      use: [
        MiniCssExtractPlugin.loader,
        {
          loader: 'css-loader',
          options: {
            modules: {
              localIdentName:'[name]_[local]_[hash:base64:6]',
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
      ],
    });

    config.resolve.alias = {
      ...config.resolve.alias,
      ...buildModuleAliases(path.join(__dirname, '../resources/apps/aerosol')),
    }

    config.externals = {
      givecloud: 'Givecloud',
    };
      
    return config;
  },
};

