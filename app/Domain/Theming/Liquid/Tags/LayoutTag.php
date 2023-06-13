<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Extends a template by another one.
 *
 * Example:
 *
 *     {% layout "theme" %}
 */
class LayoutTag extends AbstractTag
{
    /** @var string The name of the layout */
    private $layoutName;

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
        $regex = new Regexp('/("[^"]+"|\'[^\']+\'|none)?/');

        if ($regex->match($markup) && isset($regex->matches[1])) {
            if ($regex->matches[1] === 'none') {
                $this->layoutName = '';
            } else {
                $this->layoutName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
            }
        } else {
            throw new ParseException("Error in tag 'layout' - Valid syntax: layout '[layout name]'");
        }

        parent::__construct($markup, $tokens, $fileSystem);
    }

    /**
     * Get the name of the layout.
     *
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }

    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        return '';
    }
}
