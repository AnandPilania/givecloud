<?php

namespace Ds\Domain\Shared;

use DateTimeImmutable;

class NullableDateTimePeriod extends DateTimePeriod
{
    /** @var bool */
    protected $hasNullStart = false;

    /** @var bool */
    protected $hasNullEnd = false;

    public function __construct(?DateTime $start, ?DateTime $end)
    {
        if (is_null($start)) {
            $start = now()->subMillennium(5);
            $this->hasNullStart = true;
        }

        if (is_null($end)) {
            $end = now()->addMillennium(5);
            $this->hasNullEnd = true;
        }

        parent::__construct(
            DateTimeImmutable::createFromMutable($start),
            DateTimeImmutable::createFromMutable($end)
        );
    }

    public function mergeInto(DateTimePeriod $period): DateTimePeriod
    {
        if ($period instanceof NullableDateTimePeriod) {
            $this->hasNullStart = $this->hasNullStart || $period->hasNullStart;
            $this->hasNullEnd = $this->hasNullEnd || $period->hasNullEnd;
        }

        return parent::mergeInto($period);
    }

    public function getNullableStart(): ?DateTimeImmutable
    {
        if ($this->hasNullStart) {
            return null;
        }

        return $this->getStart();
    }

    public function getNullableEnd(): ?DateTimeImmutable
    {
        if ($this->hasNullEnd) {
            return null;
        }

        return $this->getEnd();
    }
}
