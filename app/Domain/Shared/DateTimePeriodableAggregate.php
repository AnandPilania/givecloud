<?php

namespace Ds\Domain\Shared;

use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DateTimePeriodableAggregate
{
    /** @var \DateTime|null */
    protected $start;

    /** @var \DateTime|null */
    protected $end;

    /** @var \Illuminate\Support\Collection */
    protected $periodables;

    public function __construct(DateTimePeriodable $periodable = null)
    {
        $this->periodables = new Collection;

        if ($periodable) {
            $this->merge($periodable);
        }
    }

    public function getStart(): ?DateTimeImmutable
    {
        return $this->start ? DateTimeImmutable::createFromMutable($this->start) : null;
    }

    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end ? DateTimeImmutable::createFromMutable($this->end) : null;
    }

    public function getPeriodables(): Collection
    {
        return $this->periodables;
    }

    public function toDateTimePeriod(): DateTimePeriod
    {
        // in the case of a NULL start or end date we adjust the
        // the corresponding date by 5 millenniums in order to fake a start/end date
        return new DateTimePeriod(
            $this->getStart() ?? now()->subMillennium(5)->toImmutable(),
            $this->getEnd() ?? now()->addMillennium(5)->toImmutable()
        );
    }

    public function merge(DateTimePeriodable $periodable): self
    {
        if ($this->canMergeWith($periodable)) {
            return $this->add($periodable);
        }

        throw new InvalidArgumentException('Unable to merge aggregate and periodable');
    }

    public function canMergeWith(DateTimePeriodable $periodable): bool
    {
        if ($this->periodables->isEmpty()) {
            return true;
        }

        return ! $this->toDateTimePeriod()->precedes($periodable->toDateTimePeriod())
            && ! $this->toDateTimePeriod()->precededBy($periodable->toDateTimePeriod());
    }

    protected function add(DateTimePeriodable $periodable): self
    {
        $this->periodables[] = $periodable;

        return $this->setStartAndEnd();
    }

    protected function setStartAndEnd(): self
    {
        $this->start = $this->periodables->reduce(function (?DateTime $start, DateTimePeriodable $periodable, $key) {
            $periodStartDate = $periodable->getPeriodStartDate();

            return $key ? min($start, $periodStartDate) : $periodStartDate;
        });

        $this->end = $this->periodables->reduce(function (?DateTime $end, DateTimePeriodable $periodable, $key) {
            $periodEndDate = $periodable->getPeriodEndDate();

            return ($periodEndDate === null || ($key && $end === null)) ? null : max($end, $periodEndDate);
        });

        return $this;
    }
}
