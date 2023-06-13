<?php

namespace Tests\Unit\Services;

use Ds\Services\StatsService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** @group dashboard */
class StatsServiceTest extends TestCase
{
    /** @dataProvider periodStartDataProvider */
    public function testGetPeriodsReturnsPeriod(
        string $start,
        string $expectedStartPeriod,
        string $expectedEndPeriod,
        string $expectedStartPrevious,
        string $expectedEndPrevious
    ): void {
        Carbon::setTestNow(fromLocal('2022-09-19 13:47:02'));

        $periods = $this->app->make(StatsService::class)->periodsForDate(toUtc(fromLocal($start)));

        $this->assertEquals($expectedStartPeriod, data_get($periods, 'current.0')->toDateTimeString());
        $this->assertEquals($expectedEndPeriod, data_get($periods, 'current.1')->toDateTimeString());
        $this->assertEquals($expectedStartPrevious, data_get($periods, 'previous.0')->toDateTimeString());
        $this->assertEquals($expectedEndPrevious, data_get($periods, 'previous.1')->toDateTimeString());

        Carbon::setTestNow(false);
    }

    public function periodStartDataProvider(): array
    {
        return [
            ['now', '2022-09-01 04:00:00', '2022-09-19 17:47:02', '2022-08-01 04:00:00', '2022-08-19 17:47:02'],
            ['September 2022', '2022-09-01 04:00:00', '2022-09-19 17:47:02', '2022-08-01 04:00:00', '2022-08-19 17:47:02'],
            ['January 2022', '2022-01-01 05:00:00', '2022-02-01 04:59:59', '2021-12-01 05:00:00', '2022-01-01 04:59:59'],
            ['February 2022', '2022-02-01 05:00:00', '2022-03-01 04:59:59', '2022-01-01 05:00:00', '2022-02-01 04:59:59'],
            ['March 2022', '2022-03-01 05:00:00', '2022-04-01 03:59:59', '2022-02-01 05:00:00', '2022-03-01 04:59:59'],
        ];
    }

    /** @dataProvider differenceDataProvider */
    public function testDifferenceReturnsDifference(int $current, int $initial, ?int $expected): void
    {
        $diff = $this->app->make(StatsService::class)->difference($current, $initial);

        $this->assertEquals($expected, $diff);
    }

    public function differenceDataProvider(): array
    {
        return [
            [2, 1, 100],
            [2, 0, null],
            [0, 2, -100],
            [1, 2, -50],
        ];
    }
}
