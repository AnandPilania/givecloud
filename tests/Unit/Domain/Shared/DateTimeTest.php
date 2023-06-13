<?php

namespace Tests\Unit\Domain\Shared;

use DateTimeZone;
use Ds\Domain\Shared\Date;
use Ds\Domain\Shared\DateTime;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        DateTime::setTestNow(null);
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::useTestNow();
    }

    private static function useTestNow()
    {
        DateTime::setTestNow('2020-06-01 12:00:00');
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormatWithDateTime(string $expected, string $format, DateTime $dateTime): void
    {
        $this->assertSame($expected, $dateTime->format($format));
    }

    public function formatProvider(): array
    {
        static::useTestNow();

        $now = DateTime::now();
        $yesterday = DateTime::yesterday();
        $tomorrow = DateTime::tomorrow();
        $anotherDayThisYear = DateTime::now()->addMonths(1);
        $anotherYear = DateTime::now()->addYears(3);
        $anotherTimezone = DateTime::now()->setTimezone(new DateTimeZone('America/Vancouver'));

        return [
            ['2020-06-01', 'date:Y-m-d', $now],
            ['Today', 'auto', $now],
            ['3 hours ago', 'auto-diff', DateTime::now()->subHours(3)],
            ['Yesterday', 'auto', $yesterday],
            ['Yesterday', 'auto-diff', $yesterday],
            ['Tomorrow', 'auto', $tomorrow],
            ['Tomorrow', 'auto-diff', $tomorrow],
            ['Jul 1', 'auto', $anotherDayThisYear],
            ['Jul 1', 'auto-diff', $anotherDayThisYear],
            ['Jun 1, 2023', 'auto', $anotherYear],
            ['Jun 1, 2023', 'auto-diff', $anotherYear],
            ['2020-06-01T12:00:00Z', 'api', $now],
            ['Jun 1, 2020', 'fdate', $now],
            ['Jun 1, 2020 12:00pm', 'fdatetime', $now],
            ['2020-06-01', 'date', $now],
            ['2020-06-01 12:00:00', 'datetime', $now],
            ['1 day ago', 'humans', $yesterday],
            ['1d ago', 'humans-short', $yesterday],
            ['1 day', 'diff-days', $yesterday],
            ['2020-06-01 12:00:00 UTC', 'json', $now],
            ['01-Jun-20 12:00 PM', 'csv', $now],
            ['Monday', '%A', $now],
            ['2020-06-01T05:00:00-0700', 'api', $anotherTimezone],
        ];
    }

    /**
     * @dataProvider formatDateProvider
     */
    public function testFormatWithDate(string $expected, string $format, Date $date): void
    {
        $this->assertSame($expected, $date->format($format));
    }

    public function formatDateProvider(): array
    {
        static::useTestNow();

        $now = DateTime::now()->asDate();

        return [
            ['2020-06-01', 'api', $now],
            ['2020-06-01', 'json', $now],
            ['01-Jun-20', 'csv', $now],
        ];
    }
}
