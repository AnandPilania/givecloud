<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('c')) {
            return $query;
        }

        switch (request('c')) {
            case '0':
                $query->incomplete();
                break;
            case '1':
                $query->complete();
                break;
            case '2':
                $query->where('total_refunded', '>', 0);
                break;
            case '3':
                $query->unsynced();
                break;
            case '4':
                $query->where('payment_status', 'succeeded');
                break;
            case '5':
                $query->where('payment_status', 'pending');
                break;
            case '6':
                $query->where('payment_status', 'failed');
                break;
            case '7':
                $query->whereIn('payment_status', ['succeeded', 'pending']);
        }

        return $query;
    }
}
