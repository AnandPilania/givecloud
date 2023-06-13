<?php

namespace Tests\Unit\Listeners\Transactions;

use Ds\Events\RecurringPaymentWasRefunded;
use Ds\Listeners\Transactions\UpdateFundraisingAggregates;
use Tests\StoryBuilder;
use Tests\TestCase;

class UpdateFundraisingAggregatesTest extends TestCase
{
    public function testShouldQueueWhenAttachedToFundraisingPage(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->fromFundraisingPage()
            ->includingPayments()
            ->create();

        $shouldQueue = app(UpdateFundraisingAggregates::class)->shouldQueue(
            new RecurringPaymentWasRefunded($rpp, $rpp->last_transaction)
        );

        $this->assertTrue($shouldQueue);
    }

    public function testShouldNotQueueWhenNotAttachedToFundraisingPage(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->includingPayments()
            ->create();

        $shouldQueue = app(UpdateFundraisingAggregates::class)->shouldQueue(
            new RecurringPaymentWasRefunded($rpp, $rpp->last_transaction)
        );

        $this->assertFalse($shouldQueue);
    }

    public function testFundraisingPageAggregatesAreUpdated(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->fromFundraisingPage()
            ->includingPayments(1)
            ->create();

        $rpp->last_transaction->refund();

        event(new RecurringPaymentWasRefunded($rpp, $rpp->last_transaction));

        $this->assertSame($rpp->init_amt, $rpp->order_item->fundraisingPage->amount_raised);
    }
}
