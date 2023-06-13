<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillContributions extends Command
{
    protected $signature = 'salesforce:backfill:contributions';

    protected $description = 'Backfills contributions to Salesforce';

    private SalesforceContributionService $salesforceContributionService;

    public function __construct(SalesforceContributionService $salesforceContributionService)
    {
        $this->salesforceContributionService = $salesforceContributionService;
        parent::__construct();
    }

    public function handle()
    {
        if (! $this->salesforceContributionService->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Order::query()->paid()->with(['member']);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $orders) use ($bar) {
            $this->salesforceContributionService->upsertMultiple($orders);

            $bar->advance($orders->count());
        });

        $bar->finish();

        return 0;
    }
}
