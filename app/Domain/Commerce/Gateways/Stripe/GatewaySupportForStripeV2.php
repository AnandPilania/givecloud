<?php

namespace Ds\Domain\Commerce\Gateways\Stripe;

use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Responses\ErrorResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stripe\Exception\CardException;
use Throwable;

/** @mixin \Ds\Domain\Commerce\Gateways\StripeGateway */
trait GatewaySupportForStripeV2
{
    public function getCaptureTokenUrlV2(): UrlResponse
    {
        return new ErrorResponse('Use Stripe.js to obtain a capture token');
    }

    public function chargeCaptureTokenV2(Order $order): TransactionResponse
    {
        if (! $this->request()->has('token')) {
            throw new InvalidArgumentException('Token required');
        }

        $data = [
            'amount' => money($order->totalamount, $order->currency_code)->getAmountInSubunits(),
            'currency' => $order->currency_code,
            'source' => $this->request()->get('token'),
            'metadata' => [
                'order_id' => $order->client_uuid,
            ],
        ];

        if ($order->shippable_items > 0) {
            $data['shipping'] = [
                'name' => trim("{$order->shipping_first_name} {$order->shipping_last_name}"),
                'phone' => $order->shipphone,
                'address' => [
                    'line1' => $order->shipaddress1,
                    'line2' => $order->shipaddress2,
                    'city' => $order->shipcity,
                    'state' => $order->shipstate,
                    'postal_code' => $order->shipzip,
                    'country' => $order->shipcountry,
                ],
            ];
        }

        try {
            $res = $this->stripe->charges->create($data, $this->getApiResourceOptions());

            $res = $this->createTransactionResponse()->setCharge($res);
        } catch (CardException $originalException) {
            try {
                $res = $this->getTransaction(
                    Arr::get($originalException->getJsonBody(), 'error.charge')
                );
            } catch (Throwable $e) {
                throw $originalException;
            }
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    public function getSourceTokenUrlV2(): UrlResponse
    {
        return new ErrorResponse('Use Stripe.js to obtain a capture token');
    }

    public function createSourceTokenV2(PaymentMethod $paymentMethod): TransactionResponse
    {
        if (! $this->request()->has('token')) {
            throw new InvalidArgumentException('Token required');
        }

        try {
            $customerId = $paymentMethod->stripe_customer_id ?? $paymentMethod->member->stripe_customer_id;

            if ($customerId) {
                $card = $this->stripe->customers->createSource($customerId, [
                    'source' => $this->request()->get('token'),
                ], $this->getApiResourceOptions());
            } elseif ($paymentMethod->billing_email) {
                $res = $this->stripe->customers->all([
                    'email' => $paymentMethod->billing_email,
                    'limit' => 1,
                ], $this->getApiResourceOptions());

                $customerId = data_get($res, 'data.0.id');

                if ($customerId) {
                    $card = $this->stripe->customers->createSource($customerId, [
                        'source' => $this->request()->get('token'),
                    ], $this->getApiResourceOptions());
                } else {
                    $res = $this->stripe->customers->create([
                        'email' => $paymentMethod->billing_email,
                        'source' => $this->request()->get('token'),
                        'expand' => ['sources'],
                    ], $this->getApiResourceOptions());

                    $customerId = $res->id;
                    $card = data_get($res, 'sources.data.0');
                }
            } else {
                $res = $this->stripe->customers->create([
                    'source' => $this->request()->get('token'),
                    'expand' => ['sources'],
                ], $this->getApiResourceOptions());

                $customerId = $res->id;
                $card = data_get($res, 'sources.data.0');
            }

            $paymentMethod->stripe_customer_id = $customerId;
            $paymentMethod->save();

            $paymentMethod->member->stripe_customer_id = $customerId;
            $paymentMethod->member->save();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse([
            'completed' => true,
            'cvv_code' => data_get($card, 'cvc_check'),
            'transaction_id' => data_get($card, 'id'),
            'account_type' => data_get($card, 'brand'),
            'cc_number' => data_get($card, 'last4'),
            'cc_exp' => str_pad(data_get($card, 'exp_month') . substr(data_get($card, 'exp_year'), 2, 2), 4, '0', STR_PAD_LEFT),
            'source_token' => data_get($card, 'id'),
        ]);
    }
}
