<?php

namespace Ds\Console\Commands;

use Ds\Models\Order;
use Ds\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class BackfillContributionsCommand extends Command
{
    /** @var string */
    protected $signature = 'backfill:contributions';

    /** @var string */
    protected $description = 'Backfill Contributions from Orders and Transactions';

    public function handle(): int
    {
        $this->handleOrders();
        $this->handleTransactions();

        return 0;
    }

    private function handleOrders(): void
    {
        $orders = Order::query()
            ->whereNull('contribution_id')
            ->where(function (Builder $query) {
                $query->whereNotNull('confirmationdatetime');
                $query->orWhereHas('payments');
            });

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders->cursor() as $order) {
            $order->createOrUpdateContribution();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function handleTransactions(): void
    {
        $transactions = Transaction::query()
            ->whereNull('contribution_id')
            ->whereHas('payments');

        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        foreach ($transactions->cursor() as $transaction) {
            $transaction->createOrUpdateContribution();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
