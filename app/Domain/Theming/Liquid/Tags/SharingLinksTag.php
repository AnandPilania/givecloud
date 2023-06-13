<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\FileSystem;
use Thunder\Shortcode\Shortcode\Shortcode;

/**
 * Outputs sharing links.
 *
 * Example:
 *
 *     {% sharing_links description: "This is the most amazing post!" %}
 */
class SharingLinksTag extends AbstractTag
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
        $this->extractAttributes($markup);
    }

    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        $attributes = array_map(function ($key) use ($context) {
            $value = $context->get($key);

            return is_array($value) ? implode(',', $value) : (string) $value;
        }, $this->attributes);

        $shareLinksHandler = app('shortcodes')->get('sharing_links');

        return (string) optional($shareLinksHandler)->handle(
            new Shortcode('sharing_links', $attributes, '')
        );
    }
}
