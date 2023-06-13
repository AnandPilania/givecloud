<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Template;
use Liquid\Context;
use Throwable;

class ContentForLayoutDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /** @var \Liquid\Context */
    protected $context;

    /** @var \Ds\Domain\Theming\Liquid\Template */
    protected $template;

    /**
     * Create an instance.
     *
     * @param \Liquid\Context $context
     */
    public function __construct(Context $context, Template $template)
    {
        $this->context = $context;
        $this->template = $template;
    }

    /**
     * Output content for the header.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->template->getRoot()->render($this->context);
        } catch (Throwable $e) {
            report($e);
        }

        return '';
    }
}
