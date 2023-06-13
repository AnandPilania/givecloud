<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class ProductController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.orders_by_product');

        $__menu = 'reports.products';

        $title = 'Contributions by Product';

        pageSetup($title, 'jpanel');

        return $this->getView('reports/products', compact('__menu', 'title'));
    }

    /**
     * Ajax data for receipt list.
     */
    public function get()
    {
        user()->canOrRedirect('reports.orders_by_product');

        $maxAmount = $this->_base_query()
            ->select(DB::raw('SUM(productorderitem.qty*(productorderitem.price*productorder.functional_exchange_rate)) as total_sales_amount'))
            ->orderBy('total_sales_amount', 'desc')
            ->first();

        if ($maxAmount) {
            $maxAmount = (float) $maxAmount->total_sales_amount;
        } else {
            $maxAmount = 0;
        }

        // generate data table
        $dataTable = new DataTable($this->_base_query(), [
            'product.id',
            'product.code',
            'product.name',
            new ExpressionWithName('MIN(productorder.ordered_at)', 'firstpurchasedatetime'),
            new ExpressionWithName('MAX(productorder.ordered_at)', 'lastpurchasedatetime'),
            new ExpressionWithName('COUNT(distinct productorder.id)', 'ordercount'),
            new ExpressionWithName('SUM(productorderitem.qty)', 'quantitypurchased'),
            new ExpressionWithName('SUM(productorderitem.qty*(productorderitem.price*productorder.functional_exchange_rate))', 'totalamount'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($product) use ($maxAmount) {
            return [
                dangerouslyUseHTML('<a href="' . route('backend.reports.products.index', $product->productId) . '"><i class="fa fa-search"></i></a>'),
                e($product->productCode),
                e($product->productName),
                e(toLocalFormat($product->firstpurchasedatetime, 'M j, Y')),
                e(toLocalFormat($product->lastpurchasedatetime, 'M j, Y')),
                e(number_format($product->ordercount)),
                e(number_format($product->quantitypurchased)),
                e(number_format($product->totalamount, 2)),
                dangerouslyUseHTML('<div class="progress" style="width:120px; margin-bottom:0px;">
                    <div class="progress-bar progress-bar-info" style="width:' . e(($maxAmount > 0) ? ($product->totalamount / $maxAmount) * 100 : 0) . '%;"></div>
                </div>'),
            ];
        });

        // return datatable JSON
        return response(dataTableGroupBy($dataTable, $this->_base_query()));
    }

    /**
     * CSV output
     */
    public function export()
    {
        user()->canOrRedirect('reports.orders_by_product');

        // generate data table
        $productOrders = $this->_base_query()
            ->select(
                'product.id',
                'product.code',
                'product.name',
                DB::raw('MIN(productorder.ordered_at) as firstpurchasedatetime'),
                DB::raw('MAX(productorder.ordered_at) as lastpurchasedatetime'),
                DB::raw('COUNT(distinct productorder.id) as ordercount'),
                DB::raw('SUM(productorderitem.qty) as quantitypurchased'),
                DB::raw('SUM(productorderitem.qty*(productorderitem.price*productorder.functional_exchange_rate)) as totalamount')
            )
            ->toBase()
            ->get();

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('orders_by_product.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Code', 'Name', 'First Purchase', 'Last Purchase', 'Contributions', 'Qty', 'Amount'], ',', '"');
        foreach ($productOrders as $product) {
            fputcsv($outstream, [
                $product->code,
                $product->name,
                toLocalFormat($product->firstpurchasedatetime, 'csv'),
                toLocalFormat($product->lastpurchasedatetime, 'csv'),
                number_format($product->ordercount),
                number_format($product->quantitypurchased),
                number_format($product->totalamount, 2),
            ], ',', '"');
        }
        fclose($outstream);
        exit;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _base_query($group_by = true)
    {
        $productOrders = OrderItem::query();

        $productOrders->join('productorder', function ($join) {
            $join->on('productorder.id', '=', 'productorderitem.productorderid')
                ->whereNull('productorder.deleted_at')
                ->where('productorder.is_processed', '=', 1);
        }, null, null, 'inner');

        $productOrders->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid', 'inner');
        $productOrders->join('product', 'product.id', '=', 'productinventory.productid', 'inner');

        if ($group_by) {
            $productOrders->groupBy('product.id', 'product.code', 'product.name');
        }

        $filters = (object) [];

        // search
        $filters->search = request('search');
        if ($filters->search) {
            $productOrders->where(function ($query) use ($filters) {
                $query->where('product.name', 'like', "%$filters->search%");
                $query->orWhere('product.code', 'like', "%$filters->search%");
                $query->orWhere('productinventory.variantname', 'like', "%$filters->search%");
            });
        }

        // category
        $filters->category_id = request('category_id');
        if ($filters->category_id) {
            $productOrders->whereRaw('product.id IN (SELECT productid FROM productcategorylink WHERE categoryid = ?)', [$filters->category_id]);
        }

        // deleted
        $filters->is_deleted = request('is_deleted');
        if ($filters->is_deleted) {
            $productOrders->whereNotNull('product.deleted_at');
        } else {
            $productOrders->whereNull('product.deleted_at');
        }

        // dates
        $filters->ordered_at_str = fromLocal(request('ordered_at_str'));
        $filters->ordered_at_end = fromLocal(request('ordered_at_end'));
        if ($filters->ordered_at_str && $filters->ordered_at_end) {
            $productOrders->whereBetween('productorder.ordered_at', [
                toUtc($filters->ordered_at_str->startOfDay()),
                toUtc($filters->ordered_at_end->endOfDay()),
            ]);
        } elseif ($filters->ordered_at_str) {
            $productOrders->where('productorder.ordered_at', '>=', toUtc($filters->ordered_at_str->startOfDay()));
        } elseif ($filters->ordered_at_end) {
            $productOrders->where('productorder.ordered_at', '<=', toUtc($filters->ordered_at_end->endOfDay()));
        }

        return $productOrders;
    }
}
