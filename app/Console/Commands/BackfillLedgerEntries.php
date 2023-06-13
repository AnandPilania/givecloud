<?php

namespace Ds\Console\Commands;

use Ds\Models\Order;
use Ds\Models\Transaction;
use Ds\Services\LedgerEntryService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BackfillLedgerEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:ledger-entries
                            {--type= : Process only a certain type  }
                            {--only-missing : Process only missing items }
                            {--order=* : Order ID to process (accepts multiple) }
                            {--transaction=* : Transaction ID to process (accepts multiple) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill ledger entries for Orders';

    /** @var \Ds\Services\LedgerEntryService */
    protected $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        parent::__construct();

        $this->ledgerEntryService = $ledgerEntryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('type') !== 'transactions') {
            $orders = Order::query()
                ->when(! empty($this->option('order')), function (Builder $query) {
                    $query->whereKey($this->option('order'));
                }, function (Builder $query) {
                    $query->whereNotNull('confirmationdatetime');
                })->when(! empty($this->option('only-missing')), function (Builder $query) {
                    $query->doesntHave('ledgerEntries');
                });

            $this->actOnModels($orders, 'order');
        }

        if ($this->option('type') !== 'orders' || ! empty($this->option('transaction'))) {
            $transactions = Transaction::query()
                ->when(! empty($this->option('transaction')), function (Builder $query) {
                    $query->whereKey($this->option('transaction'));
                }, function (Builder $query) {
                    $query->where('transaction_status', 'Completed');
                })->when(! empty($this->option('only-missing')), function (Builder $query) {
                    $query->doesntHave('ledgerEntries');
                });

            $this->actOnModels($transactions, 'transaction');
        }

        return 0;
    }

    private function actOnModels(Builder $builder, string $type): void
    {
        $total = $builder->count();
        $this->info('Acting on ' . $total . ' ' . Str::plural($type, $total));

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($builder->lazy() as $model) {
            $this->ledgerEntryService->make($model);

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }
}
