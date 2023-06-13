
const path = require('path');
const MjmlPlugin = require('../resources/assets/js/webpack/mjml-webpack-plugin');

new MjmlPlugin({
    inputPath: path.resolve(__dirname, '../resources/views/mailables/mjml'),
    outputPath: path.resolve(__dirname, '../resources/views/mailables'),
    outputStyle: 'beautified',
}).compileFiles();
