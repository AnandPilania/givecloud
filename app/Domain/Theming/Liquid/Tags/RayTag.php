<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Regexp;

/**
 * Any variable(s) that passed will be displayed in ray.
 *
 * Example:
 *
 *     {% ray variable %}
 */
class RayTag extends AbstractTag
{
    /** @var string */
    private $methodOrVariableName;

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
        $syntax = new Regexp('/(' . Liquid::get('QUOTED_STRING') . '|' . Liquid::get('VARIABLE_NAME') . ')/');

        if ($syntax->match($markup)) {
            $this->methodOrVariableName = $syntax->matches[0];
        } else {
            throw new ParseException("Syntax Error in 'ray' - Valid syntax: ray [method|variable]");
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
        if (! function_exists('ray')) {
            return '';
        }

        $methods = [
            'caller',
            'clearAll',
            'clearScreen',
            'count',
            'measure',
            'newScreen',
            'pause',
            'showQueries',
            'trace',
        ];

        $methodName = trim($this->methodOrVariableName, '\'"');

        if (in_array($methodName, $methods, true)) {
            ray()->{$methodName}(); // phpcs:ignore
        } else {
            ray($context->get($this->methodOrVariableName)); // phpcs:ignore
        }

        return '';
    }
}
