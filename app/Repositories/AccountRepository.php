<?php

namespace Ds\Repositories;

use Closure;
use Ds\Models\Member as Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AccountRepository
{
    /** @var \Ds\Models\Member */
    protected $model;

    public function __construct(Account $model)
    {
        $this->model = $model;
    }

    public function getRandomAccount(): ?Account
    {
        return $this->model->newQuery()
            ->inRandomOrder()
            ->first();
    }

    public function countAccountsWithDpMembershipStartingToday(): int
    {
        return $this->getAccountsWithDpMembershipStartingTodayBuilder()
            ->distinct()
            ->count('member.id');
    }

    public function chunkAccountsWithDpMembershipStartingToday(int $count, Closure $callback): bool
    {
        return $this->getAccountsWithDpMembershipStartingTodayBuilder()
            ->with('groups')
            ->select('member.*')
            ->groupBy('member.id')
            ->chunkById($count, $callback);
    }

    private function getAccountsWithDpMembershipStartingTodayBuilder(): Builder
    {
        return $this->model->newQuery()
            ->join('group_account as ga', 'ga.account_id', 'member.id')
            ->join('membership as g', 'g.id', 'ga.group_id')
            ->whereNotNull('g.dp_id')
            ->whereDate('ga.start_date', fromLocal('today'));
    }

    /**
     * Get receiptable amounts for a given account.
     *
     * @param \Ds\Models\Member $account
     * @param float $minReceiptable
     * @param \DateTimeInterface|null $receiptingPeriodFrom
     * @param \DateTimeInterface|null $receiptingPeriodTo
     * @return \Illuminate\Support\Collection
     */
    public function getReceiptableAmounts(
        Account $account,
        $minReceiptable = 0,
        $receiptingPeriodFrom = null,
        $receiptingPeriodTo = null
    ) {
        $receiptingPeriodFrom = toUtc($receiptingPeriodFrom);
        $receiptingPeriodTo = toUtc($receiptingPeriodTo);

        $orders = DB::table('productorder as o')
            ->select([
                'o.id',
                DB::raw("'order' as type"),
                DB::raw("CONCAT('order_', o.id) as identifier"),
                DB::raw("CONCAT('Contribution #', o.client_uuid) as description"),
                'o.ordered_at as date',
                DB::raw('ROUND(SUM(i.qty*i.price),2) as receiptable_amount'),
                'o.currency_code',
                'o.tax_receipt_type as receipt_type',
            ])->join('productorderitem as i', 'i.productorderid', 'o.id')
            ->leftJoin('productinventory as v', 'v.id', 'i.productinventoryid')
            ->leftJoin('product as p', 'p.id', 'v.productid')
            ->join('member as m', 'm.id', 'o.member_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tax_receipts as r')
                    ->join('tax_receipt_line_items as rp', 'rp.tax_receipt_id', 'r.id')
                    ->whereIn('r.status', ['draft', 'issued'])
                    ->whereNull('r.deleted_at')
                    ->whereRaw('rp.order_id = o.id');
            })->whereNotNull('o.confirmationdatetime')
            ->whereNull('o.deleted_at')
            ->whereNull('o.refunded_at')
            ->where('o.member_id', $account->id)
            ->where(function ($query) {
                $query->whereNotNull('i.sponsorship_id');
                $query->orWhere('p.is_tax_receiptable', 1);
            })->groupBy('o.id')
            ->having('receiptable_amount', '>=', max(0, $minReceiptable))
            ->where('o.currency_code', currency());

        if (sys_get('bool:sponsorship_tax_receipts') === false) {
            $orders->whereNull('i.sponsorship_id');
        }

        if (sys_get('tax_receipt_country') !== 'ANY') {
            $orders->where(DB::raw('IFNULL(m.bill_country, o.billingcountry)'), sys_get('tax_receipt_country'));
        }

        if ($receiptingPeriodFrom && $receiptingPeriodTo) {
            $orders->whereBetween('o.ordered_at', [$receiptingPeriodFrom, $receiptingPeriodTo]);
        }

        $transactions = DB::table('transactions as t')
            ->select([
                't.id',
                DB::raw("'transaction' as type"),
                DB::raw("CONCAT('transaction_', t.id) as identifier"),
                DB::raw("CONCAT('Recurring Payment #', rpp.profile_id, '-', t.id) as description"),
                't.order_time as date',
                DB::raw('ROUND(t.amt,2) as receiptable_amount'),
                't.currency_code',
                't.tax_receipt_type as receipt_type',
            ])->join('recurring_payment_profiles as rpp', 'rpp.id', 't.recurring_payment_profile_id')
            ->leftJoin('productorder as o', 'o.id', 'rpp.productorder_id')
            ->leftJoin('product as p', 'p.id', 'rpp.product_id')
            ->join('member as m', 'm.id', 'rpp.member_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tax_receipts as r')
                    ->join('tax_receipt_line_items as rp', 'rp.tax_receipt_id', 'r.id')
                    ->whereIn('r.status', ['draft', 'issued'])
                    ->whereNull('r.deleted_at')
                    ->whereRaw('rp.transaction_id = t.id');
            })->where('t.payment_status', 'Completed')
            ->whereNull('t.refunded_at')
            ->where('rpp.member_id', $account->id)
            ->where(function ($query) {
                $query->whereNotNull('rpp.sponsorship_id');
                $query->orWhere('p.is_tax_receiptable', 1);
            })->where('t.amt', '>=', max(0, $minReceiptable))
            ->where('t.currency_code', currency());

        if (sys_get('bool:sponsorship_tax_receipts') === false) {
            $transactions->whereNull('rpp.sponsorship_id');
        }

        if (sys_get('tax_receipt_country') !== 'ANY') {
            $transactions->where(DB::raw('IFNULL(m.bill_country, o.billingcountry)'), sys_get('tax_receipt_country'));
        }

        if ($receiptingPeriodFrom && $receiptingPeriodTo) {
            $transactions->whereBetween('t.order_time', [$receiptingPeriodFrom, $receiptingPeriodTo]);
        }

        return $orders->union($transactions)
            ->orderBy('date', 'asc')
            ->get();
    }
}
