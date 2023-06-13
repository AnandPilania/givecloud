<?php

namespace Ds\Domain\Theming;

use Ds\Models\Theme;
use Illuminate\Support\Str;
use ScssPhp\ScssPhp\Colors;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\OutputBlock;

class ScssCompiler extends Compiler
{
    /** @var string */
    private $currentDirectory;

    /** @var \Ds\Models\Theme */
    protected $theme;

    /**
     * Create instance.
     */
    public function __construct(Theme $theme)
    {
        parent::__construct();

        $this->theme = $theme;

        $this->addImportPath([$this, 'resolveImportFromTheming']);

        // add import path for theme stylesheets
        if ($theme->locked) {
            $this->addImportPath(base_path("resources/themes/{$theme->handle}/styles/"));
        } else {
            $this->addImportPath([$this, 'resolveImportFromThemeStyles']);
        }

        // register custom functions
        $this->registerFunction('asset_url', [$this, 'functionAssetUrl']);
        $this->registerFunction('settings', [$this, 'functionSettings']);
        $this->registerFunction('site', [$this, 'functionSite']);
    }

    /**
     * @param string $url
     */
    protected function resolveImportFromTheming($url): ?string
    {
        if (Str::startsWith($url, '~')) {
            $hasExtension = preg_match('/\.s?css$/', $url);

            $regular = base_path('resources/theming/scss/') . substr($url, 1);
            $partial = dirname($regular) . '/_' . basename($regular);

            if (is_file($file = "$regular.scss") || ($hasExtension && is_file($file = $regular))) {
                return $file;
            }

            if (is_file($file = "$partial.scss") || ($hasExtension && is_file($file = $partial))) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param string $url
     */
    protected function resolveImportFromThemeStyles($url): ?string
    {
        if ($this->theme->assets()->where('key', "styles/$url.scss")->exists()) {
            return "$url.scss";
        }

        if ($this->theme->assets()->where('key', "styles/$url")->exists()) {
            return $url;
        }

        return null;
    }

    /**
     * Import file
     *
     * @param string $path
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $out
     */
    protected function importFile($path, OutputBlock $out)
    {
        if (is_file($path) || $this->theme->locked) {
            return parent::importFile($path, $out);
        }

        $realPath = "asset://styles/$path";
        $this->pushCallStack("import $realPath");

        if (isset($this->importCache[$realPath])) {
            $this->handleImportLoop($realPath);

            $tree = $this->importCache[$realPath];
        } else {
            $code = $this->theme->assets()->where('key', "styles/$path")->value('value');
            $parser = $this->parserFactory($path);
            $tree = $parser->parse($code);

            $this->importCache[$realPath] = $tree;
        }

        $currentDirectory = $this->currentDirectory;
        $this->currentDirectory = dirname($path);

        $this->compileChildrenNoReturn($tree->children, $out);
        $this->currentDirectory = $currentDirectory;
        $this->popCallStack();
    }

    /**
     * Compile scss
     *
     * @param string $code
     * @param string $name
     * @return string
     */
    public function compile($code, $name = null)
    {
        set_time_limit(0);

        return parent::compile($code, $name);
    }

    /**
     * Returns URL relative to the theme's asset directory.
     *
     * @param array $args
     * @return string
     */
    public function functionAssetUrl($args)
    {
        $path = $this->getArgumentValue($args);

        return 'url("' . secure_site_url("/static/{$this->theme->handle}/assets/$path") . '")';
    }

    /**
     * Allows you to access theme settings
     *
     * @param array $args
     * @return string|array
     */
    public function functionSettings($args)
    {
        $name = $this->getArgumentValue($args, 0);
        $default = $this->getArgumentValue($args, 1);

        $value = (string) (new \Ds\Domain\Theming\Liquid\Drops\SettingsDrop)->invokeDrop($name);

        if ($value === '') {
            $value = (string) $default;
        }

        // Convert color strings into Scss color type allowing for integration
        // with Sass color functions for example:
        //   lighten(setting('Default Color'), 20%)
        return $this->toColor($value);
    }

    /**
     * Allows you to access theme settings
     *
     * @param array $args
     * @return string|array
     */
    public function functionSite($args)
    {
        $name = $this->getArgumentValue($args, 0);
        $default = $this->getArgumentValue($args, 1);

        $value = (string) (new \Ds\Domain\Theming\Liquid\Drops\SiteDrop)->invokeDrop($name);

        if ($value === '') {
            $value = (string) $default;
        }

        // Convert color strings into Scss color type allowing for integration
        // with Sass color functions for example:
        //   lighten(setting('Default Color'), 20%)
        return $this->toColor($value);
    }

    /**
     * Helper for extracting the value of function arguments.
     *
     * @param array $args
     * @param int $index
     * @param string $default
     * @return string
     */
    protected function getArgumentValue($args, $index = 0, $default = '')
    {
        return empty($args[$index]) ? $default : trim($this->compileValue($args[$index]), '"\'');
    }

    /**
     * Coerce a string color values into Scss values.
     *
     * @param string $value
     * @return array|string
     */
    protected function toColor($value)
    {
        // Process matching HEX colors
        if (preg_match('/^\s*(#([0-9a-f]{6})|#([0-9a-f]{3}))\s*$/Ais', $value, $m)) {
            $color = ['color'];
            if (isset($m[3])) {
                $num = $m[3];
                $width = 16;
            } else {
                $num = $m[2];
                $width = 256;
            }
            $num = hexdec($num);
            foreach ([3, 2, 1] as $i) {
                $t = $num % $width;
                $num /= $width;
                $color[$i] = $t * (256 / $width) + $t * floor(16 / $width);
            }

            return $color;
        }

        // Match colors by their CSS names
        $rgba = Colors::colorNameToRGBa($value);

        if ($rgba) {
            return isset($rgba[3])
                ? ['color', (int) $rgba[0], (int) $rgba[1], (int) $rgba[2], (int) $rgba[3]]
                : ['color', (int) $rgba[0], (int) $rgba[1], (int) $rgba[2]];
        }

        return $value;
    }
}
