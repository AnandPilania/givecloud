<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class RefundDrop extends Drop
{
    /**
     * @param \Ds\Models\Refund $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'type' => $source->type,
            'status' => $source->status,
            'amount' => $source->amount,
            'currency' => new CurrencyDrop(currency($source->currency)),
            'reference_number' => $source->reference_number,
            'refunded_at' => $source->created_at,
            'reason' => $source->description,
            'failure_reason' => $source->failure_code,
        ];
    }
}
