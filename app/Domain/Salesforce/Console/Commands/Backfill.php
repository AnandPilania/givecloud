<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Illuminate\Console\Command;

class Backfill extends Command
{
    protected $signature = 'salesforce:backfill';

    protected $description = 'Backfills supporters, contributions, discounts to Salesforce';

    public function handle()
    {
        $this->info('Backfilling supporters.');
        $this->call('salesforce:backfill:supporters');
        $this->newLine();

        $this->info('Backfilling contributions.');
        $this->call('salesforce:backfill:contributions');
        $this->newLine();

        $this->info('Backfilling transactions.');
        $this->call('salesforce:backfill:transactions');
        $this->newLine();

        $this->info('Backfilling line items.');
        $this->call('salesforce:backfill:line-items');
        $this->newLine();

        $this->info('Backfilling transactions line items.');
        $this->call('salesforce:backfill:transactions-line-items');
        $this->newLine();

        $this->info('Backfilling discounts.');
        $this->call('salesforce:backfill:discounts');
        $this->newLine();

        $this->info('Backfilling payments.');
        $this->call('salesforce:backfill:payments');
        $this->newLine();

        $this->info('Backfilling contribution-payments.');
        $this->call('salesforce:backfill:contribution-payments');
        $this->newLine();

        return 0;
    }
}
