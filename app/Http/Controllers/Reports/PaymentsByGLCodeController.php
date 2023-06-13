<?php

namespace Ds\Http\Controllers\Reports;

use Carbon\Carbon;
use Ds\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PaymentsByGLCodeController extends Controller
{
    public function index()
    {
        $filters = [];
        $filters['start_date'] = request('start_date') ? Carbon::parse(request('start_date')) : Carbon::today()->subDays(180);
        $filters['end_date'] = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::today();
        $days_diff = $filters['start_date']->diffInDays($filters['end_date']);
        if ($days_diff > 31) {
            $dates_fixed = true;
            $filters['start_date'] = $filters['end_date']->clone()->subDays(31);
        } else {
            $dates_fixed = false;
        }
        $orders = DB::table('productorder')
            ->select([
                'confirmationnumber as transaction_id',
                DB::raw("date(convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "') as transaction_time"),
                DB::raw('(productorderitem.qty*productorderitem.price) as amount'),
                DB::raw('ifnull(billingcardtype, payment_type) as account_type'),
                DB::raw('billingcardlastfour as account_number'),
                DB::raw("'order' as reference"),
                DB::raw('productorder.invoicenumber as reference_number'),
                DB::raw('productorder.billing_first_name as first_name'),
                DB::raw('productorder.billing_last_name as last_name'),
                DB::raw('product.meta1 as gl_code'),
            ])->join('productorderitem', 'productorderitem.productorderid', '=', 'productorder.id')
            ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
            ->join('product', 'product.id', '=', 'productinventory.productid')
            ->whereNotNull('productorder.confirmationdatetime')
            ->where('productorder.totalamount', '>', 0)
            ->whereNull('productorder.deleted_at')
            ->whereBetween(DB::raw("date(convert_tz(productorder.confirmationdatetime, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);
        $order_refunds = DB::table('productorder')
            ->select([
                'refunded_auth as transaction_id',
                DB::raw("date(convert_tz(refunded_at, 'UTC', '" . localOffset() . "')) AS transaction_date"),
                DB::raw("convert_tz(refunded_at, 'UTC', '" . localOffset() . "') as transaction_time"),
                DB::raw('0-(productorderitem.qty*productorderitem.price) as amount'),
                DB::raw('ifnull(billingcardtype, payment_type) as account_type'),
                DB::raw('billingcardlastfour as account_number'),
                DB::raw("'order' as reference"),
                DB::raw('productorder.invoicenumber as reference_number'),
                DB::raw('productorder.billing_first_name as first_name'),
                DB::raw('productorder.billing_last_name as last_name'),
                DB::raw('product.meta1 as gl_code'),
            ])->join('productorderitem', 'productorderitem.productorderid', '=', 'productorder.id')
            ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
            ->join('product', 'product.id', '=', 'productinventory.productid')
            ->whereNotNull('productorder.confirmationdatetime')
            ->whereNotNull('productorder.refunded_amt')
            ->whereNull('productorder.deleted_at')
            ->whereBetween(DB::raw("date(convert_tz(productorder.refunded_at, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);
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
                DB::raw('product.meta1 as gl_code'),
            ])->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
            ->leftJoin('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'transactions.recurring_payment_profile_id')
            ->leftJoin('member', 'member.id', '=', 'recurring_payment_profiles.member_id')
            ->leftJoin('product', 'product.id', '=', 'recurring_payment_profiles.product_id')
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
                DB::raw('product.meta1 as gl_code'),
            ])->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
            ->leftJoin('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'transactions.recurring_payment_profile_id')
            ->leftJoin('member', 'member.id', '=', 'recurring_payment_profiles.member_id')
            ->leftJoin('product', 'product.id', '=', 'recurring_payment_profiles.product_id')
            ->whereNotNull('refunded_at')
            ->whereBetween(DB::raw("date(convert_tz(refunded_at, 'UTC', '" . localOffset() . "'))"), [$filters['start_date'], $filters['end_date']]);
        // only return gateway-related (fee-related) payments
        if (array_key_exists('gateway_only', $filters)) {
            $orders->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $order_refunds->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $transactions->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
            $transaction_refunds->whereNotIn(DB::raw('lower(account_type)'), ['eft', 'cash', 'check', 'other']);
        }
        $payments = $orders
            ->union($order_refunds)
            ->union($transactions)
            ->union($transaction_refunds)
            ->get();

        return $this->getView('reports/payments-by-gl', [
            '__menu' => 'reports.payments',
            'title' => 'Payments by GL Account',
            'payments' => $payments,
            'filters' => $filters,
            /*'payments_by_type'           => $payments_by_card_type,
            'payments_by_card_type_chart'  => $payments_by_card_type_chart,
            'payments_total'               => $payments_total,
            'dates_fixed'                  => $dates_fixed,
            'color_array'                  => ['#8064A2','#F79646','#4F81BD','#C0504D','#9BBB59','#2C4D75','#4BACC6']*/
        ]);
    }
}
