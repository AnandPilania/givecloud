<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillTransactions extends Command
{
    protected $signature = 'salesforce:backfill:transactions';

    protected $description = 'Backfills transactions to Salesforce';

    public function handle()
    {
        if (! app(SalesforceTransactionService::class)->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Transaction::query()->succeeded()->with([
            'recurringPaymentProfile.member',
        ]);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $transactions) use ($bar) {
            app(SalesforceTransactionService::class)->upsertMultiple($transactions);

            $bar->advance($transactions->count());
        });

        $bar->finish();

        return 0;
    }
}
