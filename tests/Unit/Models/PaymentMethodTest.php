<?php

namespace Tests\Unit\Models;

use Ds\Models\PaymentMethod;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    /**
     * @dataProvider expiringScopesDataProvider
     */
    public function testExpiringScopes(string $scope, int $expected, string $name): void
    {
        $this->createPaymentMethods();

        $paymentMethod = PaymentMethod::query()->{$scope}();

        $this->assertSame($expected, $paymentMethod->count());
        $this->assertSame($name, $paymentMethod->first()->display_name);
    }

    protected function createPaymentMethods(): void
    {
        PaymentMethod::factory()->create(['display_name' => 'Payment Method']);

        PaymentMethod::factory(2)->expiringByEndOfNextMonth()->create();

        PaymentMethod::factory(3)->expired()->create();
    }

    public function expiringScopesDataProvider(): array
    {
        return [
            ['expiringByEndOfNextMonth', 2, 'Expiring by end of month Payment'],
            ['isExpired', 3, 'Expired Payment'],
            ['notExpiringByEndNextMonth', 1, 'Payment Method'],
        ];
    }
}
