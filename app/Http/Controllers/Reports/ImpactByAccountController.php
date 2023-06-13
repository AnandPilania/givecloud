<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\Member;
use Ds\Models\Order;
use Illuminate\Support\Facades\DB;

class ImpactByAccountController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports');
        user()->canOrRedirect('member');

        pageSetup('Impact by Account', 'jpanel');

        return $this->getView('reports/impact-by-account', [
            '__menu' => 'reports.impact-by-account',
        ]);
    }

    public function export()
    {
        return $this->get('csv');
    }

    public function get($request_type = 'json')
    {
        user()->canOrRedirect('reports');
        user()->canOrRedirect('member');

        $filters = (object) [
            'search' => request('search'),
        ];

        $referred_members = Member::query()
            ->select([
                'referred_by',
                DB::raw('SUM(lifetime_donation_amount) as secondary_impact_donation_total'),
                DB::raw('SUM(lifetime_donation_count) as secondary_impact_donation_count'),
            ])->groupBy('referred_by')
            ->whereNotNull('referred_by');

        $referred_orders = Order::query()
            ->select([
                'referred_by',
                DB::raw('COUNT(*) as secondary_impact_site_visits'),
                DB::raw('SUM(CASE WHEN email_opt_in = 1 THEN 1 ELSE 0 END) as secondary_impact_email_signups'),
            ])->groupBy('referred_by')
            ->whereNotNull('referred_by');

        $query = Member::query()
            ->leftJoinSub($referred_members, 'referred_members', function ($join) {
                $join->on('member.id', '=', 'referred_members.referred_by');
            })->leftJoinSub($referred_orders, 'referred_orders', function ($join) {
                $join->on('member.id', '=', 'referred_orders.referred_by');
            });

        if ($filters->search) {
            $keywords = array_map('trim', explode(' ', $filters->search));
            foreach ($keywords as $keyword) {
                $query->where('display_name', 'LIKE', "%{$keyword}%");
            }
        }

        // CSV
        if ($request_type === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $fp = fopen('php://output', 'w');

                fputcsv($fp, [
                    'Supporter Type', 'Display Name', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Email Opt-in',
                    'Lifetime Donation Amount', 'Lifetime Donation Count',
                    'Lifetime Purchase Amount', 'Lifetime Purchase Count',
                    'Lifetime Fundraising Amount', 'Lifetime Fundraising Count',
                    'Secondary Impact Donation Amount', 'Secondary Impact Donation Count',
                    'Secondary Impact Site Visits', 'Secondary Impact Email Signups',
                    'Shipping Title', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Address', 'Address 2', 'City', 'State/Prov', 'ZIP/Postal', 'Country', 'Phone',
                    'Billing Title', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Address', 'Address 2', 'City', 'State/Prov', 'ZIP/Postal', 'Country', 'Phone',
                    'DonorPerfect ID', 'Created on', 'Updated on', 'Referral Source',
                ], ',', '"');

                $query = $query->with('accountType');

                $query->chunk(250, function ($query_chunk) use ($fp) {
                    foreach ($query_chunk as $member) {
                        fputcsv($fp, [
                            ($member->accountType) ? $member->accountType->name : '',
                            $member->display_name,
                            $member->first_name,
                            $member->last_name,
                            $member->bill_organization_name,
                            $member->email,
                            ($member->email_opt_in) ? 'Yes' : 'No',

                            $member->lifetime_donation_amount,
                            $member->lifetime_donation_count,
                            $member->lifetime_purchase_amount,
                            $member->lifetime_purchase_count,
                            $member->lifetime_fundraising_amount,
                            $member->lifetime_fundraising_count,

                            ($member->secondary_impact_donation_total ?? 0),
                            ($member->secondary_impact_donation_count ?? 0),
                            ($member->secondary_impact_site_visits ?? 0),
                            ($member->secondary_impact_email_signups ?? 0),

                            $member->ship_title,
                            $member->ship_first_name,
                            $member->ship_last_name,
                            $member->ship_organization_name,
                            $member->ship_email,
                            $member->ship_address_01,
                            $member->ship_address_02,
                            $member->ship_city,
                            $member->ship_state,
                            $member->ship_zip,
                            $member->ship_country,
                            $member->ship_phone,

                            $member->bill_title,
                            $member->bill_first_name,
                            $member->bill_last_name,
                            $member->bill_organization_name,
                            $member->bill_email,
                            $member->bill_address_01,
                            $member->bill_address_02,
                            $member->bill_city,
                            $member->bill_state,
                            $member->bill_zip,
                            $member->bill_country,
                            $member->bill_phone,

                            $member->donor_id,
                            $member->created_at,
                            $member->updated_at,
                            $member->referral_source,
                        ], ',', '"');
                    }
                });
                fclose($fp);
            }, 'impact-by-supporter.csv', [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Description' => 'File Transfer',
                'Content-type' => 'text/csv',
                'Expires' => '0',
                'Pragma' => 'public',
            ]);
        }

        // generate data table
        $dataTable = new DataTable($query, [
            'id',
            'display_name',
            'lifetime_donation_amount',
            'lifetime_donation_count',
            'lifetime_purchase_amount',
            'lifetime_purchase_count',
            'lifetime_fundraising_amount',
            'lifetime_fundraising_count',
            'secondary_impact_donation_total',
            'secondary_impact_donation_count',
            'secondary_impact_site_visits',
            'secondary_impact_email_signups',
        ]);

        $dataTable->setFormatRowFunction(function ($member) {
            return [
                dangerouslyUseHTML('<a href="' . route('backend.member.edit', $member->id) . '"><i class="fa fa-search"></i></a>'),
                e($member->display_name),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($member->lifetime_donation_amount)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->lifetime_donation_count) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($member->lifetime_purchase_amount)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->lifetime_purchase_count) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($member->lifetime_fundraising_amount)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->lifetime_fundraising_count) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($member->secondary_impact_donation_total)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->secondary_impact_donation_count ?? 0) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->secondary_impact_site_visits ?? 0) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e($member->secondary_impact_email_signups ?? 0) . '</div>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }
}
