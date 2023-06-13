<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Ds\Domain\Theming\Liquid\LocalFileSystem;
use Ds\Domain\Theming\Liquid\ThemeFileSystem;
use Liquid\FileSystem;
use Liquid\Tag\TagInclude as LiquidTagInclude;

class SectionTag extends LiquidTagInclude
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
            $fileSystem = new LocalFileSystem($fileSystem->getPath('sections/'));
        } elseif ($fileSystem instanceof ThemeFileSystem) {
            $fileSystem = new ThemeFileSystem('sections/', $fileSystem->getTheme());
        } else {
            $fileSystem = new ThemeFileSystem('sections/');
        }

        parent::__construct($markup, $tokens, $fileSystem);
    }
}
