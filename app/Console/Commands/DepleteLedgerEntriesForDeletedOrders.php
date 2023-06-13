<?php

namespace Ds\Console\Commands;

use Ds\Models\Order;
use Illuminate\Console\Command;

class DepleteLedgerEntriesForDeletedOrders extends Command
{
    protected $signature = 'deplete:ledger-entries';

    protected $description = 'Soft-deletes ledger entries for deleted orders';

    public function handle()
    {
        $this->withProgressBar(Order::onlyTrashed()->get(), function (Order $order) {
            $order->ledgerEntries()->delete();
        });
    }
}
