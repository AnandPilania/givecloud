<?php

namespace Tests\Unit\Listeners\Transactions;

use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Events\RecurringPaymentWasRefunded;
use Ds\Listeners\Transactions\UpdateLedgerEntries;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateLedgerEntriesTest extends TestCase
{
    public function testListenerListensForRecurringPaymentWasCompletedEvent(): void
    {
        Event::fake();

        Event::assertListening(RecurringPaymentWasCompleted::class, UpdateLedgerEntries::class);
    }

    public function testListenerListensForRecurringPaymentWasRefundedEvent(): void
    {
        Event::fake();

        Event::assertListening(RecurringPaymentWasRefunded::class, UpdateLedgerEntries::class);
    }
}
