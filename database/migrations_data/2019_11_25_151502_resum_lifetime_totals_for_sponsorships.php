<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ResumLifetimeTotalsForSponsorships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // KNOWN ISSUE: this doesn't track refunds 100% corrently

        // Orders
        $orders = DB::table('productorderitem AS poi')
            ->join('productorder AS po', 'po.id', 'poi.productorderid')
            ->leftJoin('productinventory AS pi', 'pi.id', 'poi.productinventoryid')
            ->leftJoin('sponsorship AS sp', 'sp.id', 'poi.sponsorship_id')
            ->select([
                'po.member_id',
                DB::raw('SUM(CASE WHEN poi.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN (poi.price * poi.qty) * po.functional_exchange_rate ELSE 0 END) as donation_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN poi.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN po.id ELSE NULL END)) as donation_count'),
                DB::raw('SUM(CASE WHEN poi.sponsorship_id IS NULL AND pi.is_donation = 0 THEN (poi.price * poi.qty) * po.functional_exchange_rate ELSE 0 END) as purchase_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN poi.sponsorship_id IS NULL AND pi.is_donation = 0 THEN po.id ELSE NULL END)) as purchase_count'),
            ])->groupBy('po.member_id')
            ->whereNotNull('po.member_id')
            ->whereNotNull('po.confirmationdatetime')
            ->where('po.is_test', 0)
            ->whereNull('po.refunded_at');

        // Recurring Payments
        $rpps = DB::table('transactions AS t')
            ->join('recurring_payment_profiles AS rpp', 'rpp.id', 't.recurring_payment_profile_id')
            ->join('productorder AS po', 'po.id', 'rpp.productorder_id')
            ->leftJoin('productinventory AS pi', 'pi.id', 'rpp.productinventory_id')
            ->leftJoin('sponsorship AS sp', 'sp.id', 'rpp.sponsorship_id')
            ->select([
                'rpp.member_id',
                DB::raw('SUM(CASE WHEN rpp.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN (t.amt - t.tax_amt - t.shipping_amt) * t.functional_exchange_rate ELSE 0 END) as donation_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN rpp.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN t.id ELSE NULL END)) as donation_count'),
                DB::raw('SUM(CASE WHEN rpp.sponsorship_id IS NULL AND pi.is_donation = 0 THEN (t.amt - t.tax_amt - t.shipping_amt) * t.functional_exchange_rate ELSE 0 END) as purchase_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN rpp.sponsorship_id IS NULL AND pi.is_donation = 0 THEN t.id ELSE NULL END)) as purchase_count'),
            ])->groupBy('rpp.member_id')
            ->whereNotNull('rpp.member_id')
            ->where('t.payment_status', 'Completed')
            ->where('po.is_test', 0)
            ->whereNull('t.refunded_at');

        // Fundraisers
        $fundraisers = DB::table('productorderitem AS poi')
            ->join('productorder AS po', 'po.id', 'poi.productorderid')
            ->join('productinventory AS pi', 'pi.id', 'poi.productinventoryid')
            ->select([
                'poi.fundraising_member_id as member_id',
                DB::raw('SUM((poi.price * poi.qty) * po.functional_exchange_rate) as fundraising_total'),
                DB::raw('COUNT(DISTINCT po.id) as fundraising_count'),
            ])->groupBy('poi.fundraising_member_id')
            ->whereNotNull('poi.fundraising_member_id')
            ->whereRaw('poi.fundraising_member_id != po.member_id')
            ->whereNotNull('po.confirmationdatetime')
            ->where('po.is_test', 0)
            ->whereNull('po.refunded_at');

        DB::table('member')
            ->leftJoinSub($orders, 'orders', function ($join) {
                $join->on('member.id', '=', 'orders.member_id');
            })->leftJoinSub($rpps, 'rpps', function ($join) {
                $join->on('member.id', '=', 'rpps.member_id');
            })->leftJoinSub($fundraisers, 'fundraisers', function ($join) {
                $join->on('member.id', '=', 'fundraisers.member_id');
            })->update([
                'lifetime_donation_amount' => DB::raw('IFNULL(orders.donation_total, 0) + IFNULL(rpps.donation_total, 0)'),
                'lifetime_donation_count' => DB::raw('IFNULL(orders.donation_count, 0) + IFNULL(rpps.donation_count, 0)'),
                'lifetime_purchase_amount' => DB::raw('IFNULL(orders.purchase_total, 0) + IFNULL(rpps.purchase_total, 0)'),
                'lifetime_purchase_count' => DB::raw('IFNULL(orders.purchase_count, 0) + IFNULL(rpps.purchase_count, 0)'),
                'lifetime_fundraising_amount' => DB::raw('IFNULL(fundraisers.fundraising_total, 0)'),
                'lifetime_fundraising_count' => DB::raw('IFNULL(fundraisers.fundraising_count, 0)'),
            ]);
    }
}
