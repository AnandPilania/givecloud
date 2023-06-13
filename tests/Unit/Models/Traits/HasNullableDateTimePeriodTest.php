<?php

namespace Tests\Unit\Models\Traits;

use Ds\Domain\Shared\DateTimePeriod;
use Ds\Models\Traits\HasNullableDateTimePeriod;
use Tests\TestCase;

class HasNullableDateTimePeriodTest extends TestCase
{
    public function testReturnedInstanceIsOfType()
    {
        $mock = $this->getMockForTrait(HasNullableDateTimePeriod::class);
        $mock->start_date = toUtc('-1month');

        $this->assertInstanceOf(DateTimePeriod::class, $mock->toNullableDateTimePeriod());
    }
}
