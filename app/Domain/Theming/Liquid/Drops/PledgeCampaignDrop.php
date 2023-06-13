<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Repositories\PledgableTotalAmountRepository;

class PledgeCampaignDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'name' => $source->name,
            'start_date' => $source->start_date,
            'end_date' => $source->end_date,
            'total_count' => $source->total_count,
            'total_amount' => $source->total_amount,
            'funded_count' => $source->funded_count,
            'funded_amount' => $source->funded_amount,
            'funded_percent' => $source->funded_percent,
            'funded_status' => $source->funded_status,
        ];
    }

    public function pledgable_total_amount(): float
    {
        return app(PledgableTotalAmountRepository::class)->get($this->source);
    }
}
