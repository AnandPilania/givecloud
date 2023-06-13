<?php

namespace Tests\Concerns;

use Ds\Domain\Shared\DateTime;
use Ds\Domain\Shared\DateTimePeriodable;
use Ds\Models\Traits\HasNullableDateTimePeriod;
use Illuminate\Support\Str;

trait InteractsWithDateTimePeriodable
{
    /**
     * @param mixed $start
     * @param mixed $end
     * @return \Ds\Domain\Shared\DateTimePeriodable
     */
    protected function createPeriodable($start = null, $end = null): DateTimePeriodable
    {
        $start = $this->createDateTimeForPeriodable($start);
        $end = $this->createDateTimeForPeriodable($end);

        return new class($start, $end) implements DateTimePeriodable {
            use HasNullableDateTimePeriod;

            public function __construct($start, $end)
            {
                $this->start_date = $start;
                $this->end_date = $end;
            }

            public function getKey()
            {
                return Str::random(12);
            }
        };
    }

    /**
     * @param mixed $time
     * @return \Ds\Domain\Shared\DateTime|null
     */
    protected function createDateTimeForPeriodable($time = null): ?DateTime
    {
        return optional(fromUtc($time))->setTime(0, 0);
    }
}
