<?php

namespace Ds\Domain\Theming\Liquid\Tags;

use Liquid\Context;
use Liquid\Tag\TagRaw;

class StylesheetTag extends TagRaw
{
    /**
     * Renders the node
     *
     * @param \Liquid\Context $context
     * @return string
     */
    public function render(Context $context)
    {
        $context->registers['stylesheet'] = trim(
            $context->registers['stylesheet'] .
            PHP_EOL .
            PHP_EOL .
            parent::render($context)
        );

        return '';
    }
}
