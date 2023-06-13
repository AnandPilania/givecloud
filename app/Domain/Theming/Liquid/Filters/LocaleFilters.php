<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Ds\Domain\Theming\Liquid\Filters;

class LocaleFilters extends Filters
{
    /**
     * Returns the corresponding string of translated text from the locale file.
     *
     * @param string $input
     * @param array $data
     * @return string
     */
    public function t($input, $data = null)
    {
        return $this->themeService->translate($input, is_array($data) ? $data : []);
    }
}
