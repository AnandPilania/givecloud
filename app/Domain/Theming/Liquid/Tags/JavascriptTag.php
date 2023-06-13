<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\Context;
use Liquid\Tag\TagRaw;

class JavascriptTag extends TagRaw
{
    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        $context->registers['javascript'] = trim(
            $context->registers['javascript'] .
            PHP_EOL .
            PHP_EOL .
            parent::render($context)
        );

        return '';
    }
}
