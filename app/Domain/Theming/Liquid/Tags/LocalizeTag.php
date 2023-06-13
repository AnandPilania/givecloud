<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Ds\Domain\Theming\Liquid\ThemeFileSystem;
use Illuminate\Support\Arr;
use Liquid\Context;
use Liquid\FileSystem;
use Liquid\Tag\TagRaw;

class LocalizeTag extends TagRaw
{
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
        parent::__construct($markup, $tokens, $fileSystem);

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
        $json = parent::render($context);
        $keys = @json_decode($json, true);

        if (is_array($keys)) {
            $localizations = array_combine($keys, $keys);

            $localizations = array_map(function ($key) {
                return Arr::get($this->themeService->getTranslationKeys(), $key, $key);
            }, $localizations);

            $context->registers['localizations'] = array_merge(
                $context->registers['localizations'],
                $localizations
            );
        }

        return '';
    }
}
