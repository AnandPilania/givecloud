<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillTransactionsLineItems extends Command
{
    protected $signature = 'salesforce:backfill:transactions-line-items';

    protected $description = 'Backfills transactions line items to Salesforce';

    public function handle()
    {
        if (! app(SalesforceTransactionLineItemService::class)->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Transaction::query()->succeeded()->with([
            'recurringPaymentProfile.member',
            'recurringPaymentProfile.order_item.sponsorship',
            'recurringPaymentProfile.order_item.variant.membership',
        ]);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $transactions) use ($bar) {
            app(SalesforceTransactionLineItemService::class)->upsertMultiple($transactions);

            $bar->advance($transactions->count());
        });

        $bar->finish();

        return 0;
    }
}
