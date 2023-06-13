<?php

namespace Ds\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrderTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:ordertable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up the order table.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Pruned ' . $this->pruneEmptyCarts() . ' empty carts (over 90 days)');

        $this->info('Pruned ' . $this->pruneAbandonedCarts() . ' abandoned carts (over 2 years)');
    }

    protected function pruneEmptyCarts(): int
    {
        return DB::table('productorder as o')
            ->leftJoin('productorderitem as i', 'i.productorderid', '=', 'o.id')
            ->whereNull('o.confirmationdatetime')
            ->whereNull('o.confirmationnumber')
            ->whereDate('o.started_at', '<', fromUtcFormat('-90 days', 'date'))
            ->whereNull('i.id')
            ->delete();
    }

    protected function pruneAbandonedCarts(): int
    {
        return DB::table('productorder')
            ->leftJoin('payments_pivot', 'payments_pivot.order_id', '=', 'productorder.id')
            ->whereNull('confirmationdatetime')
            ->whereNull('confirmationnumber')
            ->whereDate('started_at', '<', fromUtcFormat('-2 years', 'date'))
            ->whereNull('payments_pivot.id')
            ->delete();
    }
}
