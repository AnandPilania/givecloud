<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Drop;

class CartDrop extends Drop
{
    protected $mutators = [
        'items',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->client_uuid,
            'currency' => new Currency($source->currency_code),
            'original_total_price' => $source->totalamount + $source->total_savings,
            'total_discount' => $source->total_savings,
            'total_price' => $source->totalamount,
            'total_weight' => $source->total_weight,
            'comments' => $source->comments,
        ];
    }

    public function item_count()
    {
        return ($this->source) ? $this->source->items()->count() : 0;
    }

    public function recurring_items()
    {
        return $this->source->recurringItems;
    }
}
