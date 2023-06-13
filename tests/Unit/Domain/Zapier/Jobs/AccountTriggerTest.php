<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Jobs\AccountCreatedTrigger;
use Ds\Domain\Zapier\Jobs\AccountUpdatedTrigger;

/**
 * @group zapier
 */
class AccountTriggerTest extends AbstractTriggers
{
    public function accountEventsDataProvider(): array
    {
        return [
            ['supporter.created', AccountCreatedTrigger::class],
            ['supporter.updated', AccountUpdatedTrigger::class],
        ];
    }

    /**
     * @dataProvider accountEventsDataProvider
     */
    public function testHandle(string $eventName, string $triggerClassName): void
    {
        $user = $this->createUserWithAccountAndSubs($eventName);

        $this->mockAndcallTrigger($triggerClassName, $user->resthookSubscriptions, $user->members->first());
    }

    /**
     * @dataProvider accountEventsDataProvider
     */
    public function testHandleMultipleHooks(string $eventName, string $triggerClassName): void
    {
        $user = $this->createUserWithAccountAndSubs($eventName, 3);

        $this->mockAndcallTrigger($triggerClassName, $user->resthookSubscriptions, $user->members->first());
    }
}
