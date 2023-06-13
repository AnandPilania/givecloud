<?php

namespace Ds\Jobs\Import;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Variant;
use Ds\Services\LedgerEntryService;
use Ds\Services\PaymentService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class ContributionsFromFile extends ImportJob
{
    /**
     * Version of the import process.
     */
    public function getColumnDefinitions(): Collection
    {
        /*
        [
            'id' => 'order_number',
            'name' => 'Contribution Number',
            'hint' => 'The unique number associated with this contribution.',
            'sanitize' => null,
            'validator' => 'required|max:48|alpha_num|exists:productorder,invoicenumber',
            'messages' => [
                'order_number.exists' => 'Contribution (:value) already exists.'
            ],
            'custom_validator' = function ($row){
                return true;
            }
        ],
        */
        return collect([
            (object) [
                'id' => 'order_number',
                'name' => 'Contribution Number',
                'hint' => 'The unique number associated with this contribution.',
                'validator' => 'required|max:48|alpha_num',
                'messages' => [
                    'order_number.unique' => 'Contribution (:value) already exists.',
                ],
            ],
            (object) [
                'id' => 'order_local_time',
                'name' => 'Contribution Date/Time',
                'hint' => 'The local time of the contribution formatted 2018-05-02 23:01:03 (yyyy-mm-dd hh:mm:ss).',
                'validator' => 'required|date',
            ],
            (object) [
                'id' => 'payment_local_time',
                'name' => 'Payment Date/Time',
                'hint' => 'The local time of the payment formatted 2018-05-02 23:01:03 (yyyy-mm-dd hh:mm:ss).',
                'validator' => 'nullable|date',
                'default' => function ($row) {
                    return Arr::get($row, 'order_local_time');
                },
            ],
            (object) [
                'id' => 'subtotal_amt',
                'name' => 'Subtotal Amount',
                'sanitize' => FILTER_VALIDATE_FLOAT,
                'hint' => 'The subtotal of the contribution.',
                'validator' => 'nullable|numeric',
                'default' => 0.00,
            ],
            (object) [
                'id' => 'shipping_amt',
                'name' => 'Shipping Amount',
                'sanitize' => FILTER_VALIDATE_FLOAT,
                'hint' => 'The amount of shipping charged on the contribution.',
                'validator' => 'nullable|numeric',
                'default' => 0.00,
            ],
            (object) [
                'id' => 'shipping_method',
                'name' => 'Shipping Method',
                'hint' => 'Any description of the shipping method (Ex: Standard Shipping).',
                'validator' => 'nullable|max:120',
            ],
            (object) [
                'id' => 'tax_amt',
                'name' => 'Tax Amount',
                'sanitize' => FILTER_VALIDATE_FLOAT,
                'hint' => 'Total amount of all taxes charged on the contribution.',
                'validator' => 'nullable|numeric',
                'default' => 0.00,
            ],
            (object) [
                'id' => 'total_amt',
                'name' => 'Total Amount',
                'sanitize' => FILTER_VALIDATE_FLOAT,
                'hint' => 'The total value of this contribution.',
                'validator' => 'nullable|numeric',
                'default' => 0.00,
            ],
            (object) [
                'id' => 'payment_type',
                'name' => 'Payment Type',
                'hint' => 'How was this contribution paid for?',
                'validator' => 'nullable|in:PayPal,Visa,MasterCard,American Express,Discover,Diners Club,GoCardless,Credit Card,Check,Cash,Other',
                'default' => 'Other',
            ],
            (object) [
                'id' => 'payment_auth',
                'name' => 'Payment Authorization',
                'hint' => 'The payment authorization code is paying with an electronic form of payment.',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'cc_last_four',
                'name' => 'Credit Card Last 4',
                'hint' => 'Last four digits of the credit card used.',
                'validator' => 'nullable|numeric|digits:4',
            ],
            (object) [
                'id' => 'cc_expiry',
                'name' => 'Credit Card Expiry',
                'hint' => 'Expiration date of the credit card (MMYY).',
                'validator' => 'nullable|date_format:my',
            ],
            (object) [
                'id' => 'user_ip',
                'name' => 'IP Address of Donor/Customer',
                'hint' => 'The IP address of the donor/customer.',
                'validator' => 'nullable|ip',
            ],
            (object) [
                'id' => 'user_agent',
                'name' => 'Browser User Agent',
                'hint' => 'The browser user agent of the donor/customer.',
            ],
            (object) [
                'id' => 'billing_first_name',
                'name' => 'Billing First Name',
                'validator' => 'required|max:64',
            ],
            (object) [
                'id' => 'billing_last_name',
                'name' => 'Billing Last Name',
                'validator' => 'required|max:64',
            ],
            (object) [
                'id' => 'billing_organization_name',
                'name' => 'Billing Organization Name',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'billing_address_1',
                'name' => 'Billing Address Line 1',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'billing_address_2',
                'name' => 'Billing Address Line 2',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'billing_city',
                'name' => 'Billing City',
                'validator' => 'nullable|max:40',
            ],
            (object) [
                'id' => 'billing_state',
                'name' => 'Billing State',
                'validator' => 'nullable|max:40',
            ],
            (object) [
                'id' => 'billing_zip',
                'name' => 'Billing Postal/ZIP',
                'validator' => 'nullable|max:20',
            ],
            (object) [
                'id' => 'billing_country',
                'name' => 'Billing Country',
                'hint' => 'Must be the 2-character ISO Country Code.',
                'validator' => 'nullable|alpha|max:2',
            ],
            (object) [
                'id' => 'billing_email',
                'name' => 'Billing Email',
                'validator' => 'nullable|email|max:60',
            ],
            (object) [
                'id' => 'billing_phone',
                'name' => 'Billing Phone',
                'validator' => 'nullable|max:36',
            ],
            (object) [
                'id' => 'shipping_first_name',
                'name' => 'Shipping First Name',
                'validator' => 'nullable|max:64',
            ],
            (object) [
                'id' => 'shipping_last_name',
                'name' => 'Shipping Last Name',
                'validator' => 'nullable|max:64',
            ],
            (object) [
                'id' => 'shipping_organization_name',
                'name' => 'Shipping Organization Name',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'shipping_address_1',
                'name' => 'Shipping Address Line 1',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'shipping_address_2',
                'name' => 'Shipping Address Line 2',
                'validator' => 'nullable|max:100',
            ],
            (object) [
                'id' => 'shipping_city',
                'name' => 'Shipping City',
                'validator' => 'nullable|max:40',
            ],
            (object) [
                'id' => 'shipping_state',
                'name' => 'Shipping State',
                'validator' => 'nullable|max:40',
            ],
            (object) [
                'id' => 'shipping_zip',
                'name' => 'Shipping Postal/ZIP',
                'validator' => 'nullable|max:20',
            ],
            (object) [
                'id' => 'shipping_country',
                'name' => 'Shipping Country',
                'hint' => 'Must be the 2-character ISO Country Code.',
                'validator' => 'nullable|alpha|max:2',
            ],
            (object) [
                'id' => 'shipping_email',
                'name' => 'Shipping Email',
                'validator' => 'nullable|email|max:60',
            ],
            (object) [
                'id' => 'shipping_phone',
                'name' => 'Shipping Phone',
                'validator' => 'nullable|max:36',
            ],
            (object) [
                'id' => 'item_product_code',
                'name' => 'Item Product Code',
                'validator' => 'required|exists:product,code|max:45',
                'messages' => [
                    'item_product_code.exists' => 'Product code (:value) is not valid.',
                ],
            ],
            (object) [
                'id' => 'item_variant_name',
                'name' => 'Item Variant Name',
                'validator' => 'nullable|exists:productinventory,variantname|max:250',
                'messages' => [
                    'item_variant_name.exists' => 'Product variant name (:value) is not valid.',
                ],
            ],
            (object) [
                'id' => 'item_price',
                'name' => 'Item Price',
                'sanitize' => FILTER_VALIDATE_FLOAT,
                'validator' => 'nullable|numeric',
                'default' => 0.00,
            ],
            (object) [
                'id' => 'item_qty',
                'name' => 'Item Qty',
                'sanitize' => FILTER_VALIDATE_INT,
                'validator' => 'nullable|numeric',
                'default' => 1,
            ],
            (object) [
                'id' => 'is_complete',
                'name' => 'Is Complete',
                'sanitize' => FILTER_VALIDATE_INT,
                'validator' => 'nullable|numeric',
                'default' => 1,
            ],
            (object) [
                'id' => 'alt_contact_id',
                'name' => 'DP Donor ID',
                'sanitize' => FILTER_VALIDATE_INT,
                'validator' => 'nullable|numeric',
                'default' => null,
            ],
            (object) [
                'id' => 'alt_transaction_id',
                'name' => 'DP Gift ID',
                'sanitize' => FILTER_VALIDATE_INT,
                'validator' => 'nullable|numeric',
                'default' => null,
            ],
        ]);
    }

    /**
     * Analyze a row.
     *
     * @param array $row
     */
    public function analyzeRow(array $row)
    {
        $messages = [];

        $likely_match = Member::findClosestMatchTo([
            'first_name' => $row['billing_first_name'],
            'last_name' => $row['billing_last_name'],
            'email' => $row['billing_email'],
            'zip' => $row['billing_zip'],
            'donor_id' => $row['alt_contact_id'],
        ]);

        if (! $likely_match) {
            $messages[] = 'A new supporter would be created for this contribution. (' . $row['billing_first_name'] . ' ' . $row['billing_last_name'] . ' - ' . $row['billing_zip'] . ' - ' . $row['billing_email'] . ')';
        }

        $variant = Variant::select('productinventory.*')
            ->join('product', 'product.id', '=', 'productinventory.productid')
            ->where('product.code', '=', $row['item_product_code'])
            ->where(function ($q) use ($row) {
                $q->where('productinventory.variantname', '=', $row['item_variant_name'])->orWhereRaw("ifnull(productinventory.variantname,'') = ''");
            })->orderBy('productinventory.variantname', 'desc')
            ->first();

        if (! $variant) {
            $messages[] = sprintf('Product variant name (%s) is not valid.', $row['item_variant_name']);
        }

        return (count($messages)) ? implode('', $messages) : null;
    }

    /**
     * Import a row.
     *
     * @param array $row
     */
    public function importRow(array $row)
    {
        // existing order in this batch
        // (all orders created in this batch will have the same created date and source = 'Import')
        $order = Order::where('invoicenumber', '=', $row['order_number'])
            ->where('source', '=', 'Import')
            ->where('createddatetime', '=', toUtc($row['order_local_time']))
            ->first();

        $existed = true;

        // if we don't find an existing order
        if (! $order) {
            $existed = false;

            $order_data = [];
            $order_data['client_uuid'] = $row['order_number'];
            $order_data['invoicenumber'] = $row['order_number'];
            $order_data['createddatetime'] = toUtc($row['order_local_time']);
            $order_data['source'] = 'Import';
            $order_data['created_at'] = toUtc($row['order_local_time']);
            $order_data['updated_at'] = toUtc($row['order_local_time']);
            $order_data['started_at'] = toUtc($row['order_local_time']);
            $order_data['ordered_at'] = toUtc($row['payment_local_time']);
            $order_data['client_ip'] = $row['user_ip'];
            $order_data['client_browser'] = $row['user_agent'];
            $order_data['currency_code'] = (string) currency();
            $order_data['functional_currency_code'] = (string) currency();
            $order_data['is_pos'] = false;
            $order_data['dp_sync_order'] = $row['alt_contact_id'] ? true : false;
            $order_data['is_processed'] = true;
            $order_data['iscomplete'] = $row['is_complete'] ?? true;
            $order_data['alt_contact_id'] = $row['alt_contact_id'] ?? null;
            $order_data['alt_transaction_id'] = $row['alt_transaction_id'] ?? null;

            // totals
            $order_data['subtotal'] = $row['subtotal_amt'] ?? 0;
            $order_data['shipping_amount'] = $row['shipping_amt'] ?? 0;
            $order_data['taxtotal'] = $row['tax_amt'] ?? 0;
            $order_data['totalamount'] = $row['total_amt'] ?? 0;

            // payment
            $order_data['confirmationdatetime'] = toUtc($row['payment_local_time']);
            $order_data['payment_type'] = $row['payment_type'];
            $order_data['payment_provider_id'] = PaymentProvider::getOfflineProviderId();

            // credit card
            if (in_array($row['payment_type'], ['Credit Card', 'Visa', 'MasterCard', 'American Express', 'Discover', 'Diners Club'])) {
                $order_data['confirmationnumber'] = $row['payment_auth'];
                $order_data['billingcardtype'] = $row['payment_type'];
                $order_data['billingcardlastfour'] = $row['cc_last_four'];

                if ($row['cc_expiry']) {
                    $order_data['billing_card_expiry_month'] = substr($row['cc_expiry'], 0, 2);
                    $order_data['billing_card_expiry_year'] = substr($row['cc_expiry'], 2, 2);
                }

                // check
            } elseif (in_array($row['payment_type'], ['Check', 'Cheque'])) {
                $order_data['check_number'] = $row['payment_auth'];
                $order_data['check_date'] = toUtc($row['payment_local_time']);
                $order_data['check_amt'] = $order_data['totalamount'];

            // cash
            } elseif (in_array($row['payment_type'], ['Cash'])) {
                $order_data['cash_received'] = $order_data['totalamount'];
                $order_data['cash_change'] = 0;

            // other
            } else {
                $order_data['payment_other_reference'] = $row['payment_auth'];
                $order_data['payment_other_note'] = $row['payment_type'];
            }

            // billing
            $order_data['billing_title'] = null;
            $order_data['billing_first_name'] = $row['billing_first_name'];
            $order_data['billing_last_name'] = $row['billing_last_name'];
            $order_data['billing_organization_name'] = $row['billing_organization_name'];
            $order_data['billingaddress1'] = $row['billing_address_1'];
            $order_data['billingaddress2'] = $row['billing_address_2'];
            $order_data['billingcity'] = $row['billing_city'];
            $order_data['billingstate'] = $row['billing_state'];
            $order_data['billingzip'] = $row['billing_zip'];
            $order_data['billingcountry'] = $row['billing_country'];
            $order_data['billingphone'] = $row['billing_phone'];
            $order_data['billingemail'] = $row['billing_email'];

            // shipping
            $order_data['courier_method'] = $row['shipping_method'];
            $order_data['shipping_title'] = null;
            $order_data['shipping_first_name'] = $row['shipping_first_name'];
            $order_data['shipping_last_name'] = $row['shipping_last_name'];
            $order_data['shipping_organization_name'] = $row['shipping_organization_name'];
            $order_data['shipaddress1'] = $row['shipping_address_1'];
            $order_data['shipaddress2'] = $row['shipping_address_2'];
            $order_data['shipcity'] = $row['shipping_city'];
            $order_data['shipstate'] = $row['shipping_state'];
            $order_data['shipzip'] = $row['shipping_zip'];
            $order_data['shipcountry'] = $row['shipping_country'];
            $order_data['shipphone'] = $row['shipping_phone'];
            $order_data['shipemail'] = $row['shipping_email'];

            // save order using MASS ASSIGNMENT
            // so no observers are run
            $order_id = Order::insertGetId($order_data);
            $order = Order::find($order_id);
        }

        // PRODUCT/VARIANT MATCH
        // --
        // Grab the variant where the product code matches
        // and either:
        //   - the variant name matches, OR
        //   - there is no variantname on the variant
        // and sequence the results by variant name (so those
        // with a variant name match will be sequenced first)
        $variant = Variant::select('productinventory.*')
            ->join('product', 'product.id', '=', 'productinventory.productid')
            ->where('product.code', '=', $row['item_product_code'])
            ->where(function ($q) use ($row) {
                $q->where('productinventory.variantname', '=', $row['item_variant_name'])->orWhereRaw("ifnull(productinventory.variantname,'') = ''");
            })->orderBy('productinventory.variantname', 'desc')
            ->first();

        // create a line-item on the order using
        // mass-assignment to avoid model observers
        OrderItem::insert([
            'productorderid' => $order->id,
            'productinventoryid' => $variant->id,
            'qty' => $row['item_qty'] ?? 1,
            'price' => $row['item_price'] ?? 0,
            'alt_transaction_id' => $row['alt_transaction_id'] ?? null,
        ]);

        // post-processing functions
        $order->updateAggregates()
            ->save();

        $order->saveOriginalData();

        if (! $order->member_id) {
            $order->createMember();
        }

        $order->applyMemberships();

        $order->grantDownloads();

        App::make(PaymentService::class)->createPaymentFromOrder($order);

        App::make(LedgerEntryService::class)->make($order);

        $order->member->saveLifeTimeTotals();

        return $existed ? 'updated_records' : 'added_records';
    }
}
