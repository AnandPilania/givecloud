<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class ItemTypeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fit')) {
            return $query;
        }

        $query->where(function ($q) {
            if (in_array('f', request('fit'), true)) {
                $q->orWhere('fundraising_items', '>', 0);
            }
            if (in_array('nf', request('fit'), true)) {
                $q->orWhere('fundraising_items', 0);
            }
            if (in_array('s', request('fit'), true)) {
                $q->orWhere('shippable_items', '>', 0);
            }
            if (in_array('ns', request('fit'), true)) {
                $q->orWhere('shippable_items', 0);
            }
            if (in_array('d', request('fit'), true)) {
                $q->orWhere('downloadable_items', '>', 0);
            }
            if (in_array('nd', request('fit'), true)) {
                $q->orWhere('downloadable_items', 0);
            }
            if (in_array('r', request('fit'), true)) {
                $q->orWhere('recurring_items', '>', 0);
            }
            if (in_array('nr', request('fit'), true)) {
                $q->orWhere('recurring_items', 0);
            }
            if (in_array('sp', request('fit'), true)) {
                $q->orWhere('sponsorship_items', '>', 0);
            }
            if (in_array('nsp', request('fit'), true)) {
                $q->orWhere('sponsorship_items', 0);
            }
        });

        return $query;
    }
}
