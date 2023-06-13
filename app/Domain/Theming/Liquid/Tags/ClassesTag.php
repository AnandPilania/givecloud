<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\Exception\RenderException;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Regexp;

/**
 * Generates a class string based on the provided expressions.
 *
 * Example:
 *
 *     {% classes active: product.id == 2, has-photo: product.has_photo %}
 */
class ClassesTag extends AbstractTag
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
     * Extracts tag attributes from a markup string.
     *
     * @param string $markup
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = [];

        $attributeRegexp = Liquid::get('QUOTED_STRING') . '|(?:[^,\|\'"]|' . Liquid::get('QUOTED_STRING') . ')+';
        $attributeRegexp = '/(' . Liquid::get('QUOTED_FRAGMENT') . ')\s*\:\s*(' . $attributeRegexp . '),?/';

        $attributeRegexp = new Regexp($attributeRegexp);

        $matches = $attributeRegexp->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $match[1];
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
        $classes = [];

        $logicalRegex = new Regexp('/\s+(and|or)\s+/');
        $conditionalRegex = new Regexp('/(' . Liquid::get('QUOTED_FRAGMENT') . ')\s*([=!<>a-z_]+)?\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')?/');

        foreach ($this->attributes as $klass => $markup) {
            $conditions = [];

            $logicalRegex->matchAll($markup);
            $logicalOperators = $logicalRegex->matches;
            $logicalOperators = $logicalOperators[1];

            foreach ($logicalRegex->split($markup) as $condition) {
                if ($conditionalRegex->match($condition)) {
                    $conditions[] = [
                        'left' => $conditionalRegex->matches[1] ?? null,
                        'operator' => $conditionalRegex->matches[2] ?? null,
                        'right' => $conditionalRegex->matches[3] ?? null,
                    ];
                } else {
                    throw new ParseException("Syntax Error in tag 'classes' - Valid syntax: classes class_name: [condition]");
                }
            }

            $display = $this->interpretCondition(
                $conditions[0]['left'],
                $conditions[0]['right'],
                $conditions[0]['operator'],
                $context
            );

            foreach ($logicalOperators as $index => $logicalOperator) {
                $result = $this->interpretCondition(
                    $conditions[$index + 1]['left'],
                    $conditions[$index + 1]['right'],
                    $conditions[$index + 1]['operator'],
                    $context
                );

                $display = $logicalOperator == 'and' ? ($display && $result) : ($display || $result);
            }

            if ($display) {
                $classes[] = $klass;
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Returns a string value of an array for comparisons.
     *
     * @param mixed $value
     * @return string
     *
     * @throws \Liquid\Exception\RenderException
     */
    private function stringValue($value)
    {
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                // toLiquid is handled in Context::variable
                $class = get_class($value);
                throw new RenderException("Value of type $class has no `toLiquid` nor `__toString` methods");
            }
        }

        return $value;
    }

    /**
     * Check to see if to variables are equal in a given context.
     *
     * @param string $left
     * @param string $right
     * @param \Liquid\Context $context
     * @return bool
     */
    protected function equalVariables($left, $right, Context $context)
    {
        $left = $this->stringValue($context->get($left));
        $right = $this->stringValue($context->get($right));

        return $left == $right;
    }

    /**
     * Interpret a comparison
     *
     * @param string $left
     * @param string $right
     * @param string $op
     * @param \Liquid\Context $context
     * @return bool
     *
     * @throws \Liquid\Exception\RenderException
     */
    protected function interpretCondition($left, $right, $op, Context $context)
    {
        if (is_null($op)) {
            return (bool) $this->stringValue($context->get($left));
        }

        // values of 'empty' have a special meaning in array comparisons
        if ($right == 'empty' && is_array($context->get($left))) {
            $left = count($context->get($left));
            $right = 0;
        } elseif ($left == 'empty' && is_array($context->get($right))) {
            $right = count($context->get($right));
            $left = 0;
        } else {
            $left = $this->stringValue($context->get($left));
            $right = $this->stringValue($context->get($right));
        }

        // special rules for null values
        if (is_null($left) || is_null($right)) {
            // null == null returns true
            if ($op == '==' && is_null($left) && is_null($right)) {
                return true;
            }

            // null != anything other than null return true
            if ($op == '!=' && (! is_null($left) || ! is_null($right))) {
                return true;
            }

            return false;
        }

        // regular rules
        switch ($op) {
            case '==': return $left == $right;
            case '!=': return $left != $right;
            case '>':  return $left > $right;
            case '<':  return $left < $right;
            case '>=': return $left >= $right;
            case '<=': return $left <= $right;
            case 'contains':
                return is_array($left) ? in_array($right, $left) : (strpos($left, $right) !== false);
        }

        throw new RenderException("Error in tag '" . $this->name() . "' - Unknown operator $op");
    }
}
