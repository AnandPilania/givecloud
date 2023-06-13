const path = require('path');
const { merge } = require('webpack-merge');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const MjmlPlugin = require('./resources/assets/js/webpack/mjml-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const webpack = require('webpack');
const baseConfig = require('./webpack.config.base');

function useEntry(entryName, v) {
    return (process.env.USE_ENTRY === entryName || !process.env.USE_ENTRY) && { [entryName]: v };
}

function entryPlugin(entryName, v) {
    return process.env.USE_ENTRY === entryName || !process.env.USE_ENTRY ? v : [];
}

const config = merge(baseConfig, {
    name: 'jQuery Sandwich',
    entry: {
        ...useEntry('app', [
            path.resolve(__dirname, 'resources/assets/js/app.js'),
            path.resolve(__dirname, 'resources/assets/sass/app.scss'),
        ]),
        ...useEntry('tailwind', path.resolve(__dirname, 'resources/assets/css/tailwind.css')),
    },
    output: {
        path: path.resolve(__dirname, 'public/jpanel/assets/dist'),
        publicPath: '/jpanel/assets/dist/',
    },
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm.js',
            '@app': path.resolve(__dirname, 'resources/assets/js/'),
            '@bootstrap': path.resolve(__dirname, 'node_modules/bootstrap-sass/assets/stylesheets/bootstrap/'),
            'ladda/css/ladda-themed.scss': 'ladda/css/ladda.scss',
            'vue-ladda$': 'vue-ladda/src/vue-ladda',
        },
    },
    module: {
        rules: [
            {
                test: /resources\/assets\/js\/templates\/.*\.html$/,
                type: 'asset/source',
            },
            {
                test: /\.scss$/,
                exclude: path.resolve(__dirname, 'resources/assets/sass/app.scss'),
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
            },
            {
                test: /\.vue$/,
                exclude: /node_modules/,
                loader: 'vue-loader',
            },
            {
                test: /vue-ladda\.vue$/,
                loader: 'vue-loader',
            },
        ],
    },
    externals: {
        jquery: 'jQuery',
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new VueLoaderPlugin(),
        {
            apply(compiler) {
                compiler.hooks.compilation.tap('CleanupUnwantedAssets', (compilation) => {
                    compilation.hooks.processAssets.tap(
                        {
                            name: 'CleanupUnwantedAssets',
                            stage: webpack.Compilation.PROCESS_ASSETS_STAGE_OPTIMIZE,
                        },
                        () => {
                            delete compilation.assets['js/tailwind.js'];
                            delete compilation.assets['js/tailwind.js.map'];
                        }
                    );
                });
            },
        },
        ...entryPlugin('app', [
            new CopyPlugin({
                patterns: [
                    {
                        from: path.resolve(__dirname, 'node_modules/bootstrap4/scss'),
                        to: path.resolve(__dirname, 'resources/theming/scss/bootstrap4'),
                    },
                    {
                        from: path.resolve(__dirname, 'node_modules/font-awesome/scss'),
                        to: path.resolve(__dirname, 'resources/theming/scss/font-awesome'),
                    },
                ],
            }),
            new MjmlPlugin({
                inputPath: path.resolve(__dirname, 'resources/views/mailables/mjml'),
                outputPath: path.resolve(__dirname, 'resources/views/mailables'),
                outputStyle: 'beautified',
            }),
        ]),
    ],
});

module.exports = config;
