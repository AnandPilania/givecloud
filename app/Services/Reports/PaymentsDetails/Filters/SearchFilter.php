<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('search')) {
            return $query;
        }

        // Groups all 'ors' together
        $query->where(function (Builder $query) {
            // Order number
            $query->whereHas('order', function (Builder $query) {
                $query->where('invoicenumber', 'LIKE', '%' . request('search') . '%');
            });

            // RPP Profile
            $query->orWhere('profile_id', 'LIKE', '%' . request('search') . '%');

            // Supporter name
            $query->orWhereHas('supporter', function (Builder $query) {
                $query->where('display_name', 'LIKE', '%' . request('search') . '%');
            });

            // Sponsorship name
            $query->orWhereHas('item', function (Builder $query) {
                $query->whereHas('sponsorship', function (Builder $query) {
                    $query->whereRaw('CONCAT(first_name, " " , last_name) LIKE ?', ['%' . request('search') . '%']);
                });
            });

            // Search by membership name
            $query->orWhereHas('item', function (Builder $query) {
                $query->whereHas('variant', function (Builder $query) {
                    $query->whereHas('membership', function (Builder $query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                });
            });

            // Search by product and variant name
            $query->orWhereHas('item', function (Builder $query) {
                $query->whereHas('variant', function (Builder $query) {
                    $query->whereHas('product', function (Builder $query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })->orWhere('variantname', 'LIKE', '%' . request('search') . '%');
                });
            });
        });

        return $query;
    }
}
