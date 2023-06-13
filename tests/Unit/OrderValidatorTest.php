<?php

namespace Tests\Unit;

use Ds\Models\Order;
use Ds\OrderValidator;
use Tests\TestCase;
use Throwable;

class OrderValidatorTest extends TestCase
{
    /*
        Scenarios to keep in mind when we test this:
            1. If the same product is in the cart twice, and the limit sales feature says theres only 1 left, you should not be able to purchase
    */

    public function testUsingInactiveCurrency()
    {
        $this->expectExceptionMessage('Unsupported local currency.');

        (new OrderValidator(
            Order::factory()->make(['currency_code' => 'JPY'])
        ))->validateCurrency();
    }

    public function testWithoutMemberWhenLoginRequiredForRpps()
    {
        sys_set(['rpp_require_login' => true]);

        $order = Order::factory()->make([
            'member_id' => null,
            'recurring_items' => 1,
        ]);

        $this->expectExceptionMessage('Your recurring transaction(s) must have a supporter account associated with it. Please login or sign-up by providing a password and try your transaction again.');

        (new OrderValidator($order))->validatePresenceOfMember();
    }

    public function testWithoutMemberThruPosWhenLoginRequiredForRpps()
    {
        sys_set(['rpp_require_login' => true]);

        $order = Order::factory()->make([
            'is_pos' => true,
            'member_id' => null,
            'recurring_items' => 1,
        ]);

        try {
            (new OrderValidator($order))->validatePresenceOfMember();

            $this->addToAssertionCount(1);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}
