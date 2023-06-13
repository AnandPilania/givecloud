<?php

namespace Tests\Unit\Domain\QuickStart\Console;

use Ds\Domain\QuickStart\Console\BackfillCommand;
use Tests\TestCase;

/** @group QuickStart */
class BackfillCommandTest extends TestCase
{
    public function testBackfillCallsAllTasks(): void
    {
        $this->artisan(BackfillCommand::class)
            ->expectsOutput('Backfilling task BrandingSetup')
            ->expectsOutput('Backfilling task DonationItem')
            ->expectsOutput('Backfilling task DonorPerfectIntegration')
            ->expectsOutput('Backfilling task TaxReceipts')
            ->expectsOutput('Backfilling task TaxReceiptTemplates')
            ->expectsOutput('Backfilling task TestTransactions')
            ->expectsOutput('Backfilling task SetupLiveGateway')
            ->expectsOutput('Backfilling task ChoosePlan')
            ->expectsOutput('Backfilling task TurnOnLiveGateway')
            ->expectsOutput('Backfilling task CustomEmails');
    }
}
