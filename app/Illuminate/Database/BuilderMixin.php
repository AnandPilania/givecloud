<?php

namespace Ds\Illuminate\Database;

use Illuminate\Support\Facades\Cache;

/** @mixin \Illuminate\Database\Query\Builder */
class BuilderMixin
{
    /**
     * Reset ORDER BY clauses.
     */
    public function orderByReset()
    {
        return function () {
            $this->{$this->unions ? 'unionOrders' : 'orders'} = null;

            return $this;
        };
    }

    public function orderBySet()
    {
        return function ($column, array $values) {
            $this->orderByRaw(sprintf(
                "FIND_IN_SET(%s, '%s')",
                tbl($column),
                implode(',', array_map(fn ($value) => db_real_escape_string($value), $values)),
            ));

            return $this;
        };
    }

    /**
     * Return all distinct values stored in the database
     * for the given column.
     */
    public function getDistinctValuesOf()
    {
        return function ($column, $forceRefresh = false) {
            $key = $this->from . ':distinct-values:' . $column;

            if ($forceRefresh) {
                Cache::forget($key);
            }

            // using DISTINCT on tables like the order table can be costly as a result
            // we are going to cache the results to prevent hammering the database
            return Cache::remember($key, now()->addMinutes(5), function () use ($column) {
                return $this->select($column)
                    ->distinct()
                    ->whereNotNull($column)
                    ->pluck($column)
                    ->filter(function ($value) {
                        return (bool) trim($value);
                    })->sortBy(null, SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();
            });
        };
    }
}
