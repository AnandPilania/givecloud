<?php

namespace Ds\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Models\Order;
use Ds\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillContributionPayments extends Command
{
    protected $signature = 'salesforce:backfill:contribution-payments';

    protected $description = 'Backfills transactions and orders payments Salesforce';

    public function handle()
    {
        if (! app(SalesforceContributionPaymentService::class)->shouldSync()) {
            $this->warn('Salesforce is not enabled, not syncing.');

            return 0;
        }

        $this->contributions();
        $this->transactions();

        return 0;
    }

    protected function contributions()
    {
        $this->info('Contributions');

        $query = Order::query()->paid()->with(['payments']);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $contributions) use ($bar) {
            $payments = $contributions->pluck('payments')->flatten()->pluck('pivot');

            foreach ($payments->chunk(200) as $data) {
                app(SalesforceContributionPaymentService::class)->upsertMultiple($data);
            }

            $bar->advance($contributions->count());
        });

        $bar->finish();
    }

    protected function transactions()
    {
        $this->info('Transactions');

        $query = Transaction::query()->succeeded()->with([
            'payments',
        ]);

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(200, function (Collection $transactions) use ($bar) {
            $payments = $transactions->pluck('payments')->flatten()->pluck('pivot');

            foreach ($payments->chunk(200) as $data) {
                app(SalesforceContributionPaymentService::class)->upsertMultiple($data);
            }

            $bar->advance($transactions->count());
        });

        $bar->finish();
    }
}
