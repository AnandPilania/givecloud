<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PledgeDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'pledge_number' => $source->hashid,
            'name' => $source->campaign->name,
            'start_date' => $source->campaign->start_date,
            'end_date' => $source->campaign->end_date,
            'funded_amount' => $source->funded_amount,
            'funded_percent' => $source->funded_percent,
            'funded_status' => $source->funded_status,
            'amount' => $source->total_amount,
            'currency' => currency($source->currency_code),
        ];
    }
}
