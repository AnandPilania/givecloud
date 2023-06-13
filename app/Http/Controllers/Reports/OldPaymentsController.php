<?php

namespace Ds\Http\Controllers\Reports;

use Carbon\Carbon;
use Ds\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OldPaymentsController extends Controller
{
    public function index()
    {
        pageSetup('Payments', 'jpanel');

        $filters = [];
        $filters['start_date'] = request('start_date') ? fromLocal(request('start_date')) : Carbon::today()->subDays(31);
        $filters['end_date'] = request('end_date') ? fromLocal(request('end_date')) : Carbon::today();

        $days_diff = $filters['start_date']->diffInDays($filters['end_date']);

        if ($days_diff > 31) {
            $dates_fixed = true;
            $filters['start_date'] = $filters['end_date']->subDays(31);
        } else {
            $dates_fixed = false;
        }

        $payments = $this->_base_query($filters)->get();

        $payments_by_card_type = $payments->groupBy('account_type')->map(function ($row) {
            return $row->sum('amount');
        })->all();

        $payments_total = $payments->sum('amount');

        $payments_by_card_type_chart = [];
        foreach ($payments_by_card_type as $type => $amount) {
            $payments_by_card_type_chart[] = (object) [
                'label' => ($type) ?: 'Unknown',
                'value' => (float) round($amount, 2),
            ];
        }

        return $this->getView('reports/payments-old', [
            '__menu' => 'reports.payments',
            'title' => 'Payments',
            'payments' => $payments,
            'payments_by_type' => $payments_by_card_type,
            'payments_by_card_type_chart' => $payments_by_card_type_chart,
            'payments_total' => $payments_total,
            'filters' => $filters,
            'dates_fixed' => $dates_fixed,
            'color_array' => ['#8064A2', '#F79646', '#4F81BD', '#C0504D', '#9BBB59', '#2C4D75', '#4BACC6'],
        ]);
    }

    public function export()
    {
        pageSetup('Payments', 'jpanel');

        $filters = [];
        $filters['start_date'] = request('start_date') ? Carbon::parse(request('start_date')) : Carbon::today()->subDays(180)->format('Y-m-d');
        $filters['end_date'] = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::today()->format('Y-m-d');

        $payments = $this->_base_query($filters)->get();

        $payments_by_card_type = $payments->groupBy('account_type')->map(function ($row) {
            return $row->sum('amount');
        })->all();

        $payments_total = $payments->sum('amount');

        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('payments.csv') . '"');

        $outstream = fopen('php://output', 'w');

        // totals by type at the top
        fputcsv($outstream, ['Totals by Method'], ',', '"');
        foreach ($payments_by_card_type as $type => $amount) {
            fputcsv($outstream, [($type ?: 'Unknown'), number_format($amount, 2)], ',', '"');
        }
        fputcsv($outstream, ['Total', number_format($payments_total, 2)], ',', '"');

        // one row buffer
        fputcsv($outstream, [' '], ',', '"');

        // headers
        fputcsv($outstream, ['Time', 'Transaction', 'First Name', 'Last Name', 'Amount', 'Method', 'Account Number', 'Reference'], ',', '"');

        // every payment
        foreach ($payments as $payment) {
            $ref = '';
            if ($payment->reference == 'order') {
                $ref = "Contribution #{$payment->reference_number}";
            } elseif ($payment->reference == 'rpp') {
                $ref = "Recurring Profile #{$payment->reference_number}";
            }

            fputcsv($outstream, [
                toLocalFormat($payment->transaction_date, 'csv'),
                $payment->transaction_id,
                $payment->first_name,
                $payment->last_name,
                number_format($payment->amount, 2),
                $payment->account_type,
                $payment->account_number,
                $ref,
            ], ',', '"');
        }

        fclose($outstream);
        exit;
    }

    private function _base_query($filters)
    {
        $orders = DB::table('productorder')
            ->select([
                'confirmationnumber as transaction_id',
                DB::raw("date(convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "') as transaction_time"),
                'totalamount as amount',
                DB::raw('ifnull(billingcardtype, payment_type) as account_type'),
                DB::raw('billingcardlastfour as account_number'),
                DB::raw("'order' as reference"),
                DB::raw('productorder.invoicenumber as reference_number'),
                DB::raw('productorder.billing_first_name as first_name'),
                DB::raw('productorder.billing_last_name as last_name'),
            ])->whereNotNull('confirmationdatetime')
            ->where('totalamount', '>', 0)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw("date(convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);

        $order_refunds = DB::table('productorder')
            ->select([
                'refunded_auth as transaction_id',
                DB::raw("date(convert_tz(refunded_at, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(refunded_at, 'UTC', '" . localOffset() . "') as transaction_time"),
                DB::raw('(0-refunded_amt) as amount'),
                DB::raw('ifnull(billingcardtype, payment_type) as account_type'),
                DB::raw('billingcardlastfour as account_number'),
                DB::raw("'order' as reference"),
                DB::raw('productorder.invoicenumber as reference_number'),
                DB::raw('productorder.billing_first_name as first_name'),
                DB::raw('productorder.billing_last_name as last_name'),
            ])->whereNotNull('confirmationdatetime')
            ->whereNotNull('refunded_amt')
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw("date(convert_tz(refunded_at, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);

        $transactions = DB::table('transactions')
            ->select([
                'transactions.transaction_id',
                DB::raw("date(convert_tz(transactions.order_time, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(transactions.order_time, 'UTC', '" . localOffset() . "') as transaction_time"),
                'transactions.amt as amount',
                DB::raw('payment_methods.account_type as account_type'),
                DB::raw('payment_methods.account_last_four as account_number'),
                DB::raw("'rpp' as reference"),
                DB::raw('recurring_payment_profiles.profile_id as reference_number'),
                DB::raw('member.first_name'),
                DB::raw('member.last_name'),
            ])->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
            ->leftJoin('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'transactions.recurring_payment_profile_id')
            ->leftJoin('member', 'member.id', '=', 'recurring_payment_profiles.member_id')
            ->whereNotNull('transaction_id')
            ->whereBetween(DB::raw("date(convert_tz(order_time, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);

        $transaction_refunds = DB::table('transactions')
            ->select([
                'transactions.transaction_id',
                DB::raw("date(convert_tz(transactions.refunded_at, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(transactions.refunded_at, 'UTC', '" . localOffset() . "') as transaction_time"),
                DB::raw('-transactions.refunded_amt as amount'),
                DB::raw('payment_methods.account_type as account_type'),
                DB::raw('payment_methods.account_last_four as account_number'),
                DB::raw("'rpp' as reference"),
                DB::raw('recurring_payment_profiles.profile_id as reference_number'),
                DB::raw('member.first_name'),
                DB::raw('member.last_name'),
            ])->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
            ->leftJoin('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'transactions.recurring_payment_profile_id')
            ->leftJoin('member', 'member.id', '=', 'recurring_payment_profiles.member_id')
            ->whereNotNull('refunded_at')
            ->whereBetween(DB::raw("date(convert_tz(refunded_at, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);

        // only return gateway-related (fee-related) payments
        if (array_key_exists('gateway_only', $filters)) {
            $orders->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $order_refunds->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $transactions->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $transaction_refunds->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
        }

        return $orders
            ->union($order_refunds)
            ->union($transactions)
            ->union($transaction_refunds);
    }
}
