<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.tax_reconciliation');

        request()->merge([
            'fd1' => request('fd1', fromLocal('now')->format('Y-m-01')),
            'fd2' => request('fd2', fromLocal('now')->format('Y-m-t')),
        ]);

        [$taxes, $getOrders] = $this->getTaxData();

        $title = 'Tax Reconciliation';

        pageSetup($title, 'jpanel');

        return $this->getView('reports/tax', [
            '__menu' => 'reports.tax',
            'title' => $title,
            'taxes' => $taxes,
            'getOrders' => $getOrders,
        ]);
    }

    public function export()
    {
        user()->canOrRedirect('reports.tax_reconciliation');

        $filename = toLocalFormat(request('fd1'), 'Y-m-d') . '_' . toLocalFormat(request('fd2'), 'Y-m-d') . '_Taxes.csv';
        $filename = sanitize_filename($filename);

        return response()->streamDownload(function () {
            $fp = fopen('php://output', 'w');

            [$taxes, $getOrders] = $this->getTaxData();

            foreach ($taxes as $tax) {
                $orders = $getOrders($tax);

                $total_price = 0;
                $total_tax = 0;

                // top row
                fputcsv($fp, [$tax->code . ' (' . $tax->rate . '%)']);

                foreach ($orders as $item) {
                    $total_price += $item->total_amount;
                    $total_tax += $item->amount;

                    // tax item
                    fputcsv($fp, [
                        toLocalFormat($item->confirmationdatetime, 'Y-m-d'),
                        $item->invoicenumber,
                        $item->name . (empty($item->variantname) ? '' : ' (' . $item->variantname . ')'),
                        number_format($item->price, 2),
                        $item->qty,
                        number_format($item->total_amount, 2),
                        number_format($item->amount, 2),
                    ]);
                }

                // total row
                fputcsv($fp, [
                    '', '', '', '', '',
                    number_format($total_price, 2),
                    number_format($total_tax, 2),
                ]);
            }

            fclose($fp);
        }, $filename, [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Get tax date based on the specific time period.
     *
     * @return array
     */
    private function getTaxData()
    {
        $filterByDate = function ($query) {
            if (request('fd1') && request('fd2')) {
                $query->whereBetween('o.createddatetime', [
                    fromUtc(request('fd1'))->startOfDay(),
                    fromUtc(request('fd2'))->endOfDay(),
                ]);
            } elseif (request('fd1')) {
                $query->where('o.createddatetime', '>=', fromUtc(request('fd1'))->startOfDay());
            } elseif (request('fd2')) {
                $query->where('o.createddatetime', '<=', fromUtc(request('fd2'))->endOfDay());
            }
        };

        $shipping = DB::table('productorder as o')
            ->selectRaw('it.taxid, COUNT(*) as tax_record_count')
            ->join('productorderitem as i', 'i.productorderid', 'o.id')
            ->leftJoin('productorderitemtax as it', 'it.orderitemid', 'i.id')
            ->where('o.is_processed', true)
            ->groupBy('it.taxid');

        $filterByDate($shipping);

        $taxes = DB::table('producttax as t')
            ->selectRaw('t.*, t1.tax_record_count')
            ->joinSub($shipping, 't1', 't1.taxid', 't.id')
            ->cursor();

        $getOrders = function ($tax) use ($filterByDate) {
            $query = DB::table('productorder as o')
                ->select([
                    'it.taxid',
                    'o.invoicenumber',
                    'o.currency_code',
                    'o.confirmationdatetime',
                    'p.name',
                    'pi.variantname',
                    DB::raw('(i.price*o.functional_exchange_rate) AS price'),
                    'i.qty',
                    DB::raw('((i.price*o.functional_exchange_rate)*i.qty) AS total_amount'),
                    DB::raw('(o.functional_exchange_rate*it.amount) AS amount'),
                ])->leftJoin('productorderitem as i', 'i.productorderid', 'o.id')
                ->leftJoin('productorderitemtax as it', 'it.orderitemid', 'i.id')
                ->leftJoin('productinventory as pi', 'pi.id', 'i.productinventoryid')
                ->leftJoin('product as p', 'p.id', 'pi.productid')
                ->where('it.taxid', $tax->id)
                ->where('o.is_processed', 1)
                ->whereNotNull('o.confirmationdatetime')
                ->orderBy('o.ordered_at');
            $filterByDate($query);

            return $query->cursor();
        };

        return [$taxes, $getOrders];
    }
}
