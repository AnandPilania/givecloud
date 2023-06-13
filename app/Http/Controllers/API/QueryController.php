<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Drops\AccountTypeDrop;
use Ds\Domain\Theming\Liquid\Drops\AddressDrop;
use Ds\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class QueryController extends DonationController
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function payments()
    {
        $data = Arr::defaults(request()->all(), [
            'ids' => null,
            'status' => 'succeeded',
            'financial_status' => 'paid',
            'since_id' => null,
            'created_at_min' => null,
            'created_at_max' => null,
            'updated_at_min' => null,
            'updated_at_max' => null,
            'limit' => 50,
        ]);

        $validator = Validator::make($data, [
            'ids' => 'nullable|regex:/^\d+(,\d+)*$/',
            'status' => 'in:succeeded,pending,failed,any',
            'financial_status' => 'in:paid,pending,refunded,partially_refunded,any',
            'since_id' => 'nullable|integer',
            'created_at_min' => 'nullable|date_format:Y-m-d\TH:i:sO',
            'created_at_max' => 'nullable|date_format:Y-m-d\TH:i:sO',
            'limit' => 'required|between:1,250',
        ], [
            'ids.regex' => 'Retrieve only contributions specified by a comma-separated list of contribution IDs.',
            'status.in' => 'Filter contributions by their status (succeeded, pending, failed or any).',
            'financial_status.in' => 'Filter contributions by their financial status. (paid, pending, refunded, partially_refunded, or any).',
            'since_id.integer' => 'Show contributions after the specified ID.',
            'created_at_min.date_format' => 'Show contributions created at or after date (format: 2014-04-25T16:15:47-0400).',
            'created_at_max.date_format' => 'Show contributions created at or before date (format: 2014-04-25T16:15:47-0400).',
            'limit' => 'The maximum number of results to show on a page (default: 50, maximum: 250).',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        $payments = Payment::query()
            ->with([
                'account.accountType',
                'orders.items.sponsorship',
                'orders.items.variant.product',
                'recurringPaymentProfiles',
            ])->orderBy('created_at', 'desc')
            ->take($data['limit']);

        if ($data['ids']) {
            $payments->whereIn('id', explode(',', $data['ids']));
            $payments->orderByReset()->orderBy('id', 'asc');
        }

        if ($data['status'] && $data['status'] !== 'any') {
            $payments->where('status', $data['status']);
        }

        if ($data['financial_status'] === 'paid') {
            $payments->where('paid', true);
        } elseif ($data['financial_status'] === 'pending') {
            $payments->where('status', 'pending');
        } elseif ($data['financial_status'] === 'refunded') {
            $payments->where('refunded', true);
        } elseif ($data['financial_status'] === 'partially_refunded') {
            $payments->where('amount_refunded', '>', 0);
        }

        if ($data['since_id']) {
            $payments->where('id', '>', $data['since_id']);
            $payments->orderByReset()->orderBy('id', 'asc');
        }

        if ($data['created_at_min']) {
            $payments->where('created_at', '>=', $data['created_at_min']);
        }

        if ($data['created_at_max']) {
            $payments->where('created_at', '<=', $data['created_at_max']);
        }

        return response([
            'payments' => $payments->get()->map(function ($payment) {
                return $this->transformPayment($payment);
            }),
        ]);
    }

    /**
     * @param \Ds\Models\Payment $payment
     * @return array
     */
    private function transformPayment(Payment $payment)
    {
        $data = Drop::factory($payment, 'Payment')->toArray();

        if ($payment->account) {
            $data['account'] = [
                'id' => $payment->account->id,
                'account_type' => $payment->account->accountType ? new AccountTypeDrop($payment->account->accountType) : null,
                'display_name' => $payment->account->display_name,
                'title' => $payment->account->title,
                'first_name' => $payment->account->first_name,
                'last_name' => $payment->account->last_name,
                'organization_name' => $payment->account->bill_organization_name,
                'email' => $payment->account->email,
                'email_opt_in' => $payment->account->email_opt_in,
                'nps' => $payment->account->nps,
                'billing_address' => new AddressDrop($payment->account, 'billing'),
                'shipping_address' => new AddressDrop($payment->account, 'shipping'),
            ];
        }

        if (count($payment->orders)) {
            $data['orders'] = [];

            foreach ($payment->orders as $order) {
                $orderData = [
                    'id' => $order->client_uuid,
                    'shipping_price' => $order->shipping_amount,
                    'subtotal_price' => $order->subtotal,
                    'tax_price' => $order->taxtotal,
                    'total_price' => $order->totalamount,
                    'currency' => $order->currency,
                    'payment_type' => $order->payment_type,
                    'billing_address' => new AddressDrop($order, 'billing'),
                    'shipping_address' => new AddressDrop($order, 'shipping'),
                    'referral_source' => $order->referral_source,
                    'comments' => $order->comments,
                    'line_items' => [],
                ];

                foreach ($order->items as $item) {
                    $item = Drop::factory($item, 'LineItem', [
                        'metadata' => null,
                        'product' => null,
                        'quantity_editable' => null,
                        'variant' => null,
                    ])->toArray();

                    unset($item['metadata'], $item['product'], $item['quantity_editable'], $item['variant']);

                    $orderData['line_items'][] = $item;
                }

                $data['orders'][] = $orderData;
            }
        }

        if (count($payment->recurringPaymentProfiles)) {
            $data['subscriptions'] = [];

            foreach ($payment->recurringPaymentProfiles as $rpp) {
                $sub = Drop::factory($rpp, 'Subscription', [
                    'payment_method' => null,
                    'payments' => null,
                ])->toArray();

                unset($sub['feature_image'], $sub['payment_method'], $sub['payments']);

                if ($rpp->sponsorship_id) {
                    $sub['sponsee_id'] = $rpp->sponsorship_id;
                }

                if ($rpp->productinventory_id) {
                    $sub['variant_id'] = $rpp->productinventory_id;
                }

                $data['subscriptions'][] = $sub;
            }
        }

        return $data;
    }
}
