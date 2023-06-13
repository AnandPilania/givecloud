<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Models\Member;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillSupporters extends Command
{
    protected $signature = 'salesforce:backfill:supporters';

    protected $description = 'Backfills supporters to Salesforce';

    protected SalesforceSupporterService $salesforceSupporterService;

    public function __construct(SalesforceSupporterService $salesforceSupporterService)
    {
        $this->salesforceSupporterService = $salesforceSupporterService;
        parent::__construct();
    }

    public function handle()
    {
        if (! $this->salesforceSupporterService->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $query = Member::query();

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $members) use ($bar) {
            $this->salesforceSupporterService->upsertMultiple($members);

            $bar->advance($members->count());
        });

        $bar->finish();

        return 0;
    }
}
