<?php

namespace Ds\Domain\Theming\Liquid;

interface Liquidable
{
    /**
     * Get the instance as Liquid representation of object.
     *
     * @return \Ds\Domain\Theming\Liquid\Drop|array
     */
    public function toLiquid();
}
