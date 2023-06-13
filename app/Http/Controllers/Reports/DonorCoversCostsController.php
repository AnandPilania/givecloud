<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class DonorCoversCostsController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.donor_covers_costs');

        pageSetup('Donor Covers Costs', 'jpanel');

        return $this->getView('reports/donor_covers_costs', [
            '__menu' => 'reports.donor-covers-costs',
        ]);
    }

    public function get()
    {
        user()->canOrRedirect('reports.donor_covers_costs');

        $query = $this->_baseQueryWithFilters();

        // generate data table
        $dataTable = new DataTable($query, [
            new ExpressionWithName('(case when transactions.id IS NOT NULL THEN \'R\' + transactions.profile_id ELSE \'O\' + orders.client_uuid END) ', 'reference_id'),
            new ExpressionWithName("IF(orders.id, CONCAT(orders.billing_first_name, ' ', orders.billing_last_name), transactions.display_name)", 'account_name'),
            new ExpressionWithName('CONCAT(COALESCE(pay_products.name, rpp_products.name), " - ", COALESCE(pay_variants.variantname, rpp_variants.variantname))', 'product_variant_name'),
            'gateway_type',
            new ExpressionWithName('IFNULL(payments.card_brand, IFNULL(payments.currency, payments.type))', 'source_type_order'),
            new ExpressionWithName('(CASE WHEN transactions.id IS NOT NULL THEN transactions.line_item_amount ELSE orders.line_item_amount END)', 'line_item_amount'),
            new ExpressionWithName('(case when transactions.id IS NOT NULL THEN transactions.dcc_amount ELSE orders.dcc_amount END) ', 'dcc_amount'),
            new ExpressionWithName('COALESCE(orders.ordered_at, transactions.order_time)', 'created_at'),

            'payments.id',
            new ExpressionWithName('orders.client_uuid', 'client_uuid'),

            new ExpressionWithName('payments_pivot.recurring_payment_profile_id', 'recurring_payment_profile_id'),
            new ExpressionWithName('transactions.profile_id', 'rpp_profile_id'),
            new ExpressionWithName('transactions.id', 'transactionid'),

            'card_brand',
            new ExpressionWithName('payments.currency', 'currency'),
            new ExpressionWithName('payments.type', 'type'),

            new ExpressionWithName('COALESCE(pay_products.id, rpp_products.id)', 'product_id'),
            new ExpressionWithName('COALESCE(pay_products.code, rpp_products.code)', 'product_code'),
        ]);

        $dataTable->setFormatRowFunction(function ($record) {
            if ($record->client_uuid) {
                $order_txn_description = sprintf('<a href="%s">Contribution #%s</a>', route('backend.orders.edit_without_id', ['c' => $record->client_uuid]), e($record->client_uuid));
            } elseif ($record->recurring_payment_profile_id) {
                $order_txn_description = sprintf('<a href="%s">Recurring Profile #%s</a>', route('backend.recurring_payments.index', ['id' => $record->recurring_payment_profile_id]), e($record->rpp_profile_id));
            }

            $line_item_description = sprintf('<a href="%s">%s (%s)</a>', route('backend.products.edit', ['i' => $record->product_id]), e($record->product_variant_name), e($record->product_code));

            return [
                dangerouslyUseHTML($order_txn_description),
                e($record->account_name),
                dangerouslyUseHTML($line_item_description),
                e($record->gateway_type),
                e($record->source_type),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($record->line_item_amount)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($record->dcc_amount)) . '</div>'),
                e(toLocalFormat($record->created_at, 'M j, Y h:i a')),
            ];
        });

        $data = $dataTable->withManualCount()->make();

        $stats = $this->_baseQueryWithFilters()
            ->select([
                DB::raw('SUM(IFNULL(orders.dcc_amount, 0) + IFNULL(transactions.dcc_amount, 0)) as total_amount'),
                DB::raw('AVG(IFNULL(orders.dcc_amount, 0) + IFNULL(transactions.dcc_amount, 0)) as average_amount'),
                DB::raw('SUM(IFNULL(orders.line_item_amount, 0) + IFNULL(transactions.line_item_amount, 0)) as total_order_amount'),
            ])->first();

        $total_amount = $stats->total_amount;
        $average_amount = $stats->average_amount;
        $total_order_amount = $stats->total_order_amount;

        $report = '<div class="row">';
        $report .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 stat">' .
                    '<div class="stat-value-sm">' . money($total_amount) . '</div>' .
                '</div>';
        $report .= '</div>';

        $data['aggregate_html'] = $report;

        $statsTemplate = '<dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">%s</dd>';
        $statsTemplate .= '<dd class="mt-1 truncate text-sm font-medium text-gray-500">%s</dd>';

        $conversions = $this->conversions();

        $averageDccAmount = $total_order_amount ? round($total_amount / $total_order_amount * 100, 2) : 0;
        $converted = $conversions['total'] ? round($conversions['converted'] / $conversions['total'] * 100, 2) : 0;

        $data['stats']['totals'] = sprintf($statsTemplate, money($total_amount), $conversions['converted'] . ' Contributions with DCC');
        $data['stats']['average'] = sprintf($statsTemplate, money($average_amount), $averageDccAmount . '% of contribution total');
        $data['stats']['conversions'] = sprintf($statsTemplate, $converted . '%', $conversions['converted'] . ' out of ' . $conversions['total'] . ' Contributions Eligible for DCC');

        return response($data);
    }

    public function export()
    {
        user()->canOrRedirect('reports.donor_covers_costs');

        $query = $this->_baseQueryWithFilters();

        $query->select([
            'orders.client_uuid as order_reference',
            'transactions.profile_id as rpp_profile_id',
            'payments.gateway_type',

            'payments.card_brand',
            'payments.currency',
            'payments.type',

            DB::raw('(CASE WHEN transactions.id IS NOT NULL THEN transactions.dcc_amount ELSE orders.dcc_amount END) as dcc_amount'),
            DB::raw('(CASE WHEN transactions.id IS NOT NULL THEN transactions.line_item_amount ELSE orders.line_item_amount END) as line_item_amount'),
            DB::raw('COALESCE(orders.ordered_at, transactions.order_time) as created_at'),

            DB::raw('COALESCE(pay_products.code, rpp_products.code) as product_code'),
            DB::raw('COALESCE(pay_products.name, rpp_products.name) as product_name'),
            DB::raw('COALESCE(pay_variants.variantname, rpp_variants.variantname) as product_variant_name'),
            DB::raw('COALESCE(pay_products.meta1, rpp_products.meta1) as product_gl_code'),

            DB::raw("IF(orders.id, CONCAT(orders.billing_first_name, ' ', orders.billing_last_name), transactions.display_name) as account_name"),
        ]);

        return response()->streamDownload(function () use ($query) {
            $fp = fopen('php://output', 'w');

            fputcsv($fp, [
                'Contribution Reference',
                'Account',
                'Recurring Profile',

                'Product Code',
                'Product Name',
                'Variant Name',
                'Product GL Code',

                'Gateway',
                'Method',

                'Line Item Amount',
                'DCC Amount',

                'Date',
            ], ',', '"');

            $query->chunk(250, function ($query_chunk) use ($fp) {
                foreach ($query_chunk as $payment) {
                    fputcsv($fp, [
                        $payment->order_reference,
                        $payment->account_name,
                        $payment->rpp_profile_id,

                        $payment->product_code,
                        $payment->product_name,
                        $payment->product_variant_name,
                        $payment->product_gl_code,

                        $payment->gateway_type,
                        $payment->source_type,

                        numeral($payment->line_item_amount),
                        numeral($payment->dcc_amount),

                        toLocalFormat($payment->created_at, 'csv'),
                    ], ',', '"');
                }
            });
            fclose($fp);
        }, 'donor_covers_costs.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        $orders = Order::query()
            ->join('productorderitem', 'productorder.id', 'productorderitem.productorderid')
            ->select([
                'productorder.id',
                DB::raw('productorder.functional_exchange_rate * (productorderitem.price * productorderitem.qty + productorderitem.dcc_amount) as line_item_amount'),
                DB::raw('productorder.functional_exchange_rate * productorderitem.dcc_amount as dcc_amount'),
                'productorder.client_uuid',
                'productorderitem.productinventoryid',
                'productorder.billing_first_name',
                'productorder.billing_last_name',
                'productorder.ordered_at',
            ])->where('productorderitem.dcc_amount', '>', 0)
            ->whereNull('productorder.refunded_at');

        $transactions = RecurringPaymentProfile::query()
            ->join('transactions', 'recurring_payment_profiles.id', 'transactions.recurring_payment_profile_id')
            ->join('member as rpp_account', 'recurring_payment_profiles.member_id', 'rpp_account.id')
            ->select([
                'transactions.id',
                'recurring_payment_profiles.profile_id',
                DB::raw('transactions.functional_total as line_item_amount'),
                DB::raw('transactions.functional_exchange_rate * transactions.dcc_amount as dcc_amount'),
                'recurring_payment_profiles.productinventory_id',
                'transactions.order_time',
                'rpp_account.display_name',
            ])->where('transactions.dcc_amount', '>', 0)
            ->whereNull('transactions.refunded_at');

        $query = Payment::query()
            ->join('payments_pivot', 'payments.id', 'payments_pivot.payment_id')
            ->leftJoinSub($orders, 'orders', function ($join) {
                $join->on('payments_pivot.order_id', '=', 'orders.id');
            })->leftJoinSub($transactions, 'transactions', function ($join) {
                $join->on('payments_pivot.transaction_id', '=', 'transactions.id');
            })->leftJoin('productinventory as pay_variants', 'pay_variants.id', 'orders.productinventoryid')
            ->leftJoin('productinventory as rpp_variants', 'rpp_variants.id', 'transactions.productinventory_id')
            ->leftJoin('product as pay_products', 'pay_variants.productid', 'pay_products.id')
            ->leftJoin('product as rpp_products', 'rpp_variants.productid', 'rpp_products.id')
            ->whereIn('payments.status', ['succeeded', 'pending']);

        $query->where(function ($query) {
            $query->where('orders.dcc_amount', '>', 0);
            $query->orWhere('transactions.dcc_amount', '>', 0);
        });

        return $this->withFilters($query);
    }

    private function withFilters(Builder $query): Builder
    {
        if (request('search')) {
            $query->where(function ($query) {
                $keywords = array_map('trim', explode(' ', request('search')));
                foreach ($keywords as $keyword) {
                    $query->orWhere(function ($query) use ($keyword) {
                        $query->where('transactions.profile_id', 'LIKE', "%{$keyword}%");
                        $query->orWhere('orders.client_uuid', 'LIKE', "%{$keyword}%");
                        $query->orWhere('payments.card_brand', 'LIKE', "%{$keyword}%");
                        $query->orWhere('payments.gateway_type', 'LIKE', "%{$keyword}%");
                    });
                }
            });
        }

        // capture date
        if (request('period_start')) {
            $query->where('payments.created_at', '>=', toUtc(fromLocal(request('period_start'))->startOfDay()));
        }
        if (request('period_end')) {
            $query->where('payments.created_at', '<=', toUtc(fromLocal(request('period_end'))->endOfDay()));
        }

        return $query;
    }

    public function conversions(): array
    {
        $orders = Order::query()
            ->select([
                'productorder.id',
                'productorder.client_uuid',
                DB::raw('IF(productorder.dcc_total_amount > 0, 1, 0) as converted'),
            ])->whereHas('items', function (Builder $query) {
                $query->where('dcc_eligible', 1);
            });

        $transactions = RecurringPaymentProfile::query()
            ->join('transactions', 'recurring_payment_profiles.id', 'transactions.recurring_payment_profile_id')
            ->select([
                'transactions.id',
                'recurring_payment_profiles.profile_id',
                DB::raw('IF(transactions.dcc_amount > 0, 1, 0) as converted'),
            ])->whereHas('order_item', function (Builder $query) {
                $query->where('dcc_eligible', 1);
            });

        $query = Payment::query()
            ->addSelect([
                DB::raw('(CASE WHEN transactions.id IS NOT NULL THEN transactions.converted ELSE orders.converted END) as converted'),
            ])->join('payments_pivot', 'payments.id', 'payments_pivot.payment_id')
            ->leftJoinSub($orders, 'orders', function ($join) {
                $join->on('payments_pivot.order_id', '=', 'orders.id');
            })->leftJoinSub($transactions, 'transactions', function ($join) {
                $join->on('payments_pivot.transaction_id', '=', 'transactions.id');
            })->whereIn('payments.status', ['succeeded', 'pending'])
            ->whereNotNull(DB::raw('(CASE WHEN transactions.id IS NOT NULL THEN transactions.converted ELSE orders.converted END)'));

        $payments = $this->withFilters($query)->get();

        return [
            'total' => $payments->count(),
            'converted' => $payments->where('converted', 1)->count(),
        ];
    }
}
