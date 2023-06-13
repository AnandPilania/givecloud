<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceDiscountsService;
use Ds\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillDiscounts extends Command
{
    protected $signature = 'salesforce:backfill:discounts';

    protected $description = 'Backfills discounts to Salesforce';

    private SalesforceDiscountsService $salesforceDiscountsService;

    public function __construct(SalesforceDiscountsService $salesforceDiscountsService)
    {
        parent::__construct();
        $this->salesforceDiscountsService = $salesforceDiscountsService;
    }

    public function handle()
    {
        if (! $this->salesforceDiscountsService->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Order::query()->paid()->whereHas('promoCodes')->with(['promoCodes']);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $appliedPromos) use ($bar) {
            $this->salesforceDiscountsService->upsertMultiple($appliedPromos->pluck('promoCodes')->map->first());

            $bar->advance($appliedPromos->count());
        });

        $bar->finish();

        return 0;
    }
}
