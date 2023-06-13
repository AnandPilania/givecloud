<?php

namespace Tests\Unit\Domain\Shared;

use Ds\Domain\Shared\NullableDateTimePeriod;
use Tests\TestCase;

class NullableDateTimePeriodTest extends TestCase
{
    public function testCanReturnNullableDates(): void
    {
        $a = new NullableDateTimePeriod(
            null,
            null,
        );

        $this->assertNull($a->getNullableStart());
        $this->assertNull($a->getNullableEnd());
    }
}
