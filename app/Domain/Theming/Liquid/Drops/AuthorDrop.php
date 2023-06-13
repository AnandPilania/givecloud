<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class AuthorDrop extends Drop
{
    protected function initialize($source)
    {
        if (is_a($source, '\Ds\Models\Member')) {
            $this->liquid = [
                'name' => $source->display_name,
                'location' => ($source->bill_country) ? app('iso3166')->expandAbbr((($source->bill_state) ? $source->bill_state . ', ' : '') . $source->bill_country) : null,
                'avatar' => null,
            ];
        } elseif (is_a($source, '\Ds\Models\User')) {
            $this->liquid = [
                'name' => $source->full_name,
                'location' => null,
                'avatar' => null,
            ];
        }
    }
}
