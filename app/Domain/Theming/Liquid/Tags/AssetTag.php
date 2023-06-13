<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Ds\Domain\Theming\Liquid\ThemeFileSystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Regexp;

/**
 * Enqueues an asset for loading.
 *
 * Example:
 *
 *     {% asset "theme.js" %}
 */
class AssetTag extends AbstractTag
{
    /** @var string The URL of the asset */
    private $assetUrl;

    /** @var string */
    private $cacheBuster;

    /** @var \Ds\Domain\Theming\Theme */
    private $themeService;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param \Liquid\FileSystem $fileSystem
     *
     * @throws \Liquid\Exception\ParseException
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        $syntax = new Regexp('/(' . Liquid::get('QUOTED_STRING') . ')/');

        if ($syntax->match($markup)) {
            $this->extractAttributes(str_replace($syntax->matches[0], '', $markup));
            $this->attributes['url'] = $syntax->matches[0];
        } else {
            $this->extractAttributes($markup);
        }

        if (empty($this->attributes['url'])) {
            throw new ParseException("Syntax Error in 'asset' - Valid syntax: asset [url]");
        }

        if ($fileSystem && $fileSystem instanceof ThemeFileSystem) {
            $this->themeService = $fileSystem->getTheme();
        } else {
            $this->themeService = app('theme');
        }
    }

    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        if ($context->registers['content_for_header_rendered']) {
            $this->attributes['footer'] = 'true';
        }

        $this->assetUrl = $context->get($this->attributes['url']);

        if (Str::startsWith($this->assetUrl, 'gc://')) {
            $this->themeService = app('theme')->theme('givecloud');
            $this->assetUrl = str_replace('gc://', '~/', $this->assetUrl);
        }

        $theme = $this->themeService->getEloquentTheme();

        if (Str::startsWith($this->assetUrl, '~/')) {
            $asset = $this->themeService->asset(Str::after($this->assetUrl, '~/'));

            if ($asset) {
                $this->assetUrl = "/static/{$theme->handle}/{$asset->key}";
                $this->cacheBuster = $asset->cacheBuster;
            }
        }

        // in dev dynamically swap to the development build of Vue
        if (Str::endsWith($this->assetUrl, 'dist/vue.min.js') && isDev()) {
            $this->assetUrl = str_replace('vue.min.js', 'vue.js', $this->assetUrl);
        }

        if (! Str::startsWith($this->assetUrl, ['http', 'https', '/'])) {
            $this->assetUrl = $context->invoke('asset_url', $this->assetUrl);
        }

        if ($theme->locked && Str::startsWith($this->assetUrl, [
            "/static/{$theme->handle}/assets/",
            "/static/{$theme->handle}/scripts/",
        ])) {
            $this->assetUrl = "/-{$this->assetUrl}";
        }

        if (Str::contains($this->assetUrl, '{google_maps_api_key}')) {
            $this->assetUrl = str_replace('{google_maps_api_key}', $context->get('settings.google_maps_api_key'), $this->assetUrl);
        }

        $asset = [
            'url' => $this->cacheBuster ? "{$this->assetUrl}?v={$this->cacheBuster}" : $this->assetUrl,
            'combine' => Arr::get($this->attributes, 'combine') === 'true',
        ];

        $asset['ext'] = Arr::get($this->attributes, 'ext') ? Arr::get($this->attributes, 'ext') : pathinfo(parse_url($this->assetUrl, PHP_URL_PATH), PATHINFO_EXTENSION);

        if ($asset['combine'] && preg_match('#^(?:https?:|)//cdn.givecloud.co/(?:gh|npm)#', $asset['url'])) {
            $asset['url'] = preg_replace('#^(?:https?:|)//cdn.givecloud.co/(.*)$#', '$1', $asset['url']);
        } else {
            $asset['combine'] = false;
        }

        if ($asset['ext'] === 'css' || $asset['ext'] === 'scss') {
            $asset['footer'] = Arr::get($this->attributes, 'footer', 'false') === 'true';

            $context->registers['assets']['css'][] = $asset;
        } elseif ($asset['ext'] === 'js') {
            $asset['type'] = 'text/javascript';
            $asset['async'] = Arr::get($this->attributes, 'async') === 'true';
            $asset['babel'] = Arr::get($this->attributes, 'babel') === 'true';
            $asset['defer'] = Arr::get($this->attributes, 'defer') === 'true';
            $asset['footer'] = Arr::get($this->attributes, 'footer', 'true') === 'true';

            if ($asset['babel']) {
                $asset['type'] = 'text/babel';
            }

            $context->registers['assets']['js'][] = $asset;
        }

        return '';
    }
}
