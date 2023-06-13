<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class RiskFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('rsk')) {
            return $query;
        }

        switch (request('rsk')) {
            case '0':
                $query->withSpam();
                break;
            case '1':
                $query->onlySpam();
                break;
            case '2':
                $query->withWarnings();
                break;
        }

        return $query;
    }
}
