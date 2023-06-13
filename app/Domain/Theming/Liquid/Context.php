<?php

namespace Ds\Domain\Theming\Liquid;

use Liquid\Context as LiquidContext;

class Context extends LiquidContext
{
    /** @var string */
    protected $layout = '';

    /**
     * Get the layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the layout.
     *
     * @param string $layout
     */
    public function setLayout($layout = '')
    {
        $this->layout = $layout;
    }
}
