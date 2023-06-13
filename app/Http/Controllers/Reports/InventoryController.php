<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        return $this->getView('reports/inventory', [
            '__menu' => 'reports.inventory',
        ]);
    }

    public function export()
    {
        if (! request('start_date') || ! request('end_date') || ! request('category_ids')) {
            $this->flash->error('This report must be filtered by at least one category and two dates.');

            return redirect()->to('jpanel/reports/inventory-export');
        }

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=inventory-export.csv');
        header('Expires: 0');
        header('Pragma: public');

        $all_products = Product::query()->orderBy('name');

        if (request('category_ids')) {
            $all_products->whereHas('categories', function ($q) {
                $q->whereIn('categoryid', request('category_ids'));
            });
        }

        $variant_names = Variant::whereIn('productid', $all_products->pluck('id'))
            ->select('variantname')
            ->orderBy('productid')
            ->orderBy('sequence')
            ->pluck('variantname')
            ->unique();

        $headers = ['Product', 'Code', 'Contributions', 'Qty', 'Amount'];
        $headers = array_merge($headers, ['Sold'], $variant_names->all(), ['Remaining'], $variant_names->all());

        $out_file = fopen('php://output', 'w');
        fputcsv($out_file, $headers);

        $all_products = $all_products->with(['variants.stockAdjustments' => function ($adjustments) {
            $adjustments->select(
                'variant_id',
                DB::raw('count(*) as `adjustment_count`'),
                DB::raw('sum(quantity) as `adjustment_total`')
            )
                ->groupBy('variant_id')
                ->where('state', 'sold')
                ->whereBetween('occurred_at', [request('start_date'), request('end_date')]);
        }])->with(['orderItems' => function ($items) {
            $items->select(
                'productorderitem.productinventoryid',
                DB::raw('count(distinct productorderitem.productorderid) as `order_count`'),
                DB::raw('sum(productorderitem.qty) as `quantity_total`'),
                DB::raw('sum(productorderitem.price*qty) as `amount_total`')
            )
                ->groupBy('productorderitem.productinventoryid')
                ->whereHas('order', function ($orders) {
                    $orders->whereBetween('confirmationdatetime', [request('start_date'), request('end_date')]);
                });
        }]);

        $all_products->chunk(50, function ($products) use (&$out_file, $variant_names) {
            $products->each(function ($product) use (&$out_file, $variant_names) {
                $row = [$product->name, $product->code];

                $row[] = number_format($product->orderItems->sum('order_count') ?? 0);
                $row[] = number_format($product->orderItems->sum('quantity_total') ?? 0);
                $row[] = number_format($product->orderItems->sum('amount_total') ?? 0, 2);

                // total sold
                $total_sold = 0;
                $product->variants->each(function ($variant) use (&$total_sold) {
                    $total_sold += $variant->stockAdjustments->sum('adjustment_total') ?? 0;
                });

                $row[] = $total_sold;

                // total by variant name
                $variant_names->each(function ($variant_name) use (&$row, $product) {
                    $variant = $product->variants
                        ->first(function ($v) use ($variant_name) {
                            return trim($v->variantname) == trim($variant_name);
                        });

                    if ($variant->stockAdjustments) {
                        $row[] = number_format($variant->stockAdjustments->sum('adjustment_total') ?? 0);
                    } else {
                        $row[] = '';
                    }
                });

                // total remaining
                $row[] = number_format($product->variants->sum('quantity') ?? 0);

                // total by variant name
                $variant_names->each(function ($variant_name) use (&$row, $product) {
                    $variant = $product->variants
                        ->first(function ($v) use ($variant_name) {
                            return trim($v->variantname) == trim($variant_name);
                        });

                    if ($variant) {
                        $row[] = number_format($variant->quantity ?? 0);
                    } else {
                        $row[] = '';
                    }
                });

                fputcsv($out_file, $row);
            });
        });

        exit;
    }
}
