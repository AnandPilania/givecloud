<?php

namespace Tests\Unit\Domain\Shared;

use DateTimeImmutable;
use DateTimeZone;
use Ds\Domain\Shared\DateTimePeriod;
use Ds\Domain\Shared\Exceptions\NegativeDateTimePeriod;
use Ds\Domain\Shared\Exceptions\UTCOffsetMismatch;
use Tests\TestCase;

/**
 * @see https://github.com/pwm/datetime-period/blob/master/tests/unit/DateTimePeriodTest.php
 */
class DateTimePeriodTest extends TestCase
{
    private const PREDICATES = [
        'precedes',
        'meets',
        'overlaps',
        'finishedBy',
        'contains',
        'starts',
        'equals',
        'startedBy',
        'during',
        'finishes',
        'overlappedBy',
        'metBy',
        'precededBy',
    ];

    public function testItCreatesFromStartAndEndInstants(): void
    {
        $start = new DateTimeImmutable('2017-01-01T14:15:00+01:00');
        $end = new DateTimeImmutable('2017-05-04T16:45:00+01:00');

        $period = new DateTimePeriod($start, $end);

        self::assertInstanceOf(DateTimePeriod::class, $period);
        self::assertEquals($start, $period->getStart());
        self::assertEquals($end, $period->getEnd());
    }

    public function testItCreatesFromEqualStartAndEndInstants(): void
    {
        $ts = new DateTimeImmutable();

        $period = new DateTimePeriod($ts, $ts);

        self::assertInstanceOf(DateTimePeriod::class, $period);
        self::assertEquals($ts, $period->getStart());
        self::assertEquals($ts, $period->getEnd());
    }

    public function testEnsureThatStartAndEndTimezonesAreEqual(): void
    {
        $this->expectException(UTCOffsetMismatch::class);

        new DateTimePeriod(
            new DateTimeImmutable('2017-10-10T10:10:10+02:00'),
            new DateTimeImmutable('2017-10-10T10:10:10-05:00')
        );
    }

    public function testItThrowsIfStartDateIsBeforeEndDate(): void
    {
        $this->expectException(NegativeDateTimePeriod::class);
        new DateTimePeriod(new DateTimeImmutable('+1 day'), new DateTimeImmutable('-1 day'));
    }

    public function testCheckPrecedesPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T14:00:00+00:00'), new DateTimeImmutable('2017-01-01T16:00:00+00:00'));

        self::assertTrue($a->precedes($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['precedes']), $a, $b));
        self::assertTrue($b->precededBy($a)); // converse relation
    }

    public function testCheckMeetsPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->meets($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['meets']), $a, $b));
        self::assertTrue($b->metBy($a)); // converse relation
    }

    public function testCheckOverlapsPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T11:00:00+00:00'), new DateTimeImmutable('2017-01-01T13:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->overlaps($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['overlaps']), $a, $b));
        self::assertTrue($b->overlappedBy($a)); // converse relation
    }

    public function testCheckFinishedByPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->finishedBy($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['finishedBy']), $a, $b));
        self::assertTrue($b->finishes($a)); // converse relation
    }

    public function testCheckContainsPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T11:00:00+00:00'), new DateTimeImmutable('2017-01-01T13:00:00+00:00'));

        self::assertTrue($a->contains($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['contains']), $a, $b));
        self::assertTrue($b->during($a)); // converse relation
    }

    public function testCheckStartsPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->starts($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['starts']), $a, $b));
        self::assertTrue($b->startedBy($a)); // converse relation
    }

    public function testCheckEqualsPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        self::assertTrue($a->equals($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['equals']), $a, $b));
        self::assertTrue($b->equals($a)); // converse relation (equals is the converse of itself)
    }

    public function testCheckStartedByPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        self::assertTrue($a->startedBy($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['startedBy']), $a, $b));
        self::assertTrue($b->starts($a)); // converse relation
    }

    public function testCheckDuringPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T11:00:00+00:00'), new DateTimeImmutable('2017-01-01T13:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->during($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['during']), $a, $b));
        self::assertTrue($b->contains($a)); // converse relation
    }

    public function testCheckFinishesPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));

        self::assertTrue($a->finishes($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['finishes']), $a, $b));
        self::assertTrue($b->finishedBy($a)); // converse relation
    }

    public function testCheckOverlappedByPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T11:00:00+00:00'), new DateTimeImmutable('2017-01-01T13:00:00+00:00'));

        self::assertTrue($a->overlappedBy($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['overlappedBy']), $a, $b));
        self::assertTrue($b->overlaps($a)); // converse relation
    }

    public function testCheckMetByPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        self::assertTrue($a->metBy($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['metBy']), $a, $b));
        self::assertTrue($b->meets($a)); // converse relation
    }

    public function testCheckPrecededByPredicate(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T14:00:00+00:00'), new DateTimeImmutable('2017-01-01T16:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        self::assertTrue($a->precededBy($b));
        self::assertFalse(self::checkOtherPredicates(array_diff(self::PREDICATES, ['precededBy']), $a, $b));
        self::assertTrue($b->precedes($a)); // converse relation
    }

    public function testRelationsDifferOnTimelinesWithDifferentGranularities(): void
    {
        $aStart = '2017-01-01T12:12:09.829462+00:00';
        $aEnd = '2017-01-01T14:23:34.534678+00:00';
        $bStart = '2017-01-01T14:41:57.657388+00:00';
        $bEnd = '2017-01-01T16:19:03.412832+00:00';

        // The 2 periods created using the above instants meet on a timeline with an hourly granule
        $hourGranule = 'Y-m-d\TH';
        $a = new DateTimePeriod(
            DateTimeImmutable::createFromFormat($hourGranule, (new DateTimeImmutable($aStart))->format($hourGranule)),
            DateTimeImmutable::createFromFormat($hourGranule, (new DateTimeImmutable($aEnd))->format($hourGranule))
        );
        $b = new DateTimePeriod(
            DateTimeImmutable::createFromFormat($hourGranule, (new DateTimeImmutable($bStart))->format($hourGranule)),
            DateTimeImmutable::createFromFormat($hourGranule, (new DateTimeImmutable($bEnd))->format($hourGranule))
        );
        self::assertTrue($a->meets($b));

        // The 2 periods created using the above instants do not meet on a timeline with a minutely granule
        $minuteGranule = 'Y-m-d\TH:i';
        $a = new DateTimePeriod(
            DateTimeImmutable::createFromFormat($minuteGranule, (new DateTimeImmutable($aStart))->format($minuteGranule)),
            DateTimeImmutable::createFromFormat($minuteGranule, (new DateTimeImmutable($aEnd))->format($minuteGranule))
        );

        $b = new DateTimePeriod(
            DateTimeImmutable::createFromFormat($minuteGranule, (new DateTimeImmutable($bStart))->format($minuteGranule)),
            DateTimeImmutable::createFromFormat($minuteGranule, (new DateTimeImmutable($bEnd))->format($minuteGranule))
        );
        self::assertFalse($a->meets($b));
    }

    public function testTimezonesCanRepresentDifferentUtcOffsetsAsAResultOfDst(): void
    {
        $this->expectException(UTCOffsetMismatch::class);
        /*
         * This 1 year period have 2 dates whose timezones look the same but they represent different UTC offsets.
         * The first one generates a UTC+01 (BST) datetime while the 2nd one generates a UTC+00 (GMT) one.
         * This is because daylight saving time (DST) happens on different days in different years.
         */
        new DateTimePeriod(
            new DateTimeImmutable('2018-03-26T08:00:00', new DateTimeZone('Europe/London')),
            new DateTimeImmutable('2019-03-26T08:00:00', new DateTimeZone('Europe/London'))
        );
    }

    public function testItCanMapTimezonesToUtcOffsets(): void
    {
        self::assertSame('+01:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2018-03-26T08:00:00', new DateTimeZone('Europe/London'))));
        self::assertSame('+00:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2019-03-26T08:00:00', new DateTimeZone('Europe/London'))));

        self::assertSame('-04:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2018-03-11T08:00:00', new DateTimeZone('America/New_York'))));
        self::assertSame('-05:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2021-03-11T08:00:00', new DateTimeZone('America/New_York'))));

        self::assertSame('+03:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2018-03-25T08:00:00', new DateTimeZone('Europe/Kiev'))));
        self::assertSame('+02:00', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2019-03-25T08:00:00', new DateTimeZone('Europe/Kiev'))));

        self::assertSame('+09:30', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2018-04-01T08:00:00', new DateTimeZone('Australia/Adelaide'))));
        self::assertSame('+10:30', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2019-04-01T08:00:00', new DateTimeZone('Australia/Adelaide'))));

        self::assertSame('-02:30', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2018-03-11T08:00:00', new DateTimeZone('America/St_Johns'))));
        self::assertSame('-03:30', DateTimePeriod::getUtcOffset(new DateTimeImmutable('2021-03-11T08:00:00', new DateTimeZone('America/St_Johns'))));
    }

    public function testItReturnsTheCorrectNumberOfDaysInAPeriod(): void
    {
        $dt = new DateTimeImmutable('2017-01-01T12:00:00+00:00');
        self::assertSame(0, (new DateTimePeriod($dt, $dt))->getNumberOfDays());

        $dts = new DateTimeImmutable('2017-01-01T12:00:00+00:00');
        $dte = new DateTimeImmutable('2017-01-02T11:59:59+00:00');
        self::assertSame(0, (new DateTimePeriod($dts, $dte))->getNumberOfDays());

        $dts = new DateTimeImmutable('2017-01-01T12:00:00+00:00');
        $dte = new DateTimeImmutable('2017-01-02T12:00:00+00:00');
        self::assertSame(1, (new DateTimePeriod($dts, $dte))->getNumberOfDays());

        $dts = new DateTimeImmutable('2017-01-01T12:00:00+00:00');
        $dte = new DateTimeImmutable('2018-01-01T12:00:00+00:00');
        self::assertSame(365, (new DateTimePeriod($dts, $dte))->getNumberOfDays());

        // leap year
        $dts = new DateTimeImmutable('2016-01-01T12:00:00+00:00');
        $dte = new DateTimeImmutable('2017-01-01T12:00:00+00:00');
        self::assertSame(366, (new DateTimePeriod($dts, $dte))->getNumberOfDays());
    }

    public function testCanMergePeriodsAndReturnsMerged(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        $merged = $a->merge($b);

        self::assertSame($merged->getStart(), $b->getStart());
        self::assertSame($merged->getEnd(), $a->getEnd());
    }

    public function testCanMergeIntoPeriod(): void
    {
        $a = new DateTimePeriod(new DateTimeImmutable('2017-01-01T12:00:00+00:00'), new DateTimeImmutable('2017-01-01T14:00:00+00:00'));
        $b = new DateTimePeriod(new DateTimeImmutable('2017-01-01T10:00:00+00:00'), new DateTimeImmutable('2017-01-01T12:00:00+00:00'));

        $a->mergeInto($b);

        self::assertSame($a->getStart(), $b->getStart());
        self::assertSame($a->getEnd(), $a->getEnd());
    }

    private static function checkOtherPredicates(array $predicates, DateTimePeriod $a, DateTimePeriod $b): bool
    {
        return array_reduce($predicates, function (bool $result, string $predicate) use ($a, $b) {
            return $result || $a->{$predicate}($b);
        }, false);
    }
}
