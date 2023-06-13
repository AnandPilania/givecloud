<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Domain\Zapier\Jobs\TransactionCompletedTrigger;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithRpps;

/** @group zapier */
class TransactionCompletedTriggerTest extends AbstractTriggers
{
    use InteractsWithRpps;
    use WithFaker;

    public function testHandle(): void
    {
        $user = $this->createUserWithAccountAndSubs(Events::CONTRIBUTION_PAID, 3);

        $transaction = $this->createTransactionWithRPP();

        $this->mockAndcallTrigger(TransactionCompletedTrigger::class, $user->resthookSubscriptions, $transaction);
    }
}
