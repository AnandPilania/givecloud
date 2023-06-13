<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class GiftAidFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('gift_aid')) {
            return $query;
        }

        return $query->where('productorderitem.gift_aid', request('gift_aid'));
    }
}
