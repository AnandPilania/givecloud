<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\AbstractBlock;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Regexp;

class FormTag extends AbstractBlock
{
    /** @var string */
    protected $formName;

    /** @var \Liquid\Variable */
    protected $formVariable;

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

        $syntax = new Regexp('/(' . Liquid::get('QUOTED_STRING') . ')(?:,\s*(' . Liquid::get('VARIABLE_NAME') . ')|)/');

        if ($syntax->match($markup)) {
            $this->formName = substr($syntax->matches[1], 1, strlen($syntax->matches[1]) - 2);
            $this->formVariable = $syntax->matches[2] ?? null;
            $this->extractAttributes(str_replace($syntax->matches[0], '', $markup));
        } else {
            throw new ParseException("Syntax Error in 'form' - Valid syntax: form 'type'");
        }
    }

    /**
     * Extracts tag attributes from a markup string.
     *
     * @param string $markup
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = [];

        $attributeRegexp = new Regexp('/([\w\-]+)\s*\:\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')/');

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
        $output = parent::render($context);

        // match the indentation on the first line of output
        preg_match('/^\n(\s*)/m', $output, $indent);
        $indent = $indent[1] ?? '';

        $attributes = collect($this->getFormAttributes($context))
            ->map(function ($value, $key) {
                return sprintf('%1$s="%2$s"', $key, e($value));
            })->implode(' ');

        $inputs = csrf_field();

        return "<form {$attributes}>\n{$indent}{$inputs}{$output}</form>";
    }

    /**
     * Retrieve the form attributes.
     *
     * @param \Liquid\Context $context
     * @return array
     */
    protected function getFormAttributes(Context $context)
    {
        if ($this->formVariable) {
            $variable = $context->get($this->formVariable);
        } else {
            $variable = null;
        }

        if ($this->formName === 'customer_login') {
            $attributes = [
                'action' => secure_site_url('account/login'),
                'method' => 'post',
            ];
        } elseif ($this->formName === 'edit_fundraiser') {
            $attributes = [
                'action' => $variable
                    ? secure_site_url("{$variable->permalink}/update")
                    : secure_site_url('fundraisers/insert'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ];
        } elseif ($this->formName === 'cancel_fundraiser') {
            $attributes = [
                'action' => secure_site_url("{$variable->permalink}/cancel"),
                'method' => 'post',
            ];
        } else {
            $attributes = [];
        }

        foreach ($this->attributes as $key => $value) {
            $attributes[$key] = $context->get($value);
        }

        return $attributes;
    }
}
