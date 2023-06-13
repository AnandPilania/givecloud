<?php

namespace Tests\Unit\Domain\Shared;

use Ds\Domain\Shared\DateTimePeriodableAggregate;
use Tests\Concerns\InteractsWithDateTimePeriodable;
use Tests\TestCase;

class DateTimePeriodableAggregateTest extends TestCase
{
    use InteractsWithDateTimePeriodable;

    public function testPeriodablesCanBeMerged(): void
    {
        $first = $this->createPeriodable('last month', 'now');
        $second = $this->createPeriodable('now', '+1month');

        $this->assertTrue(
            (new DateTimePeriodableAggregate($first))->canMergeWith($second)
        );
    }

    public function testPeriodablesCannotBeMerged(): void
    {
        $first = $this->createPeriodable('last month', 'now');
        $second = $this->createPeriodable('+1day', '+1month');

        $this->assertFalse(
            (new DateTimePeriodableAggregate($first))->canMergeWith($second)
        );
    }
}
