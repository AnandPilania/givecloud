<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class PaymentMethodFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fp')) {
            return $query;
        }

        $query->where(function (Builder $q) {
            $q->orWhereIn('payment_card_brand', request('fp'))
                ->orWhereIn('payment_type', request('fp'));

            if (in_array('Other', request('fp'), true)) {
                $q->orWhere('payment_type', 'unknown');
            }

            if (in_array('ACH', request('fp'), true)) {
                $q->orWhere('payment_type', 'bank');
            }

            if (in_array('Check', request('fp'), true)) {
                $q->orWhere('payment_type', 'cheque');
            }

            if (in_array('Google Pay', request('fp'), true)) {
                $q->orWhere('payment_card_wallet', 'google_pay');
            }

            if (in_array('Apple Pay', request('fp'), true)) {
                $q->orWhere('payment_card_wallet', 'apple_pay');
            }
        });

        return $query;
    }
}
