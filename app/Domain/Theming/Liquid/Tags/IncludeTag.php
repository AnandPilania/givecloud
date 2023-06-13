<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Ds\Domain\Theming\Liquid\LocalFileSystem;
use Ds\Domain\Theming\Liquid\ThemeFileSystem;
use Liquid\Context;
use Liquid\FileSystem;
use Liquid\Tag\TagInclude as LiquidTagInclude;

class IncludeTag extends LiquidTagInclude
{
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
        if ($fileSystem instanceof LocalFileSystem) {
            $fileSystem = new LocalFileSystem($fileSystem->getPath('snippets/'));
        } elseif ($fileSystem instanceof ThemeFileSystem) {
            $fileSystem = new ThemeFileSystem('snippets/', $fileSystem->getTheme());
        }

        parent::__construct($markup, $tokens, $fileSystem);
    }

    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        $output = parent::render($context);

        if (isset($this->attributes['trim'])) {
            return trim($output);
        }

        return $output;
    }
}
