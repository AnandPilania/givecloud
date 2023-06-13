<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Domain\Zapier\Jobs\TransactionBatchCompletedTrigger;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithRpps;

/** @group zapier */
class TransactionBatchCompletedTriggerTest extends AbstractTriggers
{
    use InteractsWithRpps;
    use WithFaker;

    public function testHandle(): void
    {
        $user = $this->createUserWithAccountAndSubs(Events::CONTRIBUTION_PAID, 3);

        $transactions = $this->createTransactionsWithRPP();

        $this->mockAndcallTrigger(TransactionBatchCompletedTrigger::class, $user->resthookSubscriptions, $transactions);
    }
}
