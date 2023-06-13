<?php

namespace Ds\Illuminate\Database\Eloquent\Relations;

use DateTimeInterface;
use Ds\Domain\Shared\Date;
use Illuminate\Database\Eloquent\Relations\Pivot as EloquentPivot;

abstract class Pivot extends EloquentPivot
{
    /**
     * Return a timestamp as Date object with time set to 00:00:00.
     *
     * @param mixed $value
     * @return \Ds\Domain\Shared\Date
     */
    protected function asDate($value)
    {
        return Date::instance($this->asDateTime($value));
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        if ($date instanceof Date) {
            return $date->toDateFormat();
        }

        return $date->format($this->getDateFormat());
    }
}
