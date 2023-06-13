<?php

namespace Tests\Unit\Domain\DoubleTheDonation\Enums;

use Ds\Domain\DoubleTheDonation\Enums\Status;
use Tests\TestCase;

/** @group DoubleTheDonation */
class StatusTest extends TestCase
{
    /** @dataProvider statusDataProvider */
    public function testStatusReturnsLabelWhenFound($status, $expected): void
    {
        $this->assertSame($expected, Status::label($status));
    }

    public function statusDataProvider(): array
    {
        return [
            [':waiting-for-donor-action', 'Waiting for Donor'],
            [':waiting-for-verification', 'Match Initiated'],
            [':pending-payment', 'Pending Payment'],
            [':match-complete', 'Match Complete'],
            [':unknown-employer', 'Unknown Employer'],
            [':ineligible', 'Ineligible'],
            [':some-invalid-status', null],
            [null, null],
        ];
    }
}
