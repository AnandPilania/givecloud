<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceLineItemService;
use Ds\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillLineItems extends Command
{
    protected $signature = 'salesforce:backfill:line-items';

    protected $description = 'Backfills contributions line items to Salesforce';

    private SalesforceLineItemService $salesforceLineItemService;

    public function __construct(SalesforceLineItemService $salesforceLineItemService)
    {
        $this->salesforceLineItemService = $salesforceLineItemService;
        parent::__construct();
    }

    public function handle()
    {
        if (! $this->salesforceLineItemService->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = OrderItem::query()->paid()->with([
            'order',
            'sponsorship',
            'variant.membership',
        ]);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $items) use ($bar) {
            $this->salesforceLineItemService->upsertMultiple($items);

            $bar->advance($items->count());
        });

        $bar->finish();

        return 0;
    }
}
