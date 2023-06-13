<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class MembershipFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('membership')) {
            return $query;
        }

        return $query->whereHas('item', function (Builder $query) {
            $query->whereHas('variant', function (Builder $query) {
                $query->whereNotNull('membership_id')
                    ->when(request('membership') !== '*', function (Builder $query) {
                        $query->whereIn('membership_id', explode(',', request('membership')));
                    });
            });
        });
    }
}
