
const fs = require('fs-extra');
const crypto = require('crypto');
const globby = require('globby');
const path = require('path');
const { Liquid } = require('liquidjs');
const htmlMinifier = require('html-minifier');
const jsBeautify = require('js-beautify');
const mjml2html = require('mjml');
const { registerComponent: registerMjmlComponent } = require('mjml-core');
const MjHeadline = require('./mjml/mj-headline');

registerMjmlComponent(MjHeadline);

class MjmlWebpackPlugin {
    constructor(options) {
        this.inputPath = options.inputPath;
        this.outputPath = options.outputPath;
        this.outputStyle = options.outputStyle || null;
    }

    apply(compiler) {
        compiler.hooks.thisCompilation.tap('MjmlWebpackPlugin', (compilation) => {
            compilation.hooks.additionalAssets.tapAsync('MjmlWebpackPlugin', async (callback) => {
                await this.compileFiles((file) => {
                    compilation.fileDependencies.add(file.absolutePath);
                });

                callback();
            });
        });
    }

    async getFiles() {
        const files = await globby('*.mjml', {
            cwd: this.inputPath,
            onlyFiles: true,
            deep: 0,
        });

        return files.map((file) => {
            return {
                name: file.replace(/\.mjml$/, ''),
                absolutePath: path.resolve(this.inputPath, file),
            };
        });
    }

    async compileFiles(callback) {
        const tasks = (await this.getFiles()).map((file) => {
            callback && callback(file);
            return this.compileFile(file.name);
        });

        await Promise.all(tasks);
    }

    async compileFile(file) {
        let contents = await this.compileLiquid(file);
        contents = await this.compileMjml(contents, file);
        contents = await this.cleanupHtml(contents);
        return this.writeFile(file, contents);
    }

    compileLiquid(file) {
        const engine = new Liquid({
            root: [this.inputPath, path.join(this.inputPath, 'partials')],
            extname: '.mjml',
            outputDelimiterLeft: '<%=',
            outputDelimiterRight: '%>',
        });

        return engine.renderFile(file);
    }

    async compileMjml(input) {
        const compiled = mjml2html(input, { filePath: this.inputPath });
        if (compiled.errors.length) {
            throw new Error(compiled.errors[0].message);
        }

        return compiled.html;
    }

    async cleanupHtml(input) {
        switch (this.outputStyle) {
            case 'beautified': return this.beautifyHtml(input);
            case 'minified': return this.minifyHtml(input);
            default: return input;
        }
    }

    async beautifyHtml(input) {
        const html = await jsBeautify.html(input, {
            indent_size: 2,
            wrap_attributes_indent_size: 2,
            max_preserve_newline: 0,
            preserve_newlines: false,
        })

        return html.replace(/<!-- beautify ignore:(start|end) -->\n?/g, '');
    }

    async minifyHtml(input) {
        return htmlMinifier.minify(input, {
            collapseWhitespace: true,
            minifyCSS: false,
            caseSensitive: true,
            removeEmptyAttributes: true,
        });
    }

    writeFile(file, contents) {
        const filename = path.resolve(this.outputPath, `${file}.blade.php`);

        try {
            const checksum = crypto.createHash('md5').update(contents).digest('hex');
            const originalChecksum = crypto.createHash('md5').update(fs.readFileSync(filename)).digest('hex');

            // a short circuit of sorts to prevent indefinite recompilation of the
            // Blade files. if they haven't changed we skip writing them to the filesystem
            if (checksum === originalChecksum) {
                return Promise.resolve();
            }
        } catch (err) {
            // do nothing
        }

        return fs.promises.writeFile(filename, contents);
    }
}

module.exports = MjmlWebpackPlugin;
