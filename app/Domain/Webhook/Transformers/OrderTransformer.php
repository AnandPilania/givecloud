<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    /** @var array */
    protected $defaultIncludes = [
        'account',
        'line_items',
    ];

    /**
     * @param \Ds\Models\Order $order
     * @return array
     */
    public function transform(Order $order)
    {
        $txnCost = (float) sys_get('dcc_cost_per_order');
        $txnRate = (float) sys_get('dcc_percentage');

        $data = [
            'id' => (int) $order->id,
            'test' => (bool) $order->is_test,
            'order_number' => $order->invoicenumber ?: null,
            'vendor_contact_id' => $order->alt_contact_id ?: null,
            'vendor_txn_ids' => array_filter(explode(',', $order->alt_transaction_id), 'strlen'),
            'subtotal_amount' => number_format($order->subtotal, 2, '.', ''),
            'shipping_amount' => number_format($order->shipping_amount, 2, '.', ''),
            'total_weight' => (float) $order->total_weight,
            'total_tax' => number_format($order->taxtotal, 2, '.', ''),
            'total_amount' => number_format($order->totalamount, 2, '.', ''),
            'currency' => sys_get('dpo_currency'),
            'processing_fee' => number_format($txnCost + ($order->totalamount * $txnRate / 100), 2, '.', ''),
            'refer_source' => null,
            'order_source' => ucwords($order->source) ?: null,
            'billing_address' => [
                'first_name' => $order->billing_first_name ?: null,
                'last_name' => $order->billing_last_name ?: null,
                'company' => null,
                'email' => $order->billingemail ?: null,
                'address1' => $order->billingaddress1 ?: null,
                'address2' => $order->billingaddress2 ?: null,
                'city' => $order->billingcity ?: null,
                'state' => $order->billingstate ?: null,
                'zip' => $order->billingzip ?: null,
                'country' => $order->billingcountry ?: null,
                'phone' => $order->billingphone ?: null,
            ],
            'shipping_address' => [
                'first_name' => $order->shipping_first_name ?: null,
                'last_name' => $order->shipping_last_name ?: null,
                'company' => null,
                'email' => $order->shipemail ?: null,
                'address1' => $order->shipaddress1 ?: null,
                'address2' => $order->shipaddress2 ?: null,
                'city' => $order->shipcity ?: null,
                'state' => $order->shipstate ?: null,
                'zip' => $order->shipzip ?: null,
                'country' => $order->shipcountry ?: null,
                'phone' => $order->shipphone ?: null,
            ],
            'transactions' => [],
            'created_at' => toUtcFormat($order->created_at ?? $order->started_at, 'json'),
            'updated_at' => toUtcFormat($order->updated_at ?? $order->started_at, 'json'),
        ];

        if ($order->confirmationdatetime) {
            $transaction = [
                'gateway' => null,
                'transaction_id' => $order->confirmationnumber,
                'transaction_type' => 'payment',
                'amount' => number_format($order->totalamount, 2, '.', ''),
                'funding_source' => null,
                'cc_type' => null,
                'cc_number' => null,
                'response_text' => $order->response_text,
                'completed_at' => toUtcFormat($order->confirmationdatetime, 'json'),
            ];

            switch ($order->payment_type_formatted) {
                case 'Cash':
                    $transaction['gateway'] = 'offline';
                    $transaction['transaction_id'] = null;
                    $transaction['funding_source'] = 'cash';
                    break;
                case 'Check':
                    $transaction['gateway'] = 'offline';
                    $transaction['transaction_id'] = $order->check_number;
                    $transaction['funding_source'] = 'check';
                    break;
                case 'Other':
                    $transaction['gateway'] = 'offline';
                    $transaction['transaction_id'] = $order->payment_other_reference;
                    $transaction['funding_source'] = 'other';
                    break;
                case 'PayPal':
                    $transaction['gateway'] = 'paypal';
                    $transaction['funding_source'] = 'paypal';
                    break;
                default:
                    $transaction['gateway'] = 'networkmerchants';
                    $transaction['funding_source'] = 'creditcard';
                    $transaction['cc_type'] = $order->payment_type_formatted;
                    $transaction['cc_number'] = '*************' . $order->billingcardlastfour;
            }

            $data['transactions'][] = $transaction;
        }

        return $data;
    }

    /**
     * @param \Ds\Models\Order $order
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeAccount(Order $order)
    {
        if ($order->member) {
            return $this->item($order->member, new MemberTransformer);
        }
    }

    /**
     * @param \Ds\Models\Order $order
     * @return \League\Fractal\Resource\Collection
     */
    public function includeLineItems(Order $order)
    {
        return $this->collection($order->items, new OrderItemTransformer);
    }
}
