<?php

namespace Tests\Unit\Domain\Shared\Services;

use Ds\Domain\Shared\Services\DateTimePeriodableMerger;
use Tests\Concerns\InteractsWithDateTimePeriodable;
use Tests\TestCase;

class DateTimePeriodableMergerTest extends TestCase
{
    use InteractsWithDateTimePeriodable;

    public function testPeriodablesAreMerged(): void
    {
        $earliest = $this->createDateTimeForPeriodable('last month');
        $latest = $this->createDateTimeForPeriodable('+3month');

        $aggregates = (new DateTimePeriodableMerger)->merge([
            $this->createPeriodable($earliest, 'now'),
            $this->createPeriodable('now', '+1month'),
            $this->createPeriodable('+1month', $latest),
        ])->getAggregates();

        $this->assertEquals($earliest, $aggregates->first()->getStart());
        $this->assertEquals($latest, $aggregates->first()->getEnd());
    }

    public function testUnsortedPeriodablesAreMerged(): void
    {
        $earliest = $this->createDateTimeForPeriodable('last month');
        $latest = $this->createDateTimeForPeriodable('+3month');

        $aggregates = (new DateTimePeriodableMerger)->merge([
            $this->createPeriodable('+1month', $latest),
            $this->createPeriodable('now', '+1month'),
            $this->createPeriodable($earliest, 'now'),
        ])->getAggregates();

        $this->assertEquals($earliest, $aggregates->first()->getStart());
        $this->assertEquals($latest, $aggregates->first()->getEnd());
    }

    public function testSomePeriodablesAreMerged(): void
    {
        $earliest = $this->createDateTimeForPeriodable('last month');
        $latest = $this->createDateTimeForPeriodable('+1month');

        $aggregates = (new DateTimePeriodableMerger)->merge([
            $this->createPeriodable($earliest, 'now'),
            $this->createPeriodable('+3month', '+4month'),
            $this->createPeriodable('now', $latest),
        ])->getAggregates();

        $this->assertCount(2, $aggregates);
        $this->assertEquals($earliest, $aggregates->first()->getStart());
        $this->assertEquals($latest, $aggregates->first()->getEnd());
    }
}
