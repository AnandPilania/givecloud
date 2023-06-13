<?php

namespace Ds\Domain\Theming\Liquid;

use Ds\Domain\Theming\Theme;

class Filters
{
    /** @var \Ds\Domain\Theming\Theme */
    protected $themeService;

    public function __construct(Theme $themeService)
    {
        $this->themeService = $themeService;
    }
}
