<?php

namespace Ds\Domain\Analytics\Builders;

use Ds\Domain\Shared\DateTime;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;

abstract class Builder
{
    use Conditionable;

    /** @var bool */
    protected $applyGroupBy = true;

    /** @var int */
    protected $periodColumn = 'created_at';

    /** @var array|null */
    protected $periodDateRange = null;

    /** @var string */
    protected $periodType = 'days';

    /**
     * @return static
     */
    public function setDateRange(DateTime $startDate, DateTime $endDate): self
    {
        $this->periodDateRange = [$startDate, $endDate];

        return $this;
    }

    /**
     * @return static
     */
    public function setPeriodType(?string $periodType = null): self
    {
        $validPeriodType = preg_match('/^(days|months)$/', (string) $periodType);

        $this->periodType = $validPeriodType ? $periodType : 'days';

        return $this;
    }

    abstract public function getBuilder(): QueryBuilder;

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     */
    protected function applyBuilder($query)
    {
        $valueMapping = [
            'days' => "DATE({$this->periodColumn})",
            'months' => "DATE_FORMAT({$this->periodColumn}, '%Y-%m')",
        ];

        $query->selectRaw($valueMapping[$this->periodType] . ' as period');

        if ($this->applyGroupBy) {
            $query->groupByRaw($valueMapping[$this->periodType]);
        }

        if ($this->periodDateRange) {
            $query->whereBetween($this->periodColumn, $this->periodDateRange);
        }

        return $query;
    }

    public function get(): Collection
    {
        return $this->getBuilder()
            ->get()
            ->map(function ($item) {
                return (object) collect((array) $item)
                    ->map(function ($value) {
                        return is_numeric($value) ? (float) $value : $value;
                    })->all();
            })->keyBy('period');
    }
}
