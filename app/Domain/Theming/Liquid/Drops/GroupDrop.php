<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class GroupDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'sequence' => $source->sequence,
            'name' => $source->public_name ?: $source->name,
            'description' => $source->public_description,
            'default_url' => $source->default_url,
            'renewal_url' => $source->renewal_url,
            'can_opt_in' => $source->members_can_manage_optin,
            'can_opt_out' => $source->members_can_manage_optout,
            'show_in_profile' => $source->show_in_profile,
        ];
    }
}
