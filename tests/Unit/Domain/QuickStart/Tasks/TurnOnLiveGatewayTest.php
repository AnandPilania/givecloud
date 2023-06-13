<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\Commerce\Gateways\NMIGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\QuickStart\Tasks\TurnOnLiveGateway;
use Tests\TestCase;

/** @group QuickStart */
class TurnOnLiveGatewayTest extends TestCase
{
    public function testIsCompletedReturnsGatewayStatus()
    {
        $task = $this->app->make(TurnOnLiveGateway::class);

        $this->assertFalse($task->isCompleted());

        /** @var \Ds\Domain\Commerce\Gateways\GivecloudTestGateway $provider */
        $provider = PaymentProvider::getCreditCardProvider(false);
        $provider->enabled = false;
        $provider->save();

        sys_set('credit_card_provider', false);

        $provider = PaymentProvider::factory()->nmi()->create(['test_mode' => false]);
        $this->app->make(NMIGateway::class, ['provider' => $provider]);

        $this->assertTrue($task->isCompleted());
    }
}
