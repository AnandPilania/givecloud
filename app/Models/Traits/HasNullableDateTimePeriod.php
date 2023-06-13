<?php

namespace Ds\Models\Traits;

use Ds\Domain\Shared\DateTime;
use Ds\Domain\Shared\DateTimePeriod;
use Ds\Domain\Shared\NullableDateTimePeriod;

trait HasNullableDateTimePeriod
{
    public function toDateTimePeriod(): DateTimePeriod
    {
        return $this->toNullableDateTimePeriod();
    }

    public function toNullableDateTimePeriod(): NullableDateTimePeriod
    {
        return new NullableDateTimePeriod(
            $this->getPeriodStartDate(),
            $this->getPeriodEndDate()
        );
    }

    public function getPeriodStartDate(): ?DateTime
    {
        return $this->start_date;
    }

    public function getPeriodEndDate(): ?DateTime
    {
        return $this->end_date ?? null;
    }
}
