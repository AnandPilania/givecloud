<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class ReferralFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fR')) {
            return $query;
        }

        $query->where(function ($query) {
            $query->whereIn('contributions.referral_source', request('fR'));

            if (in_array('Other', request('fR'), true)) {
                $query->orWhere(function ($query) {
                    $query->whereNotNull('contributions.referral_source');
                    $query->whereNotIn('contributions.referral_source', explode(',', sys_get('referral_sources_options')));
                });
            }
        });

        return $query;
    }
}
