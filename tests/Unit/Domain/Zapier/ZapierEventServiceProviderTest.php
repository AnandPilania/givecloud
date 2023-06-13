<?php

namespace Tests\Unit\Domain\Zapier;

use Ds\Domain\Zapier\ZapierEventServiceProvider;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Tests\TestCase;

/**
 * @group zapier
 */
class ZapierEventServiceProviderTest extends TestCase
{
    public function testHasListenersWhenZapierIsEnabled()
    {
        sys_set('zapier_enabled', true);

        $zapierListeners = ZapierEventServiceProvider::listens();

        $this->assertIsArray($zapierListeners);
        $this->assertArrayHasKey(AccountCreated::class, $zapierListeners);
        $this->assertArrayHasKey(AccountWasUpdated::class, $zapierListeners);
        $this->assertArrayHasKey(OrderWasCompleted::class, $zapierListeners);
        $this->assertArrayHasKey(RecurringPaymentWasCompleted::class, $zapierListeners);
        $this->assertArrayHasKey(RecurringBatchCompleted::class, $zapierListeners);
    }

    public function testHasNoListenersWhenZapierIsNotEnabled()
    {
        sys_set('zapier_enabled', false);

        $this->assertEmpty(ZapierEventServiceProvider::listens());
    }
}
