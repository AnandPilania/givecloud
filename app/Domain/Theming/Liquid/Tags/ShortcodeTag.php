<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Regexp;
use Thunder\Shortcode\Shortcode\Shortcode;

/**
 * Enqueues Google Fonts for loading.
 *
 * Example:
 *
 *     {% shortcode "posts", id: 1 %}
 */
class ShortcodeTag extends AbstractTag
{
    /** @var string */
    private $shortcodeName;

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
            $this->shortcodeName = $syntax->matches[0];
        } else {
            throw new ParseException("Syntax Error in 'shortcode' - Valid syntax: shortcode [name]");
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
        $name = $context->get($this->shortcodeName);

        $handler = app('shortcodes')->get($name);

        if (empty($handler)) {
            return '';
        }

        $parameters = $this->resolveParameters($context, $this->attributes);

        return (string) $handler(new Shortcode($name, $parameters, ''));
    }

    private function resolveParameters(Context $context, array $parameters): array
    {
        return array_map(function ($name) use ($context) {
            $value = $context->get($name);

            return is_iterable($value)
                ? collect($value)->join(',')
                : nullable_cast('string', $value);
        }, $parameters);
    }
}
