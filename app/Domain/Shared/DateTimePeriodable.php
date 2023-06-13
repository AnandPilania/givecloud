<?php

namespace Ds\Domain\Shared;

interface DateTimePeriodable
{
    /**
     * @return mixed
     */
    public function getKey();

    public function toDateTimePeriod(): DateTimePeriod;

    public function getPeriodStartDate(): ?DateTime;

    public function getPeriodEndDate(): ?DateTime;
}
