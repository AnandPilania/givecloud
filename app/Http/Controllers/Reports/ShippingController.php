<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;
use Ds\Models\Order;

class ShippingController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.shipping');

        $__menu = 'reports.shipping';

        request()->merge([
            'fd1' => request('fd1', fromLocal('now')->format('Y-m-01')),
            'fd2' => request('fd2', fromLocal('now')->format('Y-m-t')),
        ]);

        $title = 'Shipping';

        pageSetup($title, 'jpanel');

        $shipping = Order::query()
            ->where('is_processed', true)
            ->where('shipping_amount', '>', 0);

        if (request('fd1') && request('fd2')) {
            $shipping->whereBetween('createddatetime', [
                fromUtc(request('fd1'))->startOfDay(),
                fromUtc(request('fd2'))->endOfDay(),
            ]);
        } elseif (request('fd1')) {
            $shipping->where('createddatetime', '>=', fromUtc(request('fd1'))->startOfDay());
        } elseif (request('fd2')) {
            $shipping->where('createddatetime', '<=', fromUtc(request('fd2'))->endOfDay());
        }

        $shipping = $shipping->get();

        $shipping_total_amount = 0;
        $shipping_total_orders = count($shipping);
        foreach ($shipping as $o) {
            $shipping_total_amount += (float) money($o->shipping_amount, $o->currency_code)->toCurrency(sys_get('dpo_currency'))->getAmount();
        }

        return $this->getView('reports/shipping', compact('__menu', 'title', 'shipping', 'shipping_total_amount', 'shipping_total_orders'));
    }

    public function export()
    {
        user()->canOrRedirect('reports.shipping');

        $shipping = Order::query()
            ->where('is_processed', true)
            ->where('shipping_amount', '>', 0);

        if (request('fd1') && request('fd2')) {
            $shipping->whereBetween('createddatetime', [
                fromUtc(request('fd1'))->startOfDay(),
                fromUtc(request('fd2'))->endOfDay(),
            ]);
        } elseif (request('fd1')) {
            $shipping->where('createddatetime', '>=', fromUtc(request('fd1'))->startOfDay());
        } elseif (request('fd2')) {
            $shipping->where('createddatetime', '<=', fromUtc(request('fd2'))->endOfDay());
        }

        $shipping = $shipping->get();

        $filename = toLocalFormat(request('fd1'), 'Y-m-d') . '_' . toLocalFormat(request('fd2'), 'Y-m-d') . '_Shipping.csv';
        $filename = sanitize_filename($filename);

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename="' . $filename . '"');

        echo 'Date,Contribution,Ship-To,Total,Shipping,Currency' . chr(10);

        foreach ($shipping as $order) {
            echo toLocalFormat($order->confirmationdatetime, 'Y-m-d') . ',' . $order->invoicenumber . ',"' . ($order->shipcity . ', ' . $order->shipstate . ', ' . $order->shipzip) . '",' . $order->totalamount . ',' . $order->shipping_amount . ',' . $order->currency_code . chr(10);
        }

        exit;
    }
}
