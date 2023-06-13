<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\AccountType;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\ProductCustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;

class PaymentsByItemController extends Controller
{
    public function index()
    {
        $countries = collect(Order::getDistinctValuesOf('ip_country'))->map(function ($country_code) {
            return [
                'code' => $country_code,
                'name' => cart_countries()[$country_code],
            ];
        })->sortBy('name');

        $items = Product::with('variants')->get()->flatMap(function ($product) {
            return $product->variants->map(function ($variant) use ($product) {
                return [
                    'id' => $variant->id,
                    'name' => $product->name . ' - ' . $variant->variantname,
                ];
            });
        })->sortBy('name');

        return $this->getView('reports/payments-by-item', [
            'pageTitle' => 'Payments by Item',
            'items' => $items,
            'countries' => $countries,
            'account_types' => AccountType::all(),
            'categories' => ProductCategory::topLevel()->with('childCategories.childCategories.childCategories.childCategories')->orderBy('sequence')->get(),
            'currencies' => Currency::getLocalCurrencies(),
            'gateways' => Payment::getDistinctValuesOf('gateway_type'),
            'memberships' => Membership::all(),
            '__menu' => 'reports.payments-by-item',
        ]);
    }

    public function get()
    {
        $payments = $this->_base_query();

        // generate data table
        $dataTable = new DataTable($payments, [
            new ExpressionWithName('payments.description', 'description'),
            new ExpressionWithName("concat(product.name, ' - ', productinventory.variantname)", 'item_description'),
            new ExpressionWithName('productorderitem.qty', 'item_qty'),
            new ExpressionWithName('(CASE WHEN productorderitem.recurring_frequency IS NOT NULL THEN productorderitem.recurring_amount ELSE productorderitem.price END)', 'item_price'),
            new ExpressionWithName('coalesce(productorderitem.general_ledger_code, product.meta1)', 'gl_code'),
            new ExpressionWithName("concat(productorder.billing_first_name, ' ', productorder.billing_last_name)", 'billing_name'),
            new ExpressionWithName('payments.amount', 'amount'),
            new ExpressionWithName("(CASE WHEN payments.type = 'card' THEN payments.card_brand ELSE payments.type END)", 'payment_type'),
            new ExpressionWithName('payments.reference_number', 'reference_number'),
            new ExpressionWithName('payments.created_at', 'created_at'),
            new ExpressionWithName('payments.captured_at', 'captured_at'),
            new ExpressionWithName('payments.failure_message', 'failure_message'),

            new ExpressionWithName('payments.amount_refunded', 'amount_refunded'),
            new ExpressionWithName('payments_pivot.order_id', 'order_id'),
            new ExpressionWithName('payments_pivot.recurring_payment_profile_id', 'recurring_payment_profile_id'),
            new ExpressionWithName('product.id', 'product_id'),
            new ExpressionWithName('payments.currency', 'currency'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($row) {
            if (Str::startsWith($row->description, 'Payment for Contribution')) {
                $links = preg_replace('/^Payment for Contributions? /', '', $row->description);
                $links = collect(explode(',', $links))->map(function ($id) {
                    $id = trim($id, '# ');

                    return sprintf('<a href="%s">#%s</a>', e(route('backend.orders.edit_without_id', ['c' => $id])), e($id));
                });
                $description = Str::plural('Payment for Contribution', count($links)) . ' ' . $links->implode(', ');
            } elseif ($row->order_id) {
                $description = sprintf('<a href="%s">%s</a>', e(route('backend.orders.edit', $row->order_id)), e($row->description));
            } elseif (Str::startsWith($row->description, 'Payment for Recurring Payment Profile')) {
                $links = preg_replace('/^Payment for Recurring Payment Profiles? /', '', $row->description);
                $links = collect(explode(',', $links))->map(function ($id) {
                    $id = trim($id, '# ');

                    return sprintf('<a href="/jpanel/recurring_payments/%s">#%s</a>', e($id), e($id));
                });
                $description = Str::plural('Payment for Recurring Payment Profile', count($links)) . ' ' . $links->implode(', ');
            } elseif ($row->recurring_payment_profile_id) {
                $description = sprintf('<a href="/jpanel/recurring_payments?id=%s">%s</a>', e($row->recurring_payment_profile_id), e($row->description));
            } else {
                $description = e($row->description);
            }

            return [
                dangerouslyUseHTML($description),
                dangerouslyUseHTML('<a href="/jpanel/products/edit?i=' . e($row->product_id) . '">' . e($row->item_description) . '</a>'),
                e($row->item_qty),
                e(number_format($row->item_price, 2)),
                e($row->gl_code),
                e(trim($row->billing_name)),
                e(number_format($row->amount, 2) . ' ' . $row->currency),
                e(ucwords($row->payment_type)),
                e($row->reference_number),
                e(toLocalFormat($row->created_at, 'M j, Y h:i a')),
                e(toLocalFormat($row->captured_at, 'M j, Y')),
                e($row->failure_message),

                (float) $row->amount_refunded,
            ];
        });

        return response($dataTable->make());
    }

    public function export()
    {
        set_time_limit(60 * 2); // 2 minutes

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=payments-by-item.csv');
        header('Expires: 0');
        header('Pragma: public');
        $out_file = fopen('php://output', 'w');

        $payments = $this->_base_query()
            ->select([
                'payments.*',
                DB::raw("concat(product.name, ' - ', productinventory.variantname) as item_description"),
                DB::raw('productorderitem.qty as item_qty'),
                DB::raw('if(transactions.id is null, productorderitem.price, transactions.amt) as item_price'),
                DB::raw('productorderitem.id as item_id'),
                DB::raw('coalesce(productorderitem.general_ledger_code, product.meta1) as gl_code'),
                DB::raw("concat(productorder.billing_first_name, ' ', productorder.billing_last_name) as billing_name"),
                DB::raw('productorder.billingemail as billing_email'),
                DB::raw('productorder.billingphone as billing_phone'),
                DB::raw('productorder.billingaddress1 as billing_address1'),
                DB::raw('productorder.billingaddress2 as billing_address2'),
                DB::raw('productorder.billingcity as billing_city'),
                DB::raw('productorder.billingstate as billing_state'),
                DB::raw('productorder.billingzip as billing_zip'),
                DB::raw('productorder.billingcountry as billing_country'),
                DB::raw('productorder.referral_source as referral_source'),
                DB::raw('productorder.http_referer as http_referer'),
                DB::raw('productorder.tracking_source as tracking_source'),
                DB::raw('productorder.tracking_medium as tracking_medium'),
                DB::raw('productorder.tracking_campaign as tracking_campaign'),
            ]);

        $custom_fields = ProductCustomField::query()
            ->whereIn('productid', $this->_base_query()->select('product.id')->distinct()->pluck('id'))
            ->orderBy('productid')
            ->orderBy('sequence')
            ->select('name')
            ->get()
            ->unique('name');

        $headers = [
            'Item',
            'Qty',
            'Price',
            'GL',
        ];
        $headers = array_merge($headers, $custom_fields->pluck('name')->all());
        $headers = array_merge($headers, [
            'Billing Name',
            'Billing Email',
            'Billing Phone',
            'Billing Address',
            'Billing Address 2',
            'Billing City',
            'Billing Province/State',
            'Billing Postal/ZIP',
            'Billing Country',
            'Amount',
            'Currency',
            'Reference',
            'Time',
            'Captured At',
            'Gateway',
            'Card',
            'Failure Message',
            'Description',
            'Tracking Source',
            'Tracking Medium',
            'Tracking Campaign',
            'Referral Source',
            'Referral Website',
            'Payment Method',
        ]);
        fputcsv($out_file, $headers);

        $payments->chunk(250, function ($payment_chunk) use ($out_file, $custom_fields) {
            // eager load a chunk of order items
            $items_chunk = OrderItem::with('fields')
                ->whereIn('id', $payment_chunk->pluck('item_id'))
                ->get();

            foreach ($payment_chunk as $payment) {
                $item = $items_chunk->where('id', $payment->item_id)->first();

                $field_values = [];
                foreach ($custom_fields->pluck('name') as $field_name) {
                    $field_values[] = $item->fields->where('name', $field_name)->first()->value ?? null;
                }

                $row = [
                    $payment->item_description,
                    $payment->item_qty,
                    $payment->item_price,
                    $payment->gl_code,
                ];
                $row = array_merge($row, $field_values);
                $row = array_merge($row, [
                    trim($payment->billing_name),
                    $payment->billing_email,
                    $payment->billing_phone,
                    $payment->billing_address1,
                    $payment->billing_address2,
                    $payment->billing_city,
                    $payment->billing_state,
                    $payment->billing_zip,
                    $payment->billing_country,
                    $payment->amount,
                    $payment->currency,
                    $payment->reference_number,
                    toLocalFormat($payment->created_at, 'csv'),
                    toLocalFormat($payment->captured_at, 'csv'),
                    $payment->gateway_type,
                    $payment->card_brand,
                    $payment->failure_message,
                    $payment->description,
                    $payment->tracking_source,
                    $payment->tracking_medium,
                    $payment->tracking_campaign,
                    $payment->referral_source,
                    $payment->http_referer,
                    $payment->source_type,
                ]);

                fputcsv($out_file, $row);
            }
        });

        exit;
    }

    private function _base_query()
    {
        $query = Payment::query()
            ->join('payments_pivot', 'payments_pivot.payment_id', '=', 'payments.id')
            ->leftJoin('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'payments_pivot.recurring_payment_profile_id')
            ->leftJoin('transactions', 'transactions.id', '=', 'payments_pivot.transaction_id')
            ->join('productorder', 'productorder.id', '=', DB::raw('IFNULL(`payments_pivot`.`order_id`, `recurring_payment_profiles`.`productorder_id`)'))
            ->join('productorderitem', function ($join) {
                $join->on('productorderitem.productorderid', '=', 'productorder.id')
                    ->where('productorderitem.id', '=', DB::raw('IFNULL(`recurring_payment_profiles`.`productorderitem_id`, `productorderitem`.`id`)'));
            })->join('productinventory', function ($join) {
                $join->on('productinventory.id', '=', 'productorderitem.productinventoryid');
                // item ids
                if (request('i')) {
                    $join->whereIn('productinventory.id', request('i'));
                }
            })->join('product', 'product.id', '=', 'productinventory.productid');

        $query->whereNull('productorder.deleted_at');

        // category ids
        if (request('c')) {
            $query->join('productcategorylink', function ($join) {
                $join->on('productcategorylink.productid', '=', 'product.id')
                    ->whereIn('productcategorylink.categoryid', request('c'));
            });
        }

        // status
        if (request('s') == 'success') {
            $query->where('payments.paid', '=', 1);
        } elseif (request('s') == 'failed') {
            $query->where('payments.paid', '=', 0);
        }

        // gift aid eligible
        if (request('fga') != null) {
            $query->where('productorderitem.gift_aid', '=', request('fga'));
        }
        // capture date
        $captured_at_str = fromLocal(request('fd1'));
        $captured_at_end = fromLocal(request('fd2'));
        if ($captured_at_str) {
            $query->where('payments.captured_at', '>=', toUtc($captured_at_str->startOfDay()));
        }
        if ($captured_at_end) {
            $query->where('payments.captured_at', '<=', toUtc($captured_at_end->endOfDay()));
        }

        // created (attmpted) date
        $created_at_str = fromLocal(request('fc1'));
        $created_at_end = fromLocal(request('fc2'));
        if ($created_at_str) {
            $query->where('payments.created_at', '>=', toUtc($created_at_str->startOfDay()));
        }
        if ($created_at_end) {
            $query->where('payments.created_at', '<=', toUtc($created_at_end->endOfDay()));
        }

        $currency_code = request('cc');
        if ($currency_code) {
            $query->where('payments.currency', $currency_code);
        }

        if (request('fg')) {
            $query->where('payments.gateway_type', '=', request('fg'));
        }

        // ip_country
        if (request('foi')) {
            $query->where('productorder.ip_country', '=', request('foi'));
        }

        $account_type = request('fat');
        if ($account_type) {
            $query->whereHas('account', function ($query) use ($account_type) {
                $query->where('account_type_id', $account_type);
            });
        }

        // memberships
        if (request('fmm')) {
            $query->leftJoin('group_account as fmGA', 'fmGA.account_id', 'payments.source_account_id');
            $query->leftJoin('membership as fmG', 'fmG.id', 'fmGA.group_id');
            $query->where('fmGA.group_id', request('fmm'));
            $query->where(function ($query) {
                $query->whereNull('fmGA.start_date');
                $query->orWhere('fmGA.start_date', '<=', fromLocal('today'));
            });
            $query->where(function ($query) {
                $query->whereNull('fmGA.end_date');
                $query->orWhere('fmGA.end_date', '>=', fromLocal('today'));
            });
        }

        return $query;
    }
}
