<?php

namespace Ds\Domain\Theming\Liquid;

use Ds\Domain\Theming\Theme;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Liquid\Exception\NotFoundException;
use Liquid\FileSystem;

class ThemeFileSystem implements FileSystem
{
    /** @var \Ds\Domain\Theming\Theme */
    protected $theme;

    /** @var string */
    protected $prefix;

    /**
     * Create an instance.
     *
     * @param string $prefix
     * @param \Ds\Domain\Theming\Theme|null $theme
     */
    public function __construct($prefix = '', Theme $theme = null)
    {
        $this->theme = $theme ?? app('theme');
        $this->prefix = $prefix;
    }

    /**
     * Retrieve a template file.
     *
     * @param string $templatePath
     * @return string
     */
    public function readTemplateFile($templatePath)
    {
        $path = "{$this->prefix}$templatePath.liquid";

        try {
            return $this->theme->asset($path)->value;
        } catch (ModelNotFoundException $exception) {
            // do nothing
        }

        // if template path contains a parent then attempt
        // to use the parent template instead
        if (Str::contains($templatePath, '.')) {
            return $this->readTemplateFile(Str::before($templatePath, '.'));
        }

        if (isDev()) {
            throw new NotFoundException("File [$path] not found in theme.");
        }

        return '';
    }

    /**
     * Get the Theme.
     *
     * @return \Ds\Domain\Theming\Theme
     */
    public function getTheme(): Theme
    {
        return $this->theme;
    }
}
