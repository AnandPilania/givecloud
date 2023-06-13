<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Flatfile\Services\Contributions as FlatfileContributionsService;
use Ds\Domain\Shared\DataTable;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Http\Controllers\Frontend\API\Services\LocaleController;
use Ds\Jobs\CalculateLifetimeMemberGiving;
use Ds\Models\CheckIn;
use Ds\Models\GroupAccount;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Variant;
use Ds\Services\Order\OrderEmailPreferencesService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;
use Throwable;

class OrderController extends Controller
{
    /**
     * Main orders list.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        user()->canOrRedirect('order');

        if (Order::doesntExist()) {
            return view('orders.empty');
        }

        $title = 'Contributions';
        if (request('fU')) {
            $title .= ': Unsynced';
        }

        pageSetup($title, 'jpanel');

        return view('orders.index', [
            'unsynced_count' => Order::paid()->unsynced()->count(),
            'donation_forms' => Product::query()->donationForms()->get(),
            'flatfileToken' => app(FlatfileContributionsService::class)->token(),
        ]);
    }

    /**
     * Used for datatable fetches.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing()
    {
        // check permission
        user()->canOrRedirect('order');

        // orders query
        $orders = $this->_baseOrdersQueryWithFilters()->with([
            'member.latestAvatar',
            'payments',
            'items.sponsorship',
            'items.metadataRelation',
            'items.variant.metadataRelation',
            'items.variant.product',
        ]);

        // generate data table
        $dataTable = new DataTable($orders, [
            new ExpressionWithName('productorder.id', 'id'),
            new ExpressionWithName('productorder.id', 'col2'), // Images
            new ExpressionWithName("COALESCE(member.display_name, NULLIF(CASE WHEN billing_organization_name IS NULL THEN TRIM(CONCAT(COALESCE(billing_first_name,''), ' ', COALESCE(billing_last_name,''))) ELSE billing_organization_name END, ''), 'Anonymous Donor')", 'display_name'),
            ['productorder.billingcountry', 'productorder.billingcity', 'productorder.billingstate'],
            new ExpressionWithName('productorder.id', 'col3'), // Status
            new ExpressionWithName('productorder.totalamount', 'totalamount'),
            new ExpressionWithName('productorder.subtotal', 'subtotal'),
            new ExpressionWithName('productorder.ordered_at', 'ordered_at'),

            // off the grid
            new ExpressionWithName('COALESCE(member.email, productorder.billingemail, productorder.shipemail)', 'email'),
            new ExpressionWithName('productorder.invoicenumber', 'invoicenumber'),
            new ExpressionWithName('productorder.confirmationdatetime', 'confirmationdatetime'),
            new ExpressionWithName('productorder.payment_type', 'payment_type'),
            new ExpressionWithName('productorder.currency_code', 'currency_code'),
            new ExpressionWithName('productorder.refunded_amt', 'refunded_amt'),
            new ExpressionWithName('productorder.dcc_total_amount', 'dcc_total_amount'),
            new ExpressionWithName('productorder.dp_sync_order', 'dp_sync_order'),
            new ExpressionWithName('productorder.billingcardtype', 'billingcardtype'),
            new ExpressionWithName('productorder.is_spam', 'is_spam'),
            new ExpressionWithName('productorder.is_test', 'is_test'),
            new ExpressionWithName('productorder.billingcity', 'billingcity'),
            new ExpressionWithName('productorder.billingstate', 'billingstate'),
            new ExpressionWithName('productorder.billingcountry', 'billingcountry'),
            new ExpressionWithName('productorder.billingcardlastfour', 'billingcardlastfour'),
            new ExpressionWithName('productorder.iscomplete', 'iscomplete'),
            new ExpressionWithName('productorder.shippable_items', 'shippable_items'),
            new ExpressionWithName('productorder.alt_contact_id', 'alt_contact_id'),
            new ExpressionWithName('productorder.alt_transaction_id', 'alt_transaction_id'),
            new ExpressionWithName('productorder.member_id', 'member_id'),
            new ExpressionWithName('productorder.ip_country', 'ip_country'),
        ]);

        $checkboxView = view('orders._listing.checkbox');
        $thumbsView = view('orders._listing.thumbs');
        $supporterView = view('orders._listing.supporter');
        $locationView = view('orders._listing.location');
        $statusView = view('orders._listing.status');
        $paymentView = view('orders._listing.payment');
        $netView = view('orders._listing.net-amount');
        $dateView = view('orders._listing.date');

        $dataTable->setFormatRowFunction(function ($order) use (
            $checkboxView,
            $thumbsView,
            $supporterView,
            $locationView,
            $statusView,
            $paymentView,
            $netView,
            $dateView
        ) {
            return [
                $checkboxView->with(compact('order'))->render(),
                $thumbsView->with(compact('order'))->render(),
                $supporterView->with(compact('order'))->render(),
                $locationView->with(compact('order'))->render(),
                $statusView->with(compact('order'))->render(),
                $paymentView->with(compact('order'))->render(),
                $netView->with(compact('order'))->render(),
                $dateView->with(compact('order'))->render(),
                dangerouslyUseHTML('<a href="' . route('backend.orders.edit', $order->id) . '">View</a>'),
                e(route('backend.orders.edit', $order->id)),
            ];
        });

        return response($dataTable->withManualCount()->make());
    }

    /**
     * Exporting the main list of orders.
     */
    public function index_csv()
    {
        // increase timelimit (2 minutes)
        set_time_limit(2 * 60);

        // check permission
        user()->canOrRedirect('order');

        // orders query
        $orders = $this->_baseOrdersQueryWithFilters()
            ->select('productorder.*')
            ->with(['taxReceipts', 'member', 'createdBy']);

        $hasLocalCurrencies = Currency::hasLocalCurrencies();

        // headers
        $headers = ['Ordered At',
            'Contribution Number',
            'Source',
            'POS',
            'POS Entered Date/Time',
            'POS User',

            'Supporter Type',
            'Organization Name',
            'Billing Title',
            'Billing First Name',
            'Billing Last Name',
            'Billing Email',
            'Billing Phone',
            'Billing Address',
            'Billing Address 2',
            'Billing City',
            'Billing Province/State',
            'Billing Postal/ZIP',
            'Billing Country',
            'Items',
            'Total Weight',
            'Contribution Subtotal',
            'Shipping',
            'Tax',
            'DCC',
            'Contribution Total',
            'Currency',
        ];

        if ($hasLocalCurrencies) {
            $headers = array_merge($headers, [
                'FX Rate',
                currency() . ' Contribution Total',
            ]);
        }

        $headers = array_merge($headers, [
            'Payment Type',
            'Payment Gateway Response',
            'Payment Gateway Confirmation Nunber',
            'Payment Gateway Confirmation Date/Time',
            'IP',
            'Browser',
            'Tracking Source',
            'Tracking Medium',
            'Tracking Campaign',
            'Check Number',
            'Check Date',
            'Check Amount',
            'Cash Received',
            'Change Given',
            'Other Payment Reference',
            'Other Payment Note',
            'Shipping Method',
            'Shipping Title',
            'Shipping First Name',
            'Shipping Last Name',
            'Shipping Organization',
            'Shipping Email',
            'Shipping Phone',
            'Shipping Address',
            'Shipping Address 2',
            'Shipping City',
            'Shipping Province/State',
            'Shipping Postal/ZIP',
            'Shipping Country',
            'Special Notes',
            'Fulfilled',
            'Refund Date/Time',
            'Refund Amount',
            'Refund Authorization',

            'Tax Receipt Date',
            'Tax Receipt Number',
            'Tax Receipt Amount',

            'Supporter ID',
            'Supporter Display Name',
            'Supporter Organization Name',
            'Supporter First Name',
            'Supporter Last Name',
            'Supporter Email',
            'Supporter Has a Login',
            'Referral Website',
        ]);

        // referral sources
        if (sys_get('referral_sources_isactive')) {
            $headers = array_merge($headers, ['Referral Source']);
        }

        // include dpo fields
        if (dpo_is_enabled()) {
            $headers = array_merge($headers, ['DP Donor ID', 'DP Gift IDs']);
        }

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('contributions.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $headers, ',', '"');

        // chunk over 1000 records at a time
        $orders->orderBy('id')->chunk(1000, function ($orders) use ($outstream, $hasLocalCurrencies) {
            foreach ($orders as $order) {
                $data = [
                    toLocalFormat($order->ordered_at, 'csv'),
                    $order->invoicenumber,
                    $order->source,
                    ($order->is_pos) ? 'Y' : 'N',
                    ($order->is_pos) ? toLocalFormat($order->createddatetime, 'csv') : '',
                    ($order->is_pos) ? $order->createdBy->full_name : '',
                    ($order->accountType) ? $order->accountType->name : '',
                    $order->billing_organization_name,
                    $order->billing_title,
                    $order->billing_first_name,
                    $order->billing_last_name,
                    $order->billingemail,
                    $order->billingphone,
                    $order->billingaddress1,
                    $order->billingaddress2,
                    $order->billingcity,
                    $order->billingstate,
                    $order->billingzip,
                    $order->billingcountry,
                    number_format($order->total_qty),
                    number_format($order->total_weight, 2),
                    number_format($order->subtotal, 2),
                    number_format($order->shipping_amount, 2),
                    number_format($order->taxtotal, 2),
                    number_format($order->dcc_total_amount, 2),
                    number_format($order->totalamount, 2),
                    $order->currency_code,
                ];

                if ($hasLocalCurrencies) {
                    $data = array_merge($data, [
                        $order->functional_exchange_rate,
                        numeral($order->functional_total),
                    ]);
                }

                $data = array_merge($data, [
                    $order->payment_type_formatted,
                    $order->response_text,
                    $order->confirmationnumber,
                    toLocalFormat($order->confirmationdatetime, 'csv'),
                    $order->client_ip,
                    ($order->client_browser) ? ua_formatted($order->client_browser) : '',
                    $order->tracking_source,
                    $order->tracking_medium,
                    $order->tracking_campaign,
                    $order->check_number,
                    $order->check_date,
                    $order->check_amt,
                    $order->cash_received,
                    $order->cash_change,
                    $order->payment_other_reference,
                    $order->payment_other_note,
                    $order->shipping_method_name,
                    $order->shipping_title,
                    $order->shipping_first_name,
                    $order->shipping_last_name,
                    $order->shipping_organization_name,
                    $order->shipemail,
                    $order->shipphone,
                    $order->shipaddress1,
                    $order->shipaddress2,
                    $order->shipcity,
                    $order->shipstate,
                    $order->shipzip,
                    $order->shipcountry,
                    $order->comments,
                    ($order->iscomplete) ? 'Y' : 'N',
                    toLocalFormat($order->refunded_at, 'csv'),
                    $order->refunded_amt,
                    $order->refunded_auth,

                    ($order->taxReceipt) ? $order->taxReceipt->issued_at : '',
                    ($order->taxReceipt) ? $order->taxReceipt->number : '',
                    $order->taxReceipt ? (($order->taxReceipt->amount) ? number_format($order->taxReceipt->amount, 2) : '') : '',

                    ($order->member) ? $order->member->id : '',
                    ($order->member) ? $order->member->display_name : '',
                    ($order->member) ? $order->member->bill_organization_name : '',
                    ($order->member) ? $order->member->first_name : '',
                    ($order->member) ? $order->member->last_name : '',
                    ($order->member) ? $order->member->email : '',
                    $order->member ? (($order->member->email && $order->member->password) ? 'Y' : 'N') : '',
                    $order->http_referer,
                ]);

                if (sys_get('referral_sources_isactive')) {
                    $data = array_merge($data, [
                        $order->referral_source,
                    ]);
                }

                if (dpo_is_enabled()) {
                    $data = array_merge($data, [
                        $order->alt_contact_id,
                        implode(chr(10), explode(',', $order->alt_transaction_id)),
                    ]);
                }

                fputcsv($outstream, $data, ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * Exporting the main list of orders with item details.
     */
    public function orders_with_items_csv()
    {
        // increase timelimit (2 minutes)
        set_time_limit(2 * 60);

        // check permission
        user()->canOrRedirect('order');

        // orders query
        $orders = $this->_baseOrdersQueryWithFilters()
            ->select('productorder.*')
            ->with('taxReceipts')
            ->with('member')
            ->with('items.variant.product')
            ->with('items.sponsorship')
            ->with('items.fields')
            ->with('items.tribute.tributeType');

        $hasLocalCurrencies = Currency::hasLocalCurrencies();

        // headers
        $headers = [
            'Ordered At',
            'Contribution Number',
            'Source',
            'POS',
            'POS Entered Date/Time',
            'POS User',

            'Item Code',
            'Item Name',
            'Option',
            'Item Cost',
            'Fair Market Value',
            'Item Price Paid',
            'Item Promo',
            'Item Qty',
            'Item DCC',
            'Item Total',
            'GL Account',
            'Custom Fields',

            'Recurring Frequency',
            'Recurring Initial Charge',
            'Recurring Day of Month',
            'Recurring Day of Week',

            'Supporter Type',
            'Organization Name',
            'Billing Title',
            'Billing First Name',
            'Billing Last Name',
            'Billing Email',
            'Billing Phone',
            'Billing Address',
            'Billing Address 2',
            'Billing City',
            'Billing Province/State',
            'Billing Postal/ZIP',
            'Billing Country',
            'Items',
            'Contribution Subtotal',
            'Shipping',
            'Tax',
            'DCC',
            'Contribution Total',
        ];

        if ($hasLocalCurrencies) {
            $headers = array_merge($headers, [
                'FX Rate',
                currency() . ' Contribution Total',
            ]);
        }

        $headers = array_merge($headers, [
            'Payment Type',
            'Payment Gateway Response',
            'Payment Gateway Confirmation Nunber',
            'Payment Gateway Confirmation Date/Time',
            'IP',
            'Browser',
            'Check Number',
            'Check Date',
            'Check Amount',
            'Cash Received',
            'Change Given',
            'Other Payment Reference',
            'Other Payment Note',
            'Shipping Method',
            'Shipping Title',
            'Shipping First Name',
            'Shipping Last Name',
            'Shipping Organization',
            'Shipping Email',
            'Shipping Phone',
            'Shipping Address',
            'Shipping Address 2',
            'Shipping City',
            'Shipping Province/State',
            'Shipping Postal/ZIP',
            'Shipping Country',
            'Special Notes',
            'Fulfilled',
            'Refund Date/Time',
            'Refund Amount',
            'Refund Authorization',

            'Tax Receipt Date',
            'Tax Receipt Number',
            'Tax Receipt Amount',

            'Supporter ID',
            'Supporter Display Name',
            'Supporter Organization Name',
            'Supporter First Name',
            'Supporter Last Name',
            'Supporter Email',
            'Supporter Has a Login',

            'Tribute Type',
            'Tribute Name',
            'Notify',
            'Notify Name',
            'Created on',
            'Notify on',
            'Sent on',
            'Notify Message',
            'Notify Email',
            'Notify Address',
            'Notify City',
            'Notify State',
            'Notify Zip',
            'Notify Country',
        ]);

        // include dpo fields
        if (dpo_is_enabled()) {
            $headers = array_merge($headers, ['DP Donor ID', 'DP Gift IDs']);
        }

        // referral sources
        if (sys_get('referral_sources_isactive')) {
            $headers = array_merge($headers, ['Referral Source', 'Referral Website']);
        }

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('contributions.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $headers, ',', '"');

        // chunk over 250 records at a time (smaller chunk becuase there are more relationships)
        $orders->orderBy('id')->chunk(250, function ($orders) use ($outstream, $hasLocalCurrencies) {
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $custom_fields = [];
                    foreach ($item->fields as $field) {
                        $custom_fields[] = $field->name . ': ' . $field->value_formatted;
                    }

                    $data = [
                        toLocalFormat($order->ordered_at, 'csv'),
                        $order->invoicenumber,
                        $order->source,
                        ($order->is_pos) ? 'Y' : 'N',
                        ($order->is_pos) ? toLocalFormat($order->createddatetime, 'csv') : '',
                        ($order->is_pos) ? $order->createdBy->full_name : '',

                        $item->code,
                        ($item->sponsorship) ? $item->sponsorship->full_name : $item->variant->product->name,
                        ($item->variant) ? $item->variant->variantname : '',
                        ($item->variant) ? number_format($item->variant->cost, 2) : '',
                        ($item->variant) ? number_format($item->variant->fair_market_value, 2) : '',
                        ($item->variant) ? number_format($item->price, 2) : '',
                        $item->promocode,
                        number_format($item->qty),
                        number_format($item->dcc_amount, 2),
                        number_format($item->total, 2),
                        ($item->sponsorship) ? $item->sponsorship->meta1 : $item->variant->product->meta1,
                        implode(chr(10), $custom_fields),

                        $item->recurring_frequency,
                        ($item->recurring_frequency) ? (($item->recurring_with_initial_charge) ? 'Y' : 'N') : '',
                        ($item->recurring_frequency) ? $item->recurring_day : '',
                        ($item->recurring_frequency == 'weekly') ? $item->recurring_day_of_week : '',

                        ($order->accountType) ? $order->accountType->name : '',
                        $order->billing_organization_name,
                        $order->billing_title,
                        $order->billing_first_name,
                        $order->billing_last_name,
                        $order->billingemail,
                        $order->billingphone,
                        $order->billingaddress1,
                        $order->billingaddress2,
                        $order->billingcity,
                        $order->billingstate,
                        $order->billingzip,
                        $order->billingcountry,
                        number_format($order->total_qty),
                        number_format($order->subtotal, 2),
                        number_format($order->shipping_amount, 2),
                        number_format($order->taxtotal, 2),
                        number_format($order->dcc_total_amount, 2),
                        number_format($order->totalamount, 2),
                    ];

                    if ($hasLocalCurrencies) {
                        $data = array_merge($data, [
                            $order->functional_exchange_rate,
                            numeral($order->functional_total),
                        ]);
                    }

                    $data = array_merge($data, [
                        $order->payment_type_formatted,
                        $order->response_text,
                        $order->confirmationnumber,
                        toLocalFormat($order->confirmationdatetime, 'csv'),
                        $order->client_ip,
                        ($order->client_browser) ? ua_formatted($order->client_browser) : '',
                        $order->check_number,
                        $order->check_date,
                        number_format($order->check_amt, 2),
                        $order->cash_received,
                        $order->cash_change,
                        $order->payment_other_reference,
                        $order->payment_other_note,
                        $order->shipping_method_name,
                        $order->shipping_title,
                        $order->shipping_first_name,
                        $order->shipping_last_name,
                        $order->shipping_organization_name,
                        $order->shipemail,
                        $order->shipphone,
                        $order->shipaddress1,
                        $order->shipaddress2,
                        $order->shipcity,
                        $order->shipstate,
                        $order->shipzip,
                        $order->shipcountry,
                        $order->comments,
                        ($order->iscomplete) ? 'Y' : 'N',
                        toLocalFormat($order->refunded_at, 'csv'),
                        number_format($order->refunded_amt, 2),
                        $order->refunded_auth,

                        ($order->taxReceipt) ? toLocalFormat($order->taxReceipt->issued_at, 'csv') : '',
                        ($order->taxReceipt) ? $order->taxReceipt->number : '',
                        $order->taxReceipt ? (($order->taxReceipt->amount) ? number_format($order->taxReceipt->amount, 2) : '') : '',

                        ($order->member) ? $order->member->id : '',
                        ($order->member) ? $order->member->display_name : '',
                        ($order->member) ? $order->member->bill_organization_name : '',
                        ($order->member) ? $order->member->first_name : '',
                        ($order->member) ? $order->member->last_name : '',
                        ($order->member) ? $order->member->email : '',
                        $order->member ? (($order->member->email && $order->member->password) ? 'Y' : 'N') : '',

                        $item->tribute && $item->tribute->tributeType ? $item->tribute->tributeType->label : '',
                        $item->tribute ? $item->tribute->name : '',
                        $item->tribute ? $item->tribute->notify : '',
                        $item->tribute ? $item->tribute->notify_name : '',
                        $item->tribute ? toLocalFormat($item->tribute->created_at, 'csv') : '',
                        $item->tribute ? toLocalFormat($item->tribute->notify_on, 'csv') : '',
                        $item->tribute ? toLocalFormat($item->tribute->notified_at, 'csv') : '',
                        $item->tribute ? $item->tribute->message : '',
                        $item->tribute ? $item->tribute->notify_email : '',
                        $item->tribute ? $item->tribute->notify_address : '',
                        $item->tribute ? $item->tribute->notify_city : '',
                        $item->tribute ? $item->tribute->notify_state : '',
                        $item->tribute ? $item->tribute->notify_zip : '',
                        $item->tribute ? $item->tribute->notify_country : '',
                    ]);

                    if (dpo_is_enabled()) {
                        $data = array_merge($data, [
                            $order->alt_contact_id,
                            implode(chr(10), explode(',', $order->alt_transaction_id)),
                        ]);
                    }

                    if (sys_get('referral_sources_isactive')) {
                        $data = array_merge($data, [
                            $order->referral_source,
                            $order->http_referer,
                        ]);
                    }

                    fputcsv($outstream, $data, ',', '"');
                }
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function _baseOrdersQueryWithFilters()
    {
        // base orders list query
        $orders = Order::paid()
            ->leftJoin('member', 'productorder.member_id', 'member.id')
            ->with('member');

        // /////////////////////
        // // FILTERS
        // /////////////////////

        // status
        switch (request('c')) {
            case '0': $orders->incomplete(); break;
            case '1': $orders->complete(); break;
            case '2': $orders->refunded(); break;
            case '3': $orders->withWarnings(); break;
            case '4': $orders->withoutWarnings(); break;
            case '5': $orders->unsynced(); break;
        }

        if (request('c') === '6') {
            $orders->onlySpam();
        }

        // selected ids
        if (request('ids')) {
            $orders->whereIn('productorder.id', explode(',', request('ids')));
        }

        // donor id
        if (request('d')) {
            $orders->where('productorder.alt_contact_id', (int) request('d'));
        }

        // search
        if (Str::startsWith(request('fO'), 'orders:')) {
            $uuids = array_map('trim', explode(',', substr(request('fO'), 7)));
            $orders->whereIn('productorder.client_uuid', $uuids);
        } elseif (Str::startsWith(request('fO'), 'ip:')) {
            $ips = trim(substr(request('fO'), 3), '()');
            $ips = array_map('trim', explode(' or ', strtolower($ips)));
            $orders->whereIn('productorder.client_ip', $ips);
        } elseif (request('fO')) {
            $keywords = array_map('trim', explode(' ', request('fO')));
            foreach ($keywords as $keyword) {
                $orders->where(function ($query) use ($keyword) {
                    $keyword = db_escape_like($keyword);
                    $query->where('productorder.invoicenumber', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.billing_first_name', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.billing_last_name', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.billingemail', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.shipping_first_name', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.shipping_last_name', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.shipemail', 'LIKE', "%{$keyword}%");
                    $query->orWhere('productorder.client_ip', '=', $keyword);
                    $query->orWhere('productorder.client_browser', 'LIKE', "%{$keyword}%");
                    $query->orWhere('member.display_name', 'LIKE', "%{$keyword}%");
                });
            }
        }

        // referral source
        if (request('fR')) {
            $orders->where(function ($query) {
                $query->whereIn('productorder.referral_source', request('fR'));

                if (in_array('Other', request('fR'))) {
                    $query->orWhere(function ($query) {
                        $query->whereNotNull('productorder.referral_source');
                        $query->whereNotIn('productorder.referral_source', explode(',', sys_get('referral_sources_options')));
                    });
                }
            });
        }

        // Donation forms
        if (request('df')) {
            $orders->whereHas('items', function ($query) {
                $query->whereHas('variant', function ($query) {
                    $ids = collect(request('df'))->map(fn ($hash) => Product::decodeHashid($hash));
                    $query->whereIn('productinventory.productid', $ids);
                });
            });
        }

        // suppporter
        if (request('fM')) {
            $orders->where('productorder.member_id', '=', (int) request('fM'));
        }

        // suppporter
        if (request('fat')) {
            $orders->whereIn('productorder.account_type_id', request('fat'));
        }

        // source
        if ($sources = request('fs')) {
            if (in_array('Point of Sale (POS)', $sources, true)) {
                $sources = collect($sources)->reject(function ($source) {
                    return $source === 'Point of Sale (POS)';
                })->all();

                $orders->where('productorder.is_pos', true);
            }

            if (count($sources)) {
                $orders->whereIn('productorder.source', $sources);
            }
        }

        // payment type
        if (request('fp')) {
            $orders->where(function (Builder $q) {
                if (in_array('Visa', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['visa']);
                }
                if (in_array('MasterCard', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['mastercard']);
                }
                if (in_array('Discover', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['discover']);
                }
                if (in_array('Amex', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['amex', 'americanexpress', 'american express']);
                }
                if (in_array('ACH', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['ach', 'check', 'cheque', 'checking', 'business check', 'personal check', 'business cheque', 'personal cheque', 'business checking', 'personal checking']);
                }
                if (in_array('Secure Account', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['vault']);
                }
                if (in_array('Check', request('fp'))) {
                    $q->orWhere('productorder.payment_type', '=', 'check');
                }
                if (in_array('Cash', request('fp'))) {
                    $q->orWhere('productorder.payment_type', '=', 'cash');
                }
                if (in_array('PayPal', request('fp'))) {
                    $q->orWhereIn('productorder.billingcardtype', ['paypal']);
                }
                if (in_array('Other', request('fp'))) {
                    $q->orWhere('productorder.payment_type', '=', 'other');
                }
                if (in_array('Google Pay', request('fp'))) {
                    $q->orWhereHas('successfulPayments', function (Builder $query) {
                        $query->where('card_wallet', 'google_pay');
                    });
                }
                if (in_array('Apple Pay', request('fp'))) {
                    $q->orWhereHas('successfulPayments', function (Builder $query) {
                        $query->where('card_wallet', 'apple_pay');
                    });
                }
            });
        }

        // unsynced
        if (request('fU') == '1') {
            $orders->unsynced();
        }

        // unsynced
        if (request('fc')) {
            $orders->where('productorder.billingcountry', request('fc'));
        }

        // dates
        $fd1 = fromLocal(request('fd1'));
        $fd2 = fromLocal(request('fd2'));
        if ($fd1 && $fd2) {
            $orders->whereBetween('productorder.ordered_at', [
                toUtc($fd1->startOfDay()),
                toUtc($fd2->endOfDay()),
            ]);
        } elseif ($fd1) {
            $orders->where('productorder.ordered_at', '>=', toUtc($fd1->startOfDay()));
        } elseif ($fd2) {
            $orders->where('productorder.ordered_at', '<=', toUtc($fd2->endOfDay()));
        }

        // item filters
        if (request('fit')) {
            $orders->where(function ($q) {
                if (in_array('s', request('fit'))) {
                    $q->orWhere('shippable_items', '>', 0);
                }
                if (in_array('ns', request('fit'))) {
                    $q->orWhere('shippable_items', '=', 0);
                }
                if (in_array('d', request('fit'))) {
                    $q->orWhere('download_items', '>', 0);
                }
                if (in_array('nd', request('fit'))) {
                    $q->orWhere('download_items', '=', 0);
                }
                if (in_array('r', request('fit'))) {
                    $q->orWhere('recurring_items', '>', 0);
                }
                if (in_array('nr', request('fit'))) {
                    $q->orWhere('recurring_items', '=', 0);
                }
                if (in_array('sp', request('fit'))) {
                    $q->orWhereHas('items', function ($q) {
                        $q->whereNotNull('sponsorship_id');
                    });
                }
                if (in_array('nsp', request('fit'))) {
                    $q->orWhereHas('items', function ($q) {
                        $q->whereNull('sponsorship_id');
                    });
                }
            });
        }

        if (request('fv') == 'pass') {
            $orders->where(function ($q) {
                $q->whereHas('payments', function ($q) {
                    $q->where('paid', '=', 1);
                    $q->where(function ($q) {
                        $q->where('card_cvc_check', '=', 'pass');
                        $q->where('card_address_line1_check', '=', 'pass');
                        $q->where('card_address_zip_check', '=', 'pass');
                    });
                });
            });
        } elseif (request('fv') == 'fail') {
            $orders->where(function ($q) {
                $q->whereHas('payments', function ($q) {
                    $q->where('paid', '=', 1);
                    $q->where(function ($q) {
                        $q->where('card_cvc_check', '=', 'fail');
                        $q->orWhere('card_address_line1_check', '=', 'fail');
                        $q->orWhere('card_address_zip_check', '=', 'fail');
                    });
                });
            });
        } elseif (request('fv') == 'unavailable') {
            $orders->where(function ($q) {
                $q->whereHas('payments', function ($q) {
                    $q->where('paid', '=', 1);
                    $q->where(function ($q) {
                        $q->whereNotIn('card_cvc_check', ['pass', 'fail']);
                        $q->whereNotIn('card_address_line1_check', ['pass', 'fail']);
                        $q->whereNotIn('card_address_zip_check', ['pass', 'fail']);
                    });
                });
            });
        } elseif (request('fv') == 'bad_address') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->where('card_address_line1_check', '=', 'fail');
                });
            });
        } elseif (request('fv') == 'bad_zip') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->where('card_address_zip_check', '=', 'fail');
                });
            });
        } elseif (request('fv') == 'bad_cvc') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->where('card_cvc_check', '=', 'fail');
                });
            });
        } elseif (request('fv') == 'no_address') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->whereNotIn('card_address_line1_check', ['pass', 'fail']);
                });
            });
        } elseif (request('fv') == 'no_zip') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->whereNotIn('card_address_zip_check', ['pass', 'fail']);
                });
            });
        } elseif (request('fv') == 'no_cvc') {
            $orders->whereHas('payments', function ($q) {
                $q->where('paid', '=', 1);
                $q->where(function ($q) {
                    $q->whereNotIn('card_cvc_check', ['pass', 'fail']);
                });
            });
        } elseif (request('fv') == 'ip_mismatch') {
            $orders->where(function ($q) {
                $q->where('productorder.is_pos', false);
                $q->whereNotIn('productorder.source', ['Import', 'Kiosk']);
                $q->whereNotIn('productorder.payment_type', ['paypal', 'wallet_pay']);
                $q->whereNotNull('productorder.ip_country');
                $q->whereRaw('productorder.ip_country != productorder.billingcountry');
            });
        } elseif (request('fv') == 'no_ip') {
            $orders->whereNull('productorder.ip_country');
        }

        if (request('fg')) {
            $orders->whereHas('payments', function ($q) {
                $q->where('gateway_type', '=', request('fg'));
            });
        }

        if (request('promo')) {
            $orders->whereHas('promoCodes', function ($q) {
                $q->where('promocode', request('promo'));
            });
        }

        if (request('fundraising_page_id')) {
            $orders->whereHas('items', function ($q) {
                $q->where('fundraising_page_id', request('fundraising_page_id'));
            });
        }

        if (request('membership_id')) {
            $orders->whereHas('member.groups', function ($q) {
                $q->whereIn('group_id', request('membership_id'));
            });
        }

        // tracking source
        if (request('fots')) {
            $orders->where('productorder.tracking_source', 'like', '%' . request('fots') . '%');
        }

        // tracking medium
        if (request('fotm')) {
            $orders->where('productorder.tracking_medium', 'like', '%' . request('fotm') . '%');
        }

        // tracking content
        if (request('fotc')) {
            $orders->where('productorder.tracking_campaign', 'like', '%' . request('fotc') . '%');
        }

        // tracking content
        if (request('fott')) {
            $orders->where('productorder.tracking_content', 'like', '%' . request('fott') . '%');
        }

        return $orders;
    }

    /**
     * Batch process multiple orders.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batch()
    {
        // check permission
        user()->canOrRedirect('order.fullfill');

        // if no is passed in, bail
        if (! request()->filled('ids')) {
            $this->flash->error('Failed to batch process. There were no items to process.');

            return redirect()->back();
        }

        // parse ids
        $ids = explode(',', request('ids'));

        // mark all as INCOMPLETE
        if (request('action') == 'incomplete') {
            Order::withSpam()->whereIn('id', $ids)->update(['iscomplete' => 0]);

        // mark all as COMPLETE
        } elseif (request('action') == 'complete') {
            Order::withSpam()->whereIn('id', $ids)->update(['iscomplete' => 1]);

        // mark all as spam
        } elseif (request('action') === 'spam' || request('action') === 'spam_and_refund') {
            Order::whereIn('id', $ids)->update([
                'is_spam' => 1,
                'marked_as_spam_at' => now(),
                'marked_as_spam_by' => user('id'),
            ]);

            Payment::query()
                ->join('payments_pivot as pp', 'pp.payment_id', 'payments.id')
                ->join('productorder as o', 'o.id', 'pp.order_id')
                ->where('payments.spam', 0)
                ->where('o.is_spam', 1)
                ->update(['payments.spam' => 1]);

            $supporterIds = Order::query()
                ->whereIn('id', $ids)
                ->whereNotNull('member_id')
                ->withSpam()
                ->pluck('member_id');

            Member::whereIn('id', $supporterIds)->update(['is_active' => 0, 'is_spam' => 1]);

            $rppIds = OrderItem::query()
                ->join('recurring_payment_profiles as r', 'r.productorderitem_id', 'productorderitem.id')
                ->whereIn('productorderitem.productorderid', $ids)
                ->where('r.status', RecurringPaymentProfileStatus::ACTIVE)
                ->pluck('r.id');

            RecurringPaymentProfile::whereIn('id', $rppIds)->update([
                'status' => RecurringPaymentProfileStatus::CANCELLED,
                'final_payment_due_date' => now(),
                'cancel_reason' => 'Spam/Fraud',
            ]);
        }

        // attempt refunds
        if (request('action') === 'spam_and_refund') {
            $orders = Order::query()
                ->whereIn('id', $ids)
                ->whereNull('refunded_at')
                ->withSpam()
                ->cursor();

            foreach ($orders as $order) {
                rescueQuietly(fn () => $order->refund());
            }
        }

        // redirect back w/ success
        $this->flash->success('Successfully processed ' . count($ids) . ' items.');

        return redirect()->back();
    }

    public function update()
    {
        // get user model permission
        $orderModel = Order::withSpam()->findWithPermission(request('id'), ['edit', 'fullfill']);

        // if they can't edit (meaning they can only fullfill)
        if (! $orderModel->userCan('edit')) {
            // update completed status
            $orderModel->iscomplete = request('iscomplete') ? 1 : 0;
            $orderModel->customer_notes = request('customer_notes');
            $orderModel->save();

        // otherwise, update all order data
        } else {
            $orderModel->customer_notes = request('customer_notes');
            $orderModel->comments = request('comments');
            $orderModel->is_anonymous = request('is_anonymous') ? 1 : 0;
            $orderModel->iscomplete = request('iscomplete') ? 1 : 0;
            $orderModel->account_type_id = request('account_type_id');
            $orderModel->billing_title = request('billing_title');
            $orderModel->billing_first_name = request('billing_first_name');
            $orderModel->billing_last_name = request('billing_last_name');
            $orderModel->billing_organization_name = request('billing_organization_name');
            $orderModel->billingemail = request('billingemail');
            $orderModel->billingaddress1 = request('billingaddress1');
            $orderModel->billingaddress2 = request('billingaddress2');
            $orderModel->billingcity = request('billingcity');
            $orderModel->billingstate = request('billingstate');
            $orderModel->billingzip = request('billingzip');
            $orderModel->billingcountry = request('billingcountry');
            $orderModel->billingphone = request('billingphone');
            $orderModel->shipping_title = request('shipping_title');
            $orderModel->shipping_first_name = request('shipping_first_name');
            $orderModel->shipping_last_name = request('shipping_last_name');
            $orderModel->shipping_organization_name = request('shipping_organization_name');
            $orderModel->shipemail = request('shipemail');
            $orderModel->shipaddress1 = request('shipaddress1');
            $orderModel->shipaddress2 = request('shipaddress2');
            $orderModel->shipcity = request('shipcity');
            $orderModel->shipstate = request('shipstate');
            $orderModel->shipzip = request('shipzip');
            $orderModel->shipcountry = request('shipcountry');
            $orderModel->shipphone = request('shipphone');
            $orderModel->save();
        }

        $this->flash->success('Contribution updated successfully.');

        // redirect
        return redirect()->back();
    }

    public function view($id = null)
    {
        user()->canOrRedirect('order');

        $orderId = $id ?? request('i');

        if (request('gift') && empty($orderId)) {
            $orderId = Order::withSpam()->where('alt_transaction_id', 'like', '%' . request('gift') . '%')->value('id');
        }

        if (request('c') && empty($orderId)) {
            $orderId = Order::withSpam()->where('client_uuid', request('c'))->value('id');
        }

        if (! $orderId) {
            $this->flash->error('You cannot create a new Contribution.');

            return redirect()->back();
        }

        $order = Order::query()
            ->with([
                'items.variant.product',
                'items.fields',
                'items.groupAccount',
                'member.groups',
                'member.accountType',
                'payments' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
            ])->withSpam()
            ->withTrashed()
            ->where('id', $orderId)
            ->firstOrFail();

        $title = 'Contribution #' . $order->invoicenumber;

        if (! $order->confirmationdatetime) {
            $title = 'Abandoned Cart';
        }

        if ($order->is_test) {
            $title = 'Test Contribution #' . $order->invoicenumber;
        }

        $warnings = [];

        if ($order->has_cvc_failure) {
            $warnings[] = [
                'title' => 'The CVC code does not match what the bank has on file.',
                'tooltip' => '<strong>CVC Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the CVC entered matches what\'s actually on the card (CVC Check).',
            ];
        }

        if ($order->has_avs_address_failure) {
            $warnings[] = [
                'title' => 'The Billing Address does not match what the bank has on file.',
                'tooltip' => '<strong>AVS Address Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the address on the card matches the address on file with the bank (AVS Check). It\'s not uncommon for donors to misspell their address or mistype their CVC. However, it\'s important to pay special attention when the verification fails as it may be an indicator of fraud.',
            ];
        }
        if ($order->has_avs_zip_failure) {
            $warnings[] = [
                'title' => 'The Billing ZIP code does not match what the bank has on file.',
                'tooltip' => '<strong>AVS Address Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the address on the card matches the address on file with the bank (AVS Check). It\'s not uncommon for donors to misspell their address or mistype their CVC. However, it\'s important to pay special attention when the verification fails as it may be an indicator of fraud.',
            ];
        }
        if ($order->has_ip_geography_mismatch) {
            $warnings[] = [
                'title' => 'The IP Address does not match the Billing Address.',
                'tooltip' => '<strong>IP Geography Mismatch</strong><br>The country  of the device that this contribution originated from does not match the billing address country. This match is based on IP address and has a very small margin of error (~5%).',
            ];
        }

        $hasTributes = $order->items()->whereHas('tribute')->exists();

        $salesforceReference = $order->references()->salesforce()->order()->latest()->first();

        $countries = cart_countries();
        asort($countries);

        $billingSubdivisions = app(LocaleController::class)->getSubdivisions($order->billingcountry)->getData(true);
        $shippingSubdivisions = app(LocaleController::class)->getSubdivisions($order->shipcountry)->getData(true);

        return view('orders.view', compact(
            'order',
            'title',
            'warnings',
            'countries',
            'billingSubdivisions',
            'shippingSubdivisions',
            'hasTributes',
            'salesforceReference',
        ));
    }

    public function destroyOrder(Order $order)
    {
        $order->delete();

        if ($order->taxReceipt) {
            $order->taxReceipt->delete();
        }

        foreach ($order->items as $item) {
            if ($item->tribute) {
                $item->tribute->delete();
            }

            if ($item->recurringPaymentProfile) {
                $item->recurringPaymentProfile->delete();
            }

            GroupAccount::query()->where('order_item_id', $item->id)->delete();

            if ($item->fundraisingPage) {
                $item->fundraisingPage->updateAggregates();
            }
        }

        if ($order->member) {
            CalculateLifetimeMemberGiving::dispatch($order->member);
        }
    }

    public function destroy($order_id)
    {
        try {
            $order = Order::withSpam()->findWithPermission($order_id, 'edit');

            $this->destroyOrder($order);

            $this->flash->success("Contribution #{$order->invoicenumber} deleted.");

            return redirect()->route('backend.orders.index');
        } catch (\Exception $e) {
            $this->flash->error('Error deleting contribution. ' . $e->getMessage());

            return redirect()->route('backend.orders.edit', $order_id);
        }
    }

    public function restore($order_id)
    {
        // THIS NEEDS TO BE IMPROVED TO RESTORE EVERYTHING RELATED TO THE ORDER
        try {
            $order = Order::withSpam()->withTrashed()->where('id', $order_id)->first();
            $order->restore();
            $this->flash->success("Contribution #{$order->invoicenumber} restored.");
        } catch (\Exception $e) {
            $this->flash->error('Error restoring contribution. ' . $e->getMessage());
        }

        return redirect()->route('backend.orders.edit', $order_id);
    }

    public function notify_site_owner()
    {
        // show processing
        echo 'Processing... Please be patient...';

        // get cart
        $cart = Order::withSpam()->findOrFail(request('i'));

        // send site owner email
        cart_send_site_owner_email($cart->client_uuid);

        // back to order
        $this->flash->success('Successfully renotified staff.');

        return redirect()->back();
    }

    public function push_to_dpo()
    {
        // check permission
        user()->canOrRedirect('admin.dpo');

        // get cart
        $order = Order::withSpam()->findOrFail(request('i'));

        // update ability to sync to dp
        $order->dp_sync_order = 1;
        $order->save();

        try {
            // push the order to dp
            $donor_id = request()->input('donor_id') ?? null;
            app('Ds\Services\DonorPerfectService')->pushOrder($order, $donor_id); // NEW dp sync

            // refresh order
            $order = Order::withSpam()->find(request('i'));

            // if it didn't work, say so
            if ($order->is_unsynced) {
                $this->flash->error('DonorPerfect sync failed. ' . (($order->dpo_status_message) ?: ''));

            // otherwise, success message
            } else {
                $this->flash->success('Successfully re-synced contribution with DonorPerfect.');
            }
        } catch (\Exception $e) {
            $this->flash->error('DonorPerfect sync failed. ' . $e->getMessage());
        }

        // return to order details screen
        return redirect()->back();
    }

    public function reprocess_downloads($id)
    {
        $cart = Order::withSpam()->findOrFail($id);

        if (! cart_send_downloads($cart->client_uuid)) {
            $this->flash->error('An error occurred while reprocessing downloads.');
        }

        return redirect()->route('backend.orders.edit', $cart);
    }

    public function reprocess_product_specific_emails()
    {
        // get order model
        $order = Order::with('taxReceipts')
            ->where('client_uuid', request('o'))
            ->where('invoicenumber', request('o'))
            ->withSpam()
            ->firstOrFail();

        // catch invalid email
        if (! (trim($order->billingemail) !== '' && \Swift_Validate::email(trim($order->billingemail)))) {
            $this->flash->error('Could not renotify customer. There is no billing email address to send a notification to.');

            return redirect()->route('backend.orders.edit', $order);
        }

        // LEGACY
        if (app(OrderEmailPreferencesService::class)->shouldSendLegacyEmail($order)) {
            cart_send_customer_email($order->client_uuid);
        }

        $order->notify();

        // notify tax receipt
        if (feature('tax_receipt')) {
            if ($order->taxReceipt) {
                $order->taxReceipt->notify();
            }
        }

        $this->flash->success('Successfully renotified customer.');

        // hop back to order detail screen
        return redirect()->back();
    }

    public function abandoned_carts()
    {
        // check permission
        user()->canOrRedirect('reports.abandoned_carts');

        $__menu = 'reports.abandoned';

        $title = 'Abandoned Carts';

        pageSetup($title, 'jpanel');

        return $this->getView('orders/abandoned_carts', compact('__menu', 'title'));
    }

    public function abandoned_carts_ajax()
    {
        // check permission
        user()->canOrRedirect('reports.abandoned_carts');

        // base orders list query
        $orders = $this->_abandonedCartsBaseQueryWithFilters();

        // generate data table
        $dataTable = new DataTable($orders, [
            'id',
            ['billing_first_name', 'billing_last_name'],
            'billingemail',
            'total_qty',
            'totalamount',
            'response_text',
            'client_browser',
            'client_ip',
            'started_at',
            'currency_code',
            'ip_country',
        ]);

        $dataTable->setFormatRowFunction(function ($order) {
            return [
                dangerouslyUseHTML('<a href="' . e(route('backend.orders.edit', $order)) . '"><i class="fa fa-search"></i></a>'),
                e($order->billingFirstNameBillingLastName),
                dangerouslyUseHTML('<a href="mailto:' . e($order->billingemail) . '">' . e($order->billingemail) . '</a>'),
                e($order->total_qty),
                dangerouslyUseHTML('<div class="stat-val">' . e(number_format($order->totalamount, 2)) . '&nbsp;<span class="text-muted">' . e($order->currency_code) . '</span></div>'),
                dangerouslyUseHTML(($order->response_text) ? e($order->response_text) . ' &nbsp;&nbsp;<a href="https://help.givecloud.com/en/articles/1541616-failed-or-declined-payments" target="_blank" rel="noreferrer"><i class="fa fa-question-circle"></i> Why?</a>' : ''),
                dangerouslyUseHTML(($order->client_browser) ? e(ua_browser($order->client_browser)) . ' <small class="text-muted">' . e(ua_os($order->client_browser)) . '</small>' : ''),
                dangerouslyUseHTML(($order->ip_country ? '<img src="' . e(flag($order->ip_country)) . '" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"> ' : '') . e($order->client_ip)),
                dangerouslyUseHTML(e(toLocal($order->started_at)) . ' <small class="text-muted">' . e(toLocalFormat($order->started_at, 'g:iA')) . '</small>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    public function abandoned_carts_csv()
    {
        // increase timelimit (2 minutes)
        set_time_limit(2 * 60);

        // check permission
        user()->canOrRedirect('reports.abandoned_carts');

        // generate data table
        $orders = $this->_abandonedCartsBaseQueryWithFilters();

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('abandoned_carts.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Started At', 'Billing First Name', 'Billing Last Name', 'Billing Email', 'Billing Phone', 'Billing Address', 'Billing Address 2', 'Billing City', 'Billing Province/State', 'Billing Postal/ZIP', 'Billing Country', 'Items', 'Contribution Subtotal', 'Shipping', 'Tax', 'Contribution Total', 'Payment Type', 'Payment Response', 'IP', 'Browser'], ',', '"');

        // chunk over 1000 records at a time
        $orders->orderBy('id')->chunk(1000, function ($orders) use ($outstream) {
            foreach ($orders as $order) {
                fputcsv($outstream, [
                    toLocalFormat($order->started_at, 'csv'),
                    $order->billing_first_name,
                    $order->billing_last_name,
                    $order->billingemail,
                    $order->billingphone,
                    $order->billingaddress1,
                    $order->billingaddress2,
                    $order->billingcity,
                    $order->billingstate,
                    $order->billingzip,
                    $order->billingcountry,
                    number_format($order->total_qty),
                    number_format($order->subtotal, 2),
                    number_format($order->shipping_amount, 2),
                    number_format($order->taxtotal, 2),
                    number_format($order->totalamount, 2),
                    $order->payment_type_formatted,
                    $order->response_text,
                    $order->client_ip,
                    ($order->client_browser) ? ua_formatted($order->client_browser) : '',
                ], ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    private function _abandonedCartsBaseQueryWithFilters()
    {
        // base orders list query
        $orders = Order::abandoned();

        // /////////////////////
        // // FILTERS
        // /////////////////////

        // status
        if (request('fs') != '') {
            // pre-checkout (no checkout screen data entered)
            if (request('fs') == 1) {
                $orders->preCheckout();

            // checkout data
            } elseif (request('fs') == 2) {
                $orders->checkout();

            // failed payment
            } elseif (request('fs') == 3) {
                $orders->failedPayments();
            }
        }

        if (request('fs') === '4') {
            $orders->onlySpam();
        }

        // search
        if (Str::startsWith(request('fO'), 'ip:')) {
            $ips = trim(substr(request('fO'), 3), '()');
            $ips = array_map('trim', explode(' or ', strtolower($ips)));
            $orders->whereIn('productorder.client_ip', $ips);
        } elseif (request('fO')) {
            $orders->where(function ($query) {
                $keyword = '%' . db_escape_like(request('fO')) . '%';
                $query->where(DB::raw("CONCAT(billing_first_name, ' ', billing_last_name)"), 'LIKE', $keyword);
                $query->orWhere('billingemail', 'LIKE', $keyword);
                $query->orWhere(DB::raw("CONCAT(shipping_first_name, ' ', shipping_last_name)"), 'LIKE', $keyword);
                $query->orWhere('shipemail', 'LIKE', $keyword);
                $query->orWhere('response_text', 'LIKE', $keyword);
                $query->orWhere('client_ip', 'LIKE', $keyword);
            });
        }

        // dates
        $fd1 = fromLocal(request('fd1'));
        $fd2 = fromLocal(request('fd2'));
        if ($fd1 && $fd2) {
            $orders->whereBetween('started_at', [
                toUtc($fd1->startOfDay()),
                toUtc($fd2->endOfDay()),
            ]);
        } elseif ($fd1) {
            $orders->where('started_at', '>=', toUtc($fd1->startOfDay()));
        } elseif ($fd2) {
            $orders->where('started_at', '<=', toUtc($fd2->endOfDay()));
        }

        // where there is atleast something in the cart
        $orders->where('total_qty', '>', 0);

        // return query
        return $orders;
    }

    public function export_custom_fields_csv()
    {
        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename="' . date('Y-m-d') . '_OrdersByCustomField.csv"');

        function x($str)
        {
            return str_replace('"', '""', stripslashes($str));
        }

        $qO = db_query(sprintf(
            'SELECT o.invoicenumber, o.alt_contact_id, o.alt_transaction_id, f.name, fi.value
                FROM productorder o
                INNER JOIN productorderitem i ON i.productorderid = o.id
                INNER JOIN productinventory iv ON iv.id = i.productinventoryid
                INNER JOIN product p ON p.id = iv.productid
                INNER JOIN productorderitemfield fi ON fi.orderitemid = i.id
                INNER JOIN productfields f ON f.id = fi.fieldid
                WHERE o.id IN (%s)
                    AND o.deleted_at is null
                ORDER BY o.createddatetime, f.sequence',
            db_real_escape_string(request('ids'))
        ));

        echo '"Contribution No.","DPO Donor#","DPO Gift#","Field","Value"' . chr(10);
        while ($o = db_fetch_assoc($qO)) {
            echo $o['invoicenumber'] . ',"' . x($o['alt_contact_id']) . '","' . x($o['alt_transaction_id']) . '","' . x($o['name']) . '","' . x($o['value']) . '"' . chr(10);
        }
        exit;
    }

    public function packing_slip()
    {
        user()->canOrRedirect('order.fullfill');

        pageSetup('Packing Slips', '');

        $ids = request('ids', request('id'));

        $orders = Order::with('items.variant.product', 'items.lockedItems')
            ->whereIn('id', explode_ids($ids))
            ->withSpam()
            ->cursor();

        $this->setViewLayout(false);

        return $this->getView('orders/packing_slip', compact('orders'));
    }

    public function checkin()
    {
        // check inputs
        if (request('o') === null || request('i') === null) {
            abort(500, 'Missing arguments.');
        }

        // vars
        $order_id = (int) request('o');
        $order_item_id = (int) request('i');

        // if this is a post request, CHECK-IN
        if (request()->isMethod('POST')) {
            // detect checkin param
            if (request('check_in') === null) {
                abort(500, 'Missing arguments.');
            }

            // insert check-in
            $checkIn = new CheckIn;
            $checkIn->order_id = $order_id;
            $checkIn->order_item_id = $order_item_id;
            $checkIn->check_in_at = now();
            $checkIn->check_in_by = user('id');
            $checkIn->save();

            // redirect to GET request so that refreshing this URL doesn't cause another checking
            return redirect()->route('backend.orders.checkin', [
                'o' => $order_id,
                'i' => $order_item_id,
            ]);
        }

        // list checkins
        $check_ins = DB::select('SELECT c.*,
                    u.firstname AS check_in_first_name,
                    u.lastname AS check_in_last_name
                FROM ticket_check_in c
                INNER JOIN `user` u ON u.id = c.check_in_by
                WHERE order_id = ?
                    AND order_item_id = ?', [
            $order_id,
            $order_item_id,
        ]);

        // product id
        $order = DB::select('SELECT i.productinventoryid AS inventory_id,
                    o.billing_first_name,
                    o.billing_last_name,
                    o.shipping_first_name,
                    o.shipping_last_name,
                    o.invoicenumber AS order_number,
                    i.qty AS inventory_quantity
                FROM productorderitem i
                INNER JOIN productorder o ON o.id = i.productorderid and o.deleted_at is null
                WHERE i.id = ?', [
            $order_item_id,
        ]);

        $order = $order[0];

        // list product
        $inventory = Variant::find($order->inventory_id);

        $fields = DB::select(
            'SELECT f.name, v.value
                FROM productorderitem i
                INNER JOIN productorderitemfield v ON v.orderitemid = i.id
                INNER JOIN productfields f ON f.id = v.fieldid
                INNER JOIN productorder o ON o.id = i.productorderid and o.deleted_at is null
                WHERE i.id = ?',
            [$order_item_id]
        );

        // disable parent template so the output below isn't wrapped in the GC layout
        $this->setViewLayout(false);

        // show page
        return $this->getView('orders/check_in', compact('order_id', 'order_item_id', 'check_ins', 'order', 'inventory', 'fields'));
    }

    public function set_vault()
    {
        // required
        $cart_id = (int) request('i');
        $vault_id = (int) request('v');

        // optional
        $cc_number = request('a');
        $cc_exp = request('b');
        $check_account = request('c');
        $check_name = request('e');

        // credit card vault
        if (trim($cc_number) != '') {
            $expiry_date = strtotime(substr($cc_exp, 0, 2) . '/01/' . substr($cc_exp, 2, 2));

            // set the vault id
            $order = Order::withSpam()->findOrFail($cart_id);
            $order->vault_id = $vault_id;
            $order->billing_card_expiry_month = date('m', $expiry_date);
            $order->billing_card_expiry_year = date('Y', $expiry_date);
            $order->billingcardtype = card_type_from_first_number($cc_number);
            $order->billingcardlastfour = substr($cc_number, -4);
            $order->save();

        // bank account vault
        } else {
            // set the vault id
            $order = Order::withSpam()->findOrFail($cart_id);
            $order->vault_id = $vault_id;
            $order->billingcardlastfour = substr($check_account, -4);
            $order->billing_name_on_account = $check_name;
            $order->billingcardtype = 'Personal Check';
            $order->save();
        }

        // redirect to order
        return redirect()->route('backend.orders.edit', $cart_id);
    }

    /**
     * Link an order to a member
     * POST - expects member_id
     *
     * @param int $order_id
     */
    public function linkMember($order_id)
    {
        user()->canOrRedirect('order.edit');

        // bail if no member_id
        $member = \Ds\Models\Member::find((int) request('member_id'));

        if (! $member) {
            $this->flash->error("Error updating contribution. We couldn't find the supporter.");

            return redirect()->route('backend.orders.edit', $order_id);
        }

        // link order to the member passed in
        $order = Order::withSpam()->with('items.recurringPaymentProfile.paymentMethod')->find($order_id);

        if (! $order) {
            $this->flash->error("Error updating contribution. We couldn't find the contribution.");

            return redirect()->route('backend.orders.edit', $order_id);
        }

        $order->linkToMember($member->id);

        // redirect
        $this->flash->success('Successfully linked supporter to contribution.');

        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Link an order to a member
     * GET - expects no vars
     *
     * @param int $order_id
     */
    public function unlinkMember($order_id)
    {
        user()->canOrRedirect('order.edit');

        // unlink order from any member
        $order = Order::withSpam()->findOrFail($order_id);

        if (! $order) {
            $this->flash->error("Error updating contribution. We couldn't find the contribution.");

            return redirect()->route('backend.orders.edit', $order_id);
        }

        // check whether there is a recurring payment associated with the order
        if ($order->hasRecurringItemsInDs()) {
            $rpp = \Ds\Models\RecurringPaymentProfile::where('productorder_id', '=', $order->id)->first();
            $this->flash->error('Cannot unlink contribution. This contribution has recurring payment <a href="/jpanel/recurring_payments/' . $rpp->profile_id . '">' . $rpp->profile_id . '</a> associated with it.');

            return redirect()->route('backend.orders.edit', $order_id);
        }

        $order->member_id = null;
        $order->save();

        // redirect
        $this->flash->success('Successfully unlinked supporter from contribution.');

        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Create a member from the order.
     *
     * @param int $order_id
     */
    public function createMember($order_id)
    {
        user()->canOrRedirect('order.edit');

        // unlink order from any member
        $order = Order::withSpam()->findOrFail($order_id);

        if (! $order) {
            $this->flash->error("Error creating supporter. We couldn't find the contribution.");

            return redirect()->route('backend.orders.edit', $order_id);
        }

        // force creating a supporter
        $member = $order->createMember(null, true);

        // make sure that all the data is linked to the new member
        $order->linkToMember($member->id);

        $this->flash->success('Successfully created supporter.');

        if (request()->has('redirect')) {
            return redirect()->route('backend.member.edit', $order->member_id);
        }

        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Try generateing a tax receipt for the specific order.
     * GET /contributions/{order_id}/generate_tax_receipt
     *
     * @param int $order_id
     */
    public function generateTaxReceipt($order_id)
    {
        user()->canOrRedirect('order.edit');

        // try creating a tax receipt
        try {
            $receipt = \Ds\Models\TaxReceipt::createFromOrder($order_id);
            $this->flash->success('Successfully generated tax receipt ' . $receipt->number . '.');
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }

        // redirect
        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Refund an order.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund($order_id)
    {
        user()->canOrRedirect('order.refund');

        $order = Order::withSpam()->find($order_id);

        $amount = (float) ($order->totalamount - $order->refunded_amt);

        if (request('refund_type') == 'custom') {
            $amount = numeral(request('amount'))->toFloat();
        }

        if ($amount <= 0) {
            $this->flash->error('The refund amount must be greater than $0.');

            return redirect()->route('backend.orders.edit', $order_id);
        }

        if ($amount > $order->totalamount) {
            $this->flash->error('The refund amount cannot exceed the original contribution value of $' . number_format($order->totalamount, 2) . '.');

            return redirect()->route('backend.orders.edit', $order_id);
        }

        // try refunding an order
        try {
            $order->refund($amount);
            $this->flash->success('Contribution has been successfully refunded.');
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());

            return redirect()->route('backend.orders.edit', $order_id);
        }

        if (request('refund_type') == 'full_delete') {
            $this->destroyOrder($order);
            $this->flash->success('Contribution has been successfully deleted.');

            return redirect()->route('backend.orders.index', ['c' => 0]);
        }

        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Mark an order as complete.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete($order_id)
    {
        user()->canOrRedirect('order.fullfill');

        $order = Order::withSpam()->find($order_id);
        $order->iscomplete = 1;
        $order->save();

        $this->flash->success('Contribution marked as fulfilled.');

        return redirect()->back();
    }

    /**
     * Mark an order as incomplete.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function incomplete($order_id)
    {
        user()->canOrRedirect('order.fullfill');

        $order = Order::withSpam()->find($order_id);
        $order->iscomplete = 0;
        $order->save();

        $this->flash->success('Contribution marked as unfulfilled.');

        return redirect()->back();
    }

    public function refreshLatestPaymentStatus(string $orderId)
    {
        user()->canOrRedirect('order.edit');

        $order = Order::withSpam()->findOrFail($orderId);

        try {
            optional($order->latestPayment)->syncPaymentStatus();

            $this->flash->success('Contribution payment status refreshed.');
        } catch (Throwable $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect()->back();
    }

    public function markAsSpam(string $orderId)
    {
        user()->canOrRedirect('order.refund');

        $order = Order::withSpam()->findOrFail($orderId);
        $order->is_spam = true;
        $order->marked_as_spam_at = now();
        $order->marked_as_spam_by = user()->id;
        $order->save();

        if ($order->member) {
            $order->member->is_spam = true;
            $order->member->save();
        }

        foreach ($order->payments as $payment) {
            $payment->spam = true;
            $payment->save();
        }

        rescueQuietly(fn () => $order->refund());

        $this->flash->success('Contribution marked as spam.');

        return redirect()->back();
    }

    /**
     * Update the gift and donor ID on an order.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editDPData($order_id)
    {
        user()->canOrRedirect(['order.edit', 'admin.dpo']);

        // try refunding an order
        try {
            $order = Order::withSpam()->find($order_id);
            $order->dp_sync_order = (request()->input('dp_sync_order') == 1);
            $order->alt_contact_id = request('donor_id');
            $order->alt_transaction_id = request('gift_ids');
            $order->alt_data_updated_at = now();
            $order->alt_data_updated_by = user('id');
            $order->save();

            $this->flash->success('DonorPerfect data has been successfully saved.');
        } catch (\Exception $e) {
            $this->flash->error('There was an error changing the DonorPerfect data on this contribution. ' . $e->getMessage());
        }

        // redirect
        return redirect()->route('backend.orders.edit', $order_id);
    }

    /**
     * Find an order by invoice number and redirect to it.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function orderNumber($invoice_number)
    {
        user()->canOrRedirect(['order.edit']);

        $order = Order::query()
            ->where('client_uuid', $invoice_number)
            ->where('invoicenumber', $invoice_number)
            ->withSpam()
            ->firstOrFail();

        // found the order
        if ($order) {
            return redirect()->route('backend.orders.edit', $order);
        }

        // didn't find the order
        $this->flash->error("Unable to find contribution number '" . $invoice_number . "'.");

        return redirect()->route('backend.orders.index');
    }

    public function editItem($order_id)
    {
        user()->canOrRedirect(['order.edit']);

        $item = Order::withSpam()->find($order_id)
            ->items()
            ->where('id', request('item_id'))
            ->first();

        if ($item && request()->filled('new_variant_id')) {
            $item->changeVariant(request('new_variant_id'));
            $this->flash->success('Product changed successfully.');
        } else {
            $this->flash->error('Unable to change the product.');
        }

        return redirect()->route('backend.orders.edit', $order_id);
    }

    public function getItemFields($order_id)
    {
        user()->canOrRedirect(['order.edit']);

        $item = Order::withSpam()->find($order_id)
            ->items()
            ->with('fields')
            ->where('id', request('item_id'))
            ->first();

        return view('orders.getItemFields', compact('order_id', 'item'));
    }

    public function editItemFields($order_id)
    {
        user()->canOrRedirect(['order.edit']);

        foreach (request('itemField') as $itemField) {
            DB::table('productorderitemfield')
                ->where('id', $itemField['id'])
                ->update(['value' => $itemField['value']]);
        }

        $this->flash->success('Custom fields updated successfully.');

        return redirect()->route('backend.orders.edit', $order_id);
    }

    public function editGiftAidEligibility($order_id)
    {
        user()->canOrRedirect(['order.edit']);

        DB::table('productorderitem')
            ->where('id', request('item_id'))
            ->update(['gift_aid' => request('gift_aid_eligible')]);

        $this->flash->success('Gift Aid Eligibility Updated.');

        return redirect()->route('backend.orders.edit', $order_id);
    }

    public function item_applyGroup($order_item_id)
    {
        $order_item = \Ds\Models\OrderItem::find($order_item_id);

        if (! $order_item) {
            $this->flash->error('Invalid contribution item.');

            return redirect()->back();
        }

        try {
            $order_item->applyGroup();
            $this->flash->success('Group/Membership applied successfully.');
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect()->back();
    }
}
