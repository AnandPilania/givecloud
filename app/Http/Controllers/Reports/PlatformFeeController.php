<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\Transaction;
use Ds\Models\TransactionFee;
use Illuminate\Support\Facades\DB;

class PlatformFeeController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.transaction_fees');

        $period = fromUtc(request('period', '-1 month'));

        pageSetup('Platform Fees', 'jpanel');

        return $this->getView('reports/platform-fees', [
            '__menu' => 'reports.transaction-fees',
            'summary' => (object) [
                'period_start' => fromUtc($period)->startOfMonth(),
                'period_end' => fromUtc($period)->endOfMonth(),
            ],
        ]);
    }

    public function export()
    {
        return $this->get('csv');
    }

    public function get($request_type = 'json')
    {
        user()->canOrRedirect('reports.transaction_fees');

        $filters = (object) [
            'search' => request('search'),
            'period' => request('period') ?: now()->format('Y-m'),
        ];

        $query = TransactionFee::query()
            ->where('period', $filters->period);

        if ($filters->search) {
            $keywords = array_map('trim', explode(' ', $filters->search));
            foreach ($keywords as $keyword) {
                $query->where('description', 'LIKE', "%{$keyword}%");
            }
        }

        // CSV
        if ($request_type === 'csv') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename=platform-fees.csv');
            header('Expires: 0');
            header('Pragma: public');
            $out_file = fopen('php://output', 'w');
            fputcsv($out_file, ['Date', 'Ref #', 'Description', 'Fee', 'Currency', 'Exchange Rate', 'Settled Fee', 'Settled Currency']);
            $query->orderBy('created_at')->chunk(1000, function ($chunk) use (&$out_file) {
                foreach ($chunk as $fee) {
                    fputcsv($out_file, [
                        fromUtcFormat($fee->created_at, 'csv'),
                        $fee->source->reference_number ?? '',
                        $fee->description,
                        number_format($fee->amount, 2, '.', ''),
                        $fee->currency,
                        $fee->exchange_rate,
                        number_format($fee->settlement_amount, 2, '.', ''),
                        $fee->settlement_currency,
                    ]);
                }
            });
            fclose($out_file);
            exit;
        }

        $query->with('source');

        // generate data table
        $dataTable = new DataTable($query, [
            'id',
            'source_type',
            'source_id',
            'rate',
            'amount',
            'currency',
            'exchange_rate',
            'settlement_amount',
            'settlement_currency',
            'description',
            'created_at',
        ]);

        $dataTable->setFormatRowFunction(function ($fee) {
            $description = e($fee->description);
            $description = preg_replace('/(?:Contribution|Order) #(\w+)/', 'Contribution <a href="' . route('backend.orders.edit_without_id', ['c' => '']) . '$1" target="_blank">#$1</a>', $description);
            $description = preg_replace('/Transaction #(\d+)/', 'Transaction <a href="#" class="ds-txn" data-txn-id="$1">#$1</a>', $description);

            return [
                dangerouslyUseHTML(e(fromUtcFormat($fee->created_at)) . ' <small class="text-muted">' . e(fromUtcFormat($fee->created_at, 'g:iA')) . ' UTC</small>'),
                e($fee->source->reference_number ?? ''),
                dangerouslyUseHTML($description),
                e(sprintf('%s%%', $fee->rate * 100)),
                dangerouslyUseHTML(vsprintf('%s%s %s', [
                    ($fee->amount < 0 ? '<i class="fa fa-fw fa-reply"></i>' : ''),
                    e(number_format($fee->amount, 2)),
                    e($fee->currency),
                ])),
                e($fee->exchange_rate),
                dangerouslyUseHTML(vsprintf('%s%s %s', [
                    ($fee->settlement_amount < 0 ? '<i class="fa fa-fw fa-reply"></i>' : ''),
                    e(number_format($fee->settlement_amount, 2)),
                    e($fee->settlement_currency),
                ])),
            ];
        });

        $stats = DB::table('transaction_fees as f')
            ->select([
                DB::raw("SUM(IF(f.source_type='payment',                                 1, 0)) as payments_count"),
                DB::raw("SUM(IF(f.source_type='payment', f.source_amount * f.exchange_rate, 0)) as payments_total"),
                DB::raw("SUM(IF(f.source_type='refund',                                  1, 0)) as refunds_count"),
                DB::raw("SUM(IF(f.source_type='refund',  f.source_amount * f.exchange_rate, 0)) as refunds_total"),
                DB::raw('SUM(f.settlement_amount)                                               as fees_total'),
                DB::raw('MAX(f.settlement_currency)                                             as fees_currency'),
                DB::raw('MAX(f.rate)                                                            as fees_rate'),
                DB::raw('COALESCE(p1.platform_fee_type, p2.platform_fee_type)                   as fees_type'),
            ])->leftJoin('payments as p1', fn ($join) => $join->on('p1.id', 'f.source_id')->where('f.source_type', 'payment'))
            ->leftJoin('refunds as r', fn ($join) => $join->on('r.id', 'f.source_id')->where('f.source_type', 'refund'))
            ->leftJoin('payments as p2', 'p2.id', 'r.payment_id')
            ->where('f.period', $filters->period)
            ->orderByDesc('f.rate')
            ->groupByRaw('COALESCE(p1.platform_fee_type, p2.platform_fee_type)')
            ->get();

        $platformFeeTypes = collect(sys_get('json:platform_fee_types') ?: null)->keyBy('type');

        $response = $dataTable->make();
        $response['summary_data'] = [];

        foreach ($stats as $stat) {
            $response['summary_data'][] = [
                'fees_rate' => $stat->fees_rate * 100,
                'fees_type' => $platformFeeTypes[$stat->fees_type]->description ?? null,
                'currency' => $stat->fees_currency,
                'payments_count' => $stat->payments_count,
                'payments_amount' => (string) money($stat->payments_total, $stat->fees_currency),
                'refunds_count' => $stat->refunds_count,
                'refunds_amount' => (string) money(abs($stat->refunds_total), $stat->fees_currency),
                'total_amount' => (string) money($stat->fees_total, $stat->fees_currency),
            ];
        }

        $feesCurrency = $stats[0]->fees_currency ?? null;
        $dccAmount = money(max(0, $this->_getDccAmount($filters->period)), $feesCurrency)->toPhpMoney();
        $totalFees = money($stats->sum('fees_total'), $feesCurrency)->toPhpMoney();
        $extra = money($dccAmount->subtract($totalFees), $feesCurrency);
        $hasExtra = $extra->getAmount() > 0;

        $fees = (object) [
            'fees_total' => $totalFees,
            'fees_currency' => $feesCurrency,
            'extra' => $hasExtra ? $extra : money(0),
        ];

        $response['dcc_total'] = '<div class="flex">';
        $response['dcc_total'] .= '<div class="flex-1 text-neutral-500">Total DCC Collected</div>';
        $response['dcc_total'] .= '<div class="font-bold text-base text-black">' . money($this->_getDccAmount($filters->period)) . '</div>';
        $response['dcc_total'] .= '</div>';
        $response['dcc_total'] .= '<div class="flex">';
        $response['dcc_total'] .= '<div class="flex-1 text-neutral-500">Givecloud platform fees</div>';
        $response['dcc_total'] .= '<div class="font-bold text-base text-black">' . money($fees->fees_total, $fees->fees_currency) . '</div>';
        $response['dcc_total'] .= '</div>';

        $response['dcc_total'] .= '<div class="flex mt-8">';
        $response['dcc_total'] .= '<div class="flex-1 text-black font-bold">Extra For Your Misson</div>';
        $response['dcc_total'] .= '<div class="font-extrabold text-lg text-black">';
        $response['dcc_total'] .= $hasExtra ? '<span class="mr-2">🚀</span> ' : '';
        $response['dcc_total'] .= $fees->extra . '</div>';
        $response['dcc_total'] .= '</div>';

        // return datatable JSON
        return response($response);
    }

    private function _getDccAmount(string $period)
    {
        $orders = Order::query()
            ->select([
                'productorder.id',
                DB::raw('productorder.functional_exchange_rate * productorder.dcc_total_amount as dcc_amount'),
            ])->where('productorder.dcc_total_amount', '>', 0);

        $transactions = Transaction::query()
            ->select([
                'transactions.id',
                DB::raw('transactions.functional_exchange_rate * transactions.dcc_amount as dcc_amount'),
            ])->where('transactions.dcc_amount', '>', 0);

        return Payment::query()
            ->join('payments_pivot', 'payments.id', 'payments_pivot.payment_id')
            ->leftJoinSub($orders, 'orders', function ($join) {
                $join->on('payments_pivot.order_id', '=', 'orders.id');
            })->leftJoinSub($transactions, 'transactions', function ($join) {
                $join->on('payments_pivot.transaction_id', '=', 'transactions.id');
            })->whereIn('payments.status', ['succeeded', 'pending'])
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $period)
            ->sum(DB::raw('IFNULL(orders.dcc_amount, 0) + IFNULL(transactions.dcc_amount, 0)'));
    }
}
