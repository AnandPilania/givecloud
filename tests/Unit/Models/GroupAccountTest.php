<?php

namespace Tests\Unit\Models;

use Ds\Models\GroupAccount;
use Tests\TestCase;

class GroupAccountTest extends TestCase
{
    /**
     * @dataProvider isExpiredAttributeDataProvider
     */
    public function testIsExpiredAttribute(string $from, string $to, bool $isExpiredExpected): void
    {
        $this->assertSame(
            GroupAccount::factory()->make([
                'start_date' => fromLocal($from),
                'end_date' => fromLocal($to),
            ])->is_expired,
            $isExpiredExpected
        );
    }

    public function isExpiredAttributeDataProvider(): array
    {
        return [
            ['-1year', '+1year', false], // active
            ['-2years', '-1year', true], // expired
            ['+1year', '+2year', false], // future
        ];
    }
}
