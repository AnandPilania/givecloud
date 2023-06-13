<?php

namespace Tests\Unit\Domain\HotGlue;

use Ds\Domain\HotGlue\HotGlueEventServiceProvider;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\MemberOptinChanged;
use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Tests\TestCase;

/**
 * @group hotglue
 */
class HotGlueEventServiceProviderTest extends TestCase
{
    public function testHasListeners(): void
    {
        $listeners = HotGlueEventServiceProvider::listens();

        $this->assertIsArray($listeners);
        $this->assertArrayHasKey(AccountCreated::class, $listeners);
        $this->assertArrayHasKey(AccountWasUpdated::class, $listeners);
        $this->assertArrayHasKey(MemberOptinChanged::class, $listeners);
        $this->assertArrayHasKey(OrderWasCompleted::class, $listeners);
        $this->assertArrayHasKey(RecurringPaymentWasCompleted::class, $listeners);
        $this->assertArrayHasKey(RecurringBatchCompleted::class, $listeners);
        $this->assertContains(\Ds\Domain\HotGlue\Listeners\Mailchimp\MemberOptinChanged::class, $listeners[MemberOptinChanged::class]);
    }
}
