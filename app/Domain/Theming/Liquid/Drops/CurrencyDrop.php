<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Drop;

class CurrencyDrop extends Drop
{
    /** @var \Ds\Domain\Commerce\Currency */
    protected $source;

    /**
     * Create an instance.
     */
    public function __construct(Currency $source)
    {
        $this->source = $source;
        $this->liquid = $source->toArray();
    }

    public function locale()
    {
        $locale = app()->getLocale();

        if (empty($this->source->countries)) {
            return $locale;
        }

        if (in_array(locale_get_region($locale), $this->source->countries, true)) {
            return $locale;
        }

        return locale_get_primary_language($locale) . '-' . $this->source->countries[0];
    }

    /**
     * Output as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->liquid['name'];
    }
}
