<?php

namespace Ds\Domain\Shared\Services;

use Ds\Domain\Shared\DateTimePeriodable;
use Ds\Domain\Shared\DateTimePeriodableAggregate;
use Illuminate\Support\Collection;

class DateTimePeriodableMerger
{
    /** @var \Illuminate\Support\Collection */
    protected $aggregates;

    public function __construct()
    {
        $this->aggregates = new Collection();
    }

    public function merge(iterable $periodables): self
    {
        foreach ($periodables as $periodable) {
            $this->mergePeriodable($periodable);
        }

        return $this;
    }

    public function mergePeriodable(DateTimePeriodable $periodable): self
    {
        $aggregate = $this->aggregates->first(function (DateTimePeriodableAggregate $aggregate) use ($periodable) {
            return $aggregate->canMergeWith($periodable);
        });

        if (empty($aggregate)) {
            $aggregate = new DateTimePeriodableAggregate;
            $this->aggregates->add($aggregate);
        }

        $aggregate->merge($periodable);

        return $this;
    }

    public function getAggregates(): Collection
    {
        return $this->aggregates;
    }
}
