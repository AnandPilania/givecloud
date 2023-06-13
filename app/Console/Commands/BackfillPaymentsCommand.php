<?php

namespace Ds\Console\Commands;

use Ds\Models\Order;
use Ds\Models\Transaction;
use Ds\Services\PaymentService;
use Ds\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class BackfillPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills payments for existing contributions and transactions.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(PaymentService $paymentService, TransactionService $transactionService)
    {
        // DB::statement('SET foreign_key_checks = 0');
        // DB::statement('TRUNCATE TABLE payments');
        // DB::statement('TRUNCATE TABLE payments_pivot');
        // DB::statement('TRUNCATE TABLE refunds');

        $query = DB::table('productorder as o')
            ->selectRaw("o.confirmationdatetime as charged_at, 'order' as model, o.id")
            ->leftJoin('payments_pivot as pp', 'pp.order_id', '=', 'o.id')
            ->whereRaw('o.totalamount > 0')
            ->whereNotNull('o.confirmationdatetime')
            ->whereNull('pp.payment_id')
            ->union(
                DB::table('transactions as t')
                    ->selectRaw("t.order_time as charged_at, 'transaction' as model, t.id")
                    ->leftJoin('payments_pivot as pp', 'pp.transaction_id', '=', 't.id')
                    ->whereRaw('t.amt > 0')
                    ->whereNull('pp.payment_id')
            )
            ->orderBy('charged_at', 'asc');

        $count = DB::query()
            ->from(DB::raw('(' . $query->toSql() . ') as countable'))
            ->count();

        if ($count === 0) {
            return;
        }

        $bar = $this->createProgressBar($count);

        foreach ($query->cursor() as $result) {
            if ($result->model === 'order') {
                try {
                    $bar->setMessage("processing contribution #{$result->id}");
                    $paymentService->createPaymentFromOrder(Order::withTrashed()->findOrFail($result->id));
                } catch (ModelNotFoundException $e) {
                    // do nothing
                } catch (Throwable $e) {
                    $bar->error($e);
                }
            } elseif ($result->model === 'transaction') {
                try {
                    $bar->setMessage("processing transaction #{$result->id}");
                    $transactionService->createPayment(Transaction::findOrFail($result->id));
                } catch (ModelNotFoundException $e) {
                    // do nothing
                } catch (Throwable $e) {
                    $bar->error($e);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $bar->newLine();
    }
}
