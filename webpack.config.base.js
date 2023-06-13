const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CaseSensitivePathsPlugin = require('case-sensitive-paths-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CircularDependencyPlugin = require('circular-dependency-plugin');

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
    output: {
        filename: 'js/[name].js',
        chunkFilename: 'js/[name].js',
        clean: true,
    },
    resolve: {
        extensions: ['.css', '.js', '.json', '.jsx', '.scss', '.ts', '.tsx', '.vue'],
    },
    module: {
        rules: [
            {
                test: /^((?!font).)*\.svg$/,
                oneOf: [
                    {
                        resourceQuery: /react/,
                        use: {
                            loader: '@svgr/webpack',
                            options: {
                                svgoConfig: {
                                    plugins: [
                                        {
                                            name: 'preset-default',
                                            params: { overrides: { removeViewBox: false } },
                                        },
                                    ],
                                },
                            },
                        },
                    },
                    {
                        type: 'asset/resource',
                        generator: {
                            filename: 'images/[hash][ext][query]',
                        },
                    },
                ],
            },
            {
                test: /\.(png|jpe?g|gif|webp)$/,
                type: 'asset/resource',
                generator: {
                    filename: 'images/[hash][ext][query]',
                },
            },
            {
                test: /(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/,
                type: 'asset/resource',
                generator: {
                    filename: 'fonts/[hash][ext][query]',
                },
            },
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.tsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: 'ts-loader',
                    options: {
                        transpileOnly: true,
                    },
                },
            },
            {
                test: /\.css$/,
                exclude: path.resolve(__dirname, 'resources/assets/css'),
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: { importLoaders: 1 },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: () => [require('autoprefixer')],
                            },
                        },
                    },
                ],
            },
            {
                test: /\.css$/,
                include: path.resolve(__dirname, 'resources/assets/css'),
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: { importLoaders: 1 },
                    },
                    'postcss-loader',
                ],
            },
            {
                test: path.resolve(__dirname, 'resources/assets/sass/app.scss'),
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                            url: (url, resourcePath) => {
                                return !url.startsWith('/jpanel/assets/');
                            },
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: () => [require('autoprefixer')],
                            },
                        },
                    },
                    'sass-loader',
                ],
            },
        ],
    },
    plugins: [
        new CaseSensitivePathsPlugin(),
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'production'),
            },
        }),
        new webpack.ContextReplacementPlugin(/moment[/\\]locale$/, /en/),
        new MiniCssExtractPlugin({
            filename: 'css/[name].css',
            chunkFilename: 'css/[id].css',
        }),
        new CircularDependencyPlugin({
            failOnError: true,
        }),
    ],
    devtool: process.env.NODE_ENV === 'production' ? false : 'source-map',
    optimization: {
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    output: { comments: /^\**!|@preserve|@license|@cc_on/i },
                },
            }),
            new CssMinimizerPlugin(),
        ],
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendor: {
                    test: new RegExp('(/node_modules/|resources/assets/sass/vendor/)'),
                    name: 'vendor',
                    chunks: 'all',
                    enforce: true,
                },
            },
        },
    },
};
