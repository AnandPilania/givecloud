<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class ReferralSourceController extends Controller
{
    public function index()
    {
        pageSetup('Referral Sources', 'jpanel');

        $referral_options = explode(',', sys_get('referral_sources_options'));

        $referral_options_for_sql = [];
        foreach ($referral_options as $opt) {
            $referral_options_for_sql[] = "'" . str_replace("'", "''", $opt) . "'";
        }
        $referral_options_for_sql = implode(',', $referral_options_for_sql);

        $total_sales = Order::paid()
            ->select([
                DB::raw("(case when referral_source in ($referral_options_for_sql) then referral_source else 'Other' end) as label"),
                DB::raw('count(id) as value'),
            ])->whereNotNull('referral_source')
            ->groupBy('label')
            ->get();

        return $this->getView('reports/referral_sources', [
            '__menu' => 'reports.referral-sources',
            'title' => 'Referral Sources',
            'total_sales' => $total_sales,
        ]);
    }

    public function get()
    {
        $qry_sources_with_stats = $this->_base_query();

        // generate data table
        $dataTable = new DataTable($qry_sources_with_stats, [
            'referral_source',
            new ExpressionWithName('MIN(productorder.createddatetime)', 'first_purchased_date'),
            new ExpressionWithName('MAX(productorder.createddatetime)', 'last_purchased_date'),
            new ExpressionWithName('count(id)', 'sales_count'),
            // new ExpressionWithName('avg(totalamount-ifnull(refunded_amt,0))','avg_amount'), // these amounts don't include recurring revenue
            // new ExpressionWithName('sum(totalamount-ifnull(refunded_amt,0))','total_amount')
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($source) {
            return [
                dangerouslyUseHTML(($source->referral_source) ? e($source->referral_source) : '<span class="text-muted">[No Value]</span>'),
                e(toLocalFormat($source->first_purchased_date, 'M j, Y')),
                e(toLocalFormat($source->last_purchased_date, 'M j, Y')),
                e(number_format($source->sales_count)),
                // number_format($source->avg_amount,2),
                // number_format($source->total_amount,2)
            ];
        });

        return response(dataTableGroupBy($dataTable, $this->_base_query()));
    }

    public function export()
    {
        $qry_sources_with_stats = $this->_base_query()
            ->select([
                'referral_source',
                DB::raw('MIN(productorder.createddatetime) as first_purchased_date'),
                DB::raw('MAX(productorder.createddatetime) as last_purchased_date'),
                DB::raw('count(id) as sales_count'),
            ])->get();

        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('referral_sources.csv') . '"');

        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Source', 'First Purchase', 'Last Purchase', 'Sales'], ',', '"');

        foreach ($qry_sources_with_stats as $source) {
            fputcsv($outstream, [
                $source->referral_source,
                toLocalFormat($source->first_purchased_date, 'csv'),
                toLocalFormat($source->last_purchased_date, 'csv'),
                number_format($source->sales_count),
            ], ',', '"');
        }

        fclose($outstream);
        exit;
    }

    private function _base_query()
    {
        $qry = Order::paid()->groupBy('referral_source');

        $filters = (object) [];

        $filters->search = request('search');
        if ($filters->search) {
            $qry->where('referral_source', 'like', '%' . $filters->search . '%');
        }

        // dates
        $filters->ordered_at_str = fromLocal(request('ordered_at_str'));
        $filters->ordered_at_end = fromLocal(request('ordered_at_end'));
        if ($filters->ordered_at_str && $filters->ordered_at_end) {
            $qry->whereBetween('productorder.createddatetime', [
                toUtcFormat($filters->ordered_at_str, 'Y-m-d 00:00:00'),
                toUtcFormat($filters->ordered_at_end, 'Y-m-d 23:59:59'),
            ]);
        } elseif ($filters->ordered_at_str) {
            $qry->where('productorder.createddatetime', '>=', toUtcFormat($filters->ordered_at_str, 'Y-m-d 00:00:00'));
        } elseif ($filters->ordered_at_end) {
            $qry->where('productorder.createddatetime', '<=', toUtcFormat($filters->ordered_at_end, 'Y-m-d 23:59:59'));
        }

        return $qry;
    }
}
