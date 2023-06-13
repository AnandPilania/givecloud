<?php

namespace Ds\Http\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filters\Filter;

class MatchAgainstQueryFilter implements Filter
{
    /** @var string */
    protected $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if ($value === null) {
            return $query;
        }

        // Surround values with *
        $value = preg_replace('/(\w+)/', '*${1}*', $value);

        return $this->buildMatchAgainstQuery($query, $value);
    }

    protected function buildMatchAgainstQuery(Builder $query, $values): Builder
    {
        $values = Arr::wrap($values);

        return $query->select()
            ->selectRaw(DB::raw("MATCH ( $this->column ) AGAINST ( ? IN BOOLEAN MODE ) score"), $values)
            ->whereRaw("MATCH ( $this->column ) AGAINST ( ? IN BOOLEAN MODE )", $values)
            ->orderByDesc('score');
    }
}
