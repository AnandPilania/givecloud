<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Responses\RedirectToResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Refund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PayPal\Validation\JsonValidator;

class PayPalCheckoutGateway extends PayPalExpressGateway
{
    /** @var \PayPal\Rest\ApiContext */
    protected $apiContext;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'paypalcheckout';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'PayPal Checkout';
    }

    /**
     * Get the rest api client context.
     *
     * @return \PayPal\Rest\ApiContext
     */
    protected function getApiContext()
    {
        if (! $this->apiContext) {
            $this->apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $this->config('rest.client_id'),
                    $this->config('rest.secret')
                )
            );

            $this->apiContext->setConfig([
                'mode' => $this->config('test_mode') ? 'sandbox' : 'live',
                'http.headers.PayPal-Partner-Attribution-Id' => $this->config('bn_code'),
                'log.LogEnabled' => $this->config('logging.log_enabled'),
                'log.FileName' => $this->config('logging.filename'),
                'log.LogLevel' => $this->config('logging.log_level'),
            ]);
        }

        return $this->apiContext;
    }

    /**
     * Get url required for creation a capture token.
     *
     * @param \Ds\Models\Order $order
     * @param string|null $returnUrl
     * @param string|null $cancelUrl
     * @return \Ds\Domain\Commerce\Responses\UrlResponse
     */
    public function getCaptureTokenUrl(Order $order, ?string $returnUrl = null, ?string $cancelUrl = null): UrlResponse
    {
        $data = [
            'intent' => 'sale',
            'payer' => [
                'payment_method' => 'paypal',
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'commit',
            ],
            'redirect_urls' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
            'transactions' => [[
                'amount' => [
                    'total' => round($order->totalamount, 2),
                    'currency' => $order->currency_code,
                    'details' => [
                        'subtotal' => round($order->subtotal + $order->dcc_total_amount, 2),
                        'tax' => round($order->taxtotal, 2),
                        'shipping' => round($order->shipping_amount, 2),
                    ],
                ],
                'payee' => [
                    'merchant_id' => $this->config('credential1'),
                ],
                'payment_options' => [
                    'allowed_payment_method' => 'IMMEDIATE_PAY',
                ],
                'invoice_number' => $order->client_uuid,
                'notify_url' => secure_site_url('jpanel/webhooks/paypalcheckout-notify'),
            ]],
        ];

        if ($order->shippable_items) {
            $data['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';
            $data['transactions'][0]['item_list'] = [
                'shipping_address' => [
                    'recipient_name' => $order->shipname,
                    'line1' => $order->shipaddress1,
                    'line2' => $order->shipaddress2,
                    'city' => $order->shipcity,
                    'state' => $order->shipstate,
                    'postal_code' => $order->shipzip,
                    'country_code' => $order->shipcountry,
                    'phone' => $order->shipphone,
                ],
            ];
        }

        $payment = new Payment($data);

        try {
            $payment = $payment->create($this->getApiContext());
        } catch (PayPalConnectionException $e) {
            $this->throwGatewayException($e);
        }

        return new RedirectToResponse($payment->getApprovalLink(), [
            'payment_id' => $payment->getId(),
            'token' => $payment->getToken(),
        ]);
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        if (! $this->request()->filled(['paymentId', 'PayerID'])) {
            throw new GatewayException('Payment Id and Payer Id are required to complete checkout');
        }

        $payment = Payment::get($this->request()->input('paymentId'), $this->getApiContext());

        $execution = new PaymentExecution;
        $execution->setPayerId($this->request()->input('PayerID'));

        try {
            $payment = $payment->execute($execution, $this->getApiContext());
        } catch (PayPalConnectionException $e) {
            $this->throwGatewayException($e);
        }

        $res = $this->createTransactionResponseFromPayment($payment);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    /**
     * Refund a charge.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param bool $fullRefund
     * @param \Ds\Models\PaymentMethod|null $paymentMethod
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function refundCharge(string $transactionId, ?float $amount = null, bool $fullRefund = true, ?PaymentMethod $paymentMethod = null): TransactionResponse
    {
        $req = new RefundRequest([
            'amount' => [
                'currency' => $this->config('currency'),
                'total' => round($amount, 2),
            ],
        ]);

        $sale = (new Sale)->setId($transactionId);

        try {
            $refund = $sale->refundSale($req, $this->getApiContext());
        } catch (PayPalConnectionException $e) {
            $this->throwGatewayException($e);
        }

        $res = $this->createTransactionResponse([
            'completed' => $refund->state === 'completed',
            'response' => (string) $refund->state === 'completed' ? '1' : '2',
            'response_text' => (string) $refund->state,
            'transaction_id' => (string) $refund->id,
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new RefundException($res);
    }

    /**
     * Convert a Payment into a TransactionResponse.
     *
     * @param \PayPal\Api\Payment $payment
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function createTransactionResponseFromPayment(Payment $payment): TransactionResponse
    {
        $sale = data_get($payment, 'transactions.0.related_resources.0.sale');

        if (! $sale) {
            $this->throwGatewayException("Payment doesn't have a related Sale");
        }

        return $this->createTransactionResponse([
            'completed' => $sale->state === 'completed',
            'response' => (string) $sale->state === 'completed' ? 'succeeded' : 'failed',
            'response_text' => (string) data_get($payment, 'failure_reason', $sale->state),
            'avs_code' => (string) data_get($sale, 'processor_response.avs_code'),
            'cvv_code' => (string) data_get($sale, 'processor_response.cvv_code'),
            'transaction_id' => (string) $sale->id,
            'source_token' => (string) $sale->billing_agreement_id,
        ]);
    }

    /**
     * Throw a GatewayException with data from a PayPal response.
     *
     * @param mixed $data
     */
    protected function throwGatewayException($data = null)
    {
        if ($data instanceof PayPalConnectionException) {
            $error = $data->getData();

            if (is_string($error) && JsonValidator::validate($error, true)) {
                $error = json_decode($error);
                $error = $error->details[0]->issue ?? $error->error_description ?? $error->message ?? $data->getMessage();
                throw new GatewayException($error, $data->getCode());
            }

            throw new GatewayException($data->getMessage(), $data->getCode());
        }

        parent::throwGatewayException($data);
    }

    public function getViewConfig(): ?object
    {
        // https://developer.paypal.com/docs/checkout/quick-start/
        return (object) [
            'name' => $this->name(),
            'scripts' => [['src' => 'https://www.paypalobjects.com/api/checkout.js', 'data-version-4' => true]],
            'settings' => [
                'client_id' => $this->config('rest.client_id'),
                'merchant_id' => $this->config('credential1'),
                'environment' => $this->config('test_mode') ? 'sandbox' : 'production',
            ],
        ];
    }
}
