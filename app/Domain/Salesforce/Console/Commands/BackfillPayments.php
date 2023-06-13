<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BackfillPayments extends Command
{
    protected $signature = 'salesforce:backfill:payments';

    protected $description = 'Backfills payments to Salesforce';

    private SalesforcePaymentsService $salesforcePaymentsService;

    public function __construct(SalesforcePaymentsService $salesforcePaymentsService)
    {
        parent::__construct();
        $this->salesforcePaymentsService = $salesforcePaymentsService;
    }

    public function handle()
    {
        if (! $this->salesforcePaymentsService->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Payment::query()->where(function (Builder $query) {
            $query->whereHas('orders')->orWhereHas('transactions');
        })->with('orders');

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $orders) use ($bar) {
            $this->salesforcePaymentsService->upsertMultiple($orders);

            $bar->advance($orders->count());
        });

        $bar->finish();

        return 0;
    }
}
