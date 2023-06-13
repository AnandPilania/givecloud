<?php

namespace Ds\Domain\QuickStart\Listeners;

use Ds\Domain\QuickStart\QuickStartService;
use Ds\Domain\QuickStart\Tasks\TestTransactions;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentOccurredListener implements ShouldQueue
{
    public function handle(): void
    {
        app(QuickStartService::class)->updateTaskStatus(TestTransactions::initialize());
    }
}
