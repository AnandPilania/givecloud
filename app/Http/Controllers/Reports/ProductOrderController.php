<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class ProductOrderController extends Controller
{
    public function index($product_id)
    {
        user()->canOrRedirect('reports.product_orders');

        $__menu = 'reports.product-orders';

        $productModel = Product::withTrashed()->find($product_id);

        $productModel->userCanOrRedirect('view');

        $title = 'Product Contributions Report: ' . $productModel->name;

        pageSetup($title, 'jpanel');

        $query = "SELECT p.id,
                o.id AS orderid,
                oi.id AS orderitemid,
                p.code,
                p.name,
                iv.variantname,
                oi.qty,
                o.ordered_at as createddatetime,
                o.confirmationnumber,
                o.billing_first_name,
                o.billing_last_name,
                CONCAT(o.billing_first_name,' ',o.billing_last_name) AS billingname,
                o.invoicenumber,
                (oi.price*oi.qty*o.functional_exchange_rate) AS price,
                1 as counter
            FROM productorderitem oi
            INNER JOIN productorder o ON o.id = oi.productorderid
            INNER JOIN productinventory iv ON iv.id = oi.productinventoryid
            INNER JOIN product p ON p.id = iv.productid
            WHERE o.is_processed = 1 and o.deleted_at is null ";

        // filters
        $query .= sprintf(' AND p.id = %d', db_real_escape_string($product_id)); // complete

        // ordering
        $query .= ' HAVING ordered_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) ORDER BY ordered_at';

        $qList = db_query($query);
        if (! $qList) {
            $qList_len = 0;
        } else {
            $qList_len = db_num_rows($qList);
        } // store the length

        // does this product have custom fields?
        $has_custom_fields = false;
        $qCField = db_query(sprintf(
            'SELECT COUNT(*) AS fieldcount
                FROM productfields
                WHERE productid = %d
                    AND deleted_at is null',
            db_real_escape_string($product_id)
        ));
        if ($qCField) {
            $cf = db_fetch_assoc($qCField);
            if ($cf['fieldcount'] > 0) {
                $has_custom_fields = true;
            }
        }

        // sales chart
        $end_date = toLocal($productModel->deleted_at) ?? fromLocal('today');
        $start_date = fromLocal('today')->subDays(59);

        $chart_data = query_to_dated_array($qList, $start_date, $end_date, 'createddatetime', 'price');

        $currencies = Currency::getLocalCurrencies();
        $countries = collect(Order::getDistinctValuesOf('ip_country'))->map(function ($country_code) {
            return [
                'code' => $country_code,
                'name' => cart_countries()[$country_code],
            ];
        })->sortBy('name');

        $filters = $this->_base_query($product_id, true);

        return $this->getView('products/orders', compact(
            '__menu',
            'productModel',
            'title',
            'query',
            'qList',
            'qList_len',
            'has_custom_fields',
            'qCField',
            'end_date',
            'start_date',
            'chart_data',
            'currencies',
            'countries',
            'filters',
        ));
    }

    public function get($product_id)
    {
        user()->canOrRedirect('reports.product_orders');

        $query = $this->_base_query($product_id);

        // generate data table
        $dataTable = new DataTable($query, [
            'productorderid',
            'ordered_at',
            'invoicenumber',
            'billing_first_name',
            'billing_last_name',
            'variantname',
            'qty',
            'productorderitem.price',
            new ExpressionWithName('productorderitem.price', 'col8'),
            'productorderitem.recurring_amount',
            'productorder.refunded_amt',
            'productorderitem.recurring_frequency',
            'is_test',
            'iscomplete',
            'productorder.functional_exchange_rate',
            'productorder.currency_code',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($order) {
            $money_format = '0,0.00' . (Currency::hasMultipleCurrencies() ? ' $$$' : '');

            return [
                dangerouslyUseHTML('<a href="' . e(route('backend.orders.edit', $order->productorderid)) . '"><i class="fa fa-search"></i></a>'),
                dangerouslyUseHTML(e(toLocalFormat($order->ordered_at)) . ' <small class="text-muted">' . e(toLocalFormat($order->ordered_at, 'g:iA')) . '</small>'),
                dangerouslyUseHTML(e($order->invoicenumber) . (($order->is_test) ? '&nbsp;<span class="pull-right label label-xs label-warning">TEST</span>' : '')),
                e($order->billing_first_name),
                e($order->billing_last_name),
                e($order->variantname),
                e(number_format($order->qty)),
                e(($order->productorderitemRecurringFrequency) ? money($order->productorderitemRecurringAmount, $order->productorderCurrencyCode)->format($money_format) : money($order->productorderitemPrice, $order->productorderCurrencyCode)->format($money_format)),
                dangerouslyUseHTML((($order->productorderRefundedAmt > 0) ? '<i class="fa fa-reply"></i> ' : '') . e(($order->productorderitemRecurringFrequency) ? money($order->productorderitemRecurringAmount * $order->qty, $order->productorderCurrencyCode)->format($money_format) : money($order->productorderitemPrice * $order->qty, $order->productorderCurrencyCode)->format($money_format))),
                e(number_format($order->productorderRefundedAmt)),
                e($order->is_test),
                e($order->iscomplete),
            ];
        });

        $data = $dataTable->make();

        $data['stats'] = $this->_base_query($product_id)->select([
            DB::raw('COUNT(DISTINCT productorder.id) as order_count'),
            DB::raw('SUM(productorderitem.qty) as total_qty'),
            DB::raw('SUM(productorderitem.qty*(productorderitem.price*productorder.functional_exchange_rate)) as total_sales'),
            DB::raw('SUM(productorderitem.qty*(productorderitem.recurring_amount*productorder.functional_exchange_rate)) as total_sales_recurring'),
            DB::raw('SUM(CASE WHEN productorderitem.recurring_amount > 0 AND productorderitem.price > 0 THEN 1 ELSE 0 END) as recurring_with_initial_payment_count'),
            DB::raw('SUM(CASE WHEN productorderitem.recurring_amount > 0 AND productorderitem.price > 0 THEN (productorderitem.qty*(productorderitem.price*productorder.functional_exchange_rate)) ELSE 0 END) as recurring_with_initial_payment_total'),
        ])->first();

        // return datatable JSON
        return response($data);
    }

    public function export($product_id)
    {
        user()->canOrRedirect('reports.product_orders');

        $productModel = Product::withTrashed()->find($product_id);

        $productModel->userCanOrRedirect('view');

        $orders_items = $this->_base_query($product_id)
            ->with('order', 'fields', 'variant.product');

        // add shippingmethod join
        $orders_items->join('shipping_method', 'shipping_method.id', '=', 'productorder.shipping_method_id', 'left');

        // check-ins
        $orders_items->join(DB::raw('(SELECT ch.order_item_id,
                        COUNT(*) AS check_in_count,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    GROUP BY ch.order_item_id) as check_ins'), function ($join) {
            $join->on('check_ins.order_item_id', '=', 'productorderitem.id');
        }, null, null, 'left');

        // select statement for CSV
        $orders_items->select([
            'productorderitem.*',
            DB::raw('check_ins.check_in_count'),
            DB::raw('check_ins.first_check_in'),
            DB::raw('check_ins.last_check_in'),
        ]);

        $productname_forfilename = preg_replace('/[^A-Za-z0-9]/', '', $productModel->name);
        $field_columns = $productModel->customFields;
        $field_column_headers = [];

        foreach ($field_columns as $column) {
            $field_column_headers[] = $column->name;
        }

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename="' . date('Y-m-d') . '_OrdersOf_' . $productname_forfilename . '.csv"');
        $outstream = fopen('php://output', 'w');

        $header = [
            'Date',
            'Contribution No.',
            'Product Name',
            'Code (SKU)',
            'Option',
            'Qty',
            'Price',
            'Total',
            'Currency',
            'Promocode',
            'Billing Title',
            'Billing First Name',
            'Billing Last Name',
            'Billing Organization Name',
            'Billing Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Card Type',
            'Card Number (Last 4 Digits)',
            'Shipping Title', 'Shipping First Name',
            'Shipping Last Name',
            'Shipping Organization Name',
            'Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Shipping Method',
            'DPO Donor#',
            'DPO Gift#',
            'Special Notes',
            'Check-In Count',
            'First Check-In',
            'Last Check-In',
            'Refunded Date',
            'Refunded Amount',
            'Refunded Auth',
        ];

        // add custom field headers
        $header = array_merge($header, $field_column_headers);

        // header row
        fputcsv($outstream, $header, ',', '"');

        // chunk orders in 150
        $orders_items->chunk(150, function ($orders_items_chunk) use ($outstream, $field_columns) {
            foreach ($orders_items_chunk as $order_item) {
                $row = [
                    toLocalFormat($order_item->order->ordered_at, 'csv'),
                    $order_item->order->invoicenumber,
                    $order_item->reference,
                    $order_item->code,
                    $order_item->description,
                    $order_item->qty,
                    (($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price),
                    ((($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price) * $order_item->qty),
                    $order_item->order->currency_code,
                    $order_item->promocode,
                    $order_item->order->billing_title,
                    $order_item->order->billing_first_name,
                    $order_item->order->billing_last_name,
                    $order_item->order->billing_organization_name,
                    $order_item->order->billingaddress1,
                    $order_item->order->billingaddress2,
                    $order_item->order->billingcity,
                    $order_item->order->billingstate,
                    $order_item->order->billingzip,
                    $order_item->order->billingcountry,
                    $order_item->order->billingemail,
                    $order_item->order->billingphone,
                    $order_item->order->billingcardtype,
                    $order_item->order->billingcardlastfour,
                    $order_item->order->shipping_title,
                    $order_item->order->shipping_first_name,
                    $order_item->order->shipping_last_name,
                    $order_item->order->shipping_organization_name,
                    $order_item->order->shipaddress1,
                    $order_item->order->shipaddress2,
                    $order_item->order->shipcity,
                    $order_item->order->shipstate,
                    $order_item->order->shipzip,
                    $order_item->order->shipcountry,
                    $order_item->order->shipemail,
                    $order_item->order->shipphone,
                    $order_item->order->shipping_method_name,
                    $order_item->order->alt_contact_id,
                    $order_item->alt_transaction_id,
                    $order_item->order->comments,
                    $order_item->order->check_in_count,
                    toLocalFormat($order_item->order->first_check_in, 'csv'),
                    toLocalFormat($order_item->order->last_check_in, 'csv'),
                    toLocalFormat($order_item->order->refunded_at, 'csv'),
                    $order_item->order->refunded_amt,
                    $order_item->order->refunded_auth,
                ];

                $fields = $order_item->fields->pluck('value', 'id')->all();
                foreach ($field_columns as $field) {
                    $row[] = $fields[$field->id] ?? null;
                }

                fputcsv($outstream, $row, ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    public function export_with_items($product_id)
    {
        user()->canOrRedirect('reports.product_orders');

        $productModel = Product::withTrashed()->find($product_id);

        $productModel->userCanOrRedirect('view');

        $other_product_ids = Arr::wrap(request('product_ids'));

        $order_items = $this->_base_query($product_id)
            ->with([
                'order.shippingMethod',
                'fields',
                'orderItems' => function ($qry) use ($other_product_ids) {
                    $qry->select('productorderitem.*', 'productinventory.productid as product_id')
                        ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
                        ->whereIn('productinventory.productid', $other_product_ids)
                        ->with('variant', 'sponsorship');
                },
            ]);

        // check-ins
        $order_items->join(DB::raw('(SELECT ch.order_item_id,
                        COUNT(*) AS check_in_count,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    GROUP BY ch.order_item_id) as check_ins'), function ($join) {
            $join->on('check_ins.order_item_id', '=', 'productorderitem.id');
        }, null, null, 'left');

        $order_items->select([
            'productorderitem.*',
            DB::raw('check_ins.check_in_count'),
            DB::raw('check_ins.first_check_in'),
            DB::raw('check_ins.last_check_in'),
        ]);

        $productname_forfilename = preg_replace('/[^A-Za-z0-9]/', '', $productModel->name);
        $field_columns = $productModel->customFields;
        $field_column_headers = [];

        foreach ($field_columns as $column) {
            $field_column_headers[] = $column->name;
        }

        $other_products = Product::whereIn('id', $other_product_ids)->with('customFields')->get();
        $other_product_headers = [];

        foreach ($other_products as $product) {
            $other_product_headers[] = $product->name;
            $other_product_headers[] = 'Code (SKU)';
            $other_product_headers[] = 'Qty';
            $other_product_headers[] = 'Price';
            $other_product_headers[] = 'Total';
            if (count($product->customFields) > 0) {
                foreach ($product->customFields as $field) {
                    $other_product_headers[] = $field->name;
                }
            }
        }

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv'); // text/csv
        header('Content-disposition: attachment; filename="' . date('Y-m-d') . '_OrdersOf_' . $productname_forfilename . '.csv"');
        $outstream = fopen('php://output', 'w');

        // headers
        $header = [
            'Date',
            'Contribution No.',
            'Product Name',
            'Code (SKU)',
            'Option',
            'Qty',
            'Price',
            'Total',
            'Currency',
            'Promocode',
            'Billing Title',
            'Billing First Name',
            'Billing Last Name',
            'Billing Organization Name',
            'Billing Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Card Type',
            'Card Number (Last 4 Digits)',
            'Shipping Title', 'Shipping First Name',
            'Shipping Last Name',
            'Shipping Organization Name',
            'Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Shipping Method',
            'Special Notes',
            'DPO Donor#',
            'DPO Gift#',
            'Comments',
            'Check-In Count',
            'First Check-In',
            'Last Check-In',
            'Refunded Date',
            'Refunded Amount',
            'Refunded Auth',
        ];

        $header = array_merge($header, $field_column_headers, $other_product_headers);

        // header row
        fputcsv($outstream, $header, ',', '"');

        // chunk orders in 150
        $order_items->chunk(150, function ($order_items_chunk) use ($outstream, $field_columns, $other_products) {
            foreach ($order_items_chunk as $order_item) {
                $row = [
                    toLocalFormat($order_item->order->ordered_at, 'csv'),
                    $order_item->order->invoicenumber,
                    $order_item->reference,
                    $order_item->code,
                    $order_item->description,
                    $order_item->qty,
                    (($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price),
                    ((($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price) * $order_item->qty),
                    $order_item->order->currency_code,
                    $order_item->promocode,
                    $order_item->order->billing_title,
                    $order_item->order->billing_first_name,
                    $order_item->order->billing_last_name,
                    $order_item->order->billing_organization_name,
                    $order_item->order->billingaddress1,
                    $order_item->order->billingaddress2,
                    $order_item->order->billingcity,
                    $order_item->order->billingstate,
                    $order_item->order->billingzip,
                    $order_item->order->billingcountry,
                    $order_item->order->billingemail,
                    $order_item->order->billingphone,
                    $order_item->order->billingcardtype,
                    $order_item->order->billingcardlastfour,
                    $order_item->order->shipping_title,
                    $order_item->order->shipping_first_name,
                    $order_item->order->shipping_last_name,
                    $order_item->order->shipping_organization_name,
                    $order_item->order->shipaddress1,
                    $order_item->order->shipaddress2,
                    $order_item->order->shipcity,
                    $order_item->order->shipstate,
                    $order_item->order->shipzip,
                    $order_item->order->shipcountry,
                    $order_item->order->shipemail,
                    $order_item->order->shipphone,
                    $order_item->order->shipping_method_name,
                    $order_item->order->comments,
                    $order_item->order->alt_contact_id,
                    $order_item->alt_transaction_id,
                    $order_item->order->customer_notes,
                    $order_item->check_in_count,
                    toLocalFormat($order_item->first_check_in, 'csv'),
                    toLocalFormat($order_item->last_check_in, 'csv'),
                    toLocalFormat($order_item->order->refunded_at, 'csv'),
                    $order_item->order->refunded_amt,
                    $order_item->order->refunded_auth,
                ];

                $fields = $order_item->fields->pluck('value', 'id')->all();
                foreach ($field_columns as $field) {
                    $row[] = $fields[$field->id] ?? null;
                }

                foreach ($other_products as $op) {
                    if ($other_item = $order_item->orderItems->where('product_id', $op->id)->first()) {
                        $row[] = $other_item->description;
                        $row[] = $other_item->code;
                        $row[] = $other_item->qty;
                        $row[] = $other_item->price;
                        $row[] = $other_item->total;
                        if (count($op->customFields) > 0) {
                            $fields = $other_item->fields->pluck('value', 'id')->all();
                            foreach ($op->customFields as $field) {
                                $row[] = $fields[$field->id] ?? null;
                            }
                        }
                    } else {
                        $row[] = '';
                        $row[] = '0';
                        $row[] = '0.00';
                        $row[] = '0.00';
                        if (count($op->customFields) > 0) {
                            foreach ($op->customFields as $field) {
                                $row[] = '';
                            }
                        }
                    }
                }

                fputcsv($outstream, $row, ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _base_query($product_id, bool $wantsFilters = false)
    {
        $orders = OrderItem::query();

        // joins
        $orders->join('productorder', function ($join) {
            $join->on('productorderitem.productorderid', '=', 'productorder.id')
                ->whereNull('productorder.deleted_at') // filter out soft-deletes
                ->whereRaw('productorder.confirmationdatetime is not null'); // only paid orders
        }, 'inner');

        $orders->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid', 'inner');
        $orders->join('product', 'product.id', '=', 'productinventory.productid', 'inner');

        // only the given product
        $orders->whereRaw('product.id = ' . $product_id);

        // search
        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $orders->where(function ($query) use ($filters) {
                $query->where(DB::raw("concat(productorder.billing_first_name,' ',productorder.billing_last_name)"), 'like', "%$filters->search%");
                $query->orWhere(DB::raw("concat(productorder.shipping_first_name,' ',productorder.shipping_last_name)"), 'like', "%$filters->search%");
                $query->orWhere(DB::raw('productorder.invoicenumber'), 'like', "%$filters->search%");
            });
        }

        // completed
        switch (request('c')) {
            case '0': $orders->where('iscomplete', '=', '0'); break;
            case '1': $orders->where('iscomplete', '=', '1'); break;
            case '2': $orders->where('refunded_amt', '>', 0); break;
        }

        // issued date
        $filters->ordered_at_str = fromLocal(request('ordered_at_str'));
        $filters->ordered_at_end = fromLocal(request('ordered_at_end'));
        if ($filters->ordered_at_str && $filters->ordered_at_end) {
            $orders->whereBetween('productorder.ordered_at', [
                toUtc($filters->ordered_at_str->startOfDay()),
                toUtc($filters->ordered_at_end->endOfDay()),
            ]);
        } elseif ($filters->ordered_at_str) {
            $orders->where('productorder.ordered_at', '>=', toUtc($filters->ordered_at_str->startOfDay()));
        } elseif ($filters->ordered_at_end) {
            $orders->where('productorder.ordered_at', '<=', toUtc($filters->ordered_at_end->endOfDay()));
        }

        // total amount
        $filters->total_str = request('total_str');
        $filters->total_end = request('total_end');
        if ($filters->total_str && $filters->total_end) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) >= ?', [$filters->total_str]);
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) <= ?', [$filters->total_end]);
        } elseif ($filters->total_str) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) >= ?', [$filters->total_str]);
        } elseif ($filters->total_end) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) <= ?', [$filters->total_end]);
        }

        $currency_code = request('cc');
        if ($currency_code) {
            $orders->where('productorder.currency_code', $currency_code);
        }

        // ip_country
        if (request('foi')) {
            $orders->where('productorder.ip_country', '=', request('foi'));
        }

        // gift aid eligible
        if (request('fga') != null) {
            $orders->where('productorderitem.gift_aid', '=', request('fga'));
        }

        if ($wantsFilters) {
            return $filters;
        }

        // return base query
        return $orders;
    }
}
