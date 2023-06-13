<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class GroupAccountDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'group_id' => $source->group_id,
            'account_id' => $source->account_id,
            'source' => $source->source,
            'start_date' => $source->start_date,
            'end_date' => $source->end_date,
            'end_reason' => $source->end_reason,
            'is_active' => $source->is_active,
            'is_expired' => $source->is_expired,
            'days_left' => $source->days_left,
        ];
    }

    public function group()
    {
        return $this->source->group;
    }

    public function show_in_profile()
    {
        return $this->source->group->show_in_profile ?? false;
    }
}
