<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentFailedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentSucceededAction;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\SyncablePaymentStatus;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Exceptions\TransactionException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\Shared\DateTime;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Omnipay\Common\Http\Client;
use Omnipay\NMI\Message\ThreeStepRedirectResponse;
use Omnipay\NMI\ThreeStepRedirectGateway;
use Throwable;

class NMIGateway extends AbstractGateway implements
    Gateway,
    CaptureTokens,
    SourceTokens,
    Refunds,
    PartialRefunds,
    SyncablePaymentStatus
{
    /** @var \Omnipay\NMI\ThreeStepRedirectGateway */
    protected $redirectApi;

    /** @var string */
    protected $redirectApiEndpoint = 'https://secure.nmi.com/api/v2/three-step';

    /** @var string|null */
    protected $queryApiEndpoint = 'https://secure.nmi.com/api/query.php';

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'nmi';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'NMI';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.nmi.com';
    }

    /**
     * Get the redirect api client.
     *
     * @return \Omnipay\NMI\ThreeStepRedirectGateway
     */
    protected function getRedirectApi()
    {
        if (! $this->redirectApi) {
            $httpClient = app(Client::class, ['httpClient' => Http::buildClientForDirectUsage()]);

            $this->redirectApi = app(ThreeStepRedirectGateway::class, compact('httpClient'));
            $this->redirectApi->initialize([
                'api_key' => $this->config('credential3'),
                'endpoint' => $this->redirectApiEndpoint,
            ]);
        }

        return $this->redirectApi;
    }

    protected function getQueryApiEndpoint(): string
    {
        if (empty($this->queryApiEndpoint)) {
            throw new MessageException(sprintf("%s doesn't support the Query API", $this->getDisplayName()));
        }

        return $this->queryApiEndpoint;
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
            'clientIp' => $this->getClientIp(),
            'redirect_url' => $returnUrl,
            'orderid' => $order->client_uuid,
            'amount' => round($order->totalamount, 2),
            'currency' => $order->currency_code,
            'sec_code' => 'WEB',
            'card' => [
                'email' => $order->billingemail,
                'billingFirstName' => $order->billing_first_name,
                'billingLastName' => $order->billing_last_name,
                'billingAddress1' => $order->billingaddress1,
                'billingAddress2' => $order->billingaddress2,
                'billingCity' => $order->billingcity,
                'billingState' => $order->billingstate,
                'billingPostcode' => $order->billingzip,
                'billingCountry' => $order->billingcountry,
                'billingPhone' => $order->billingphone,
                'shippingFirstName' => $order->shipping_first_name,
                'shippingLastName' => $order->shipping_last_name,
                'shippingAddress1' => $order->shipaddress1,
                'shippingAddress2' => $order->shipaddress2,
                'shippingCity' => $order->shipcity,
                'shippingState' => $order->shipstate,
                'shippingPostcode' => $order->shipzip,
                'shippingCountry' => $order->shipcountry,
                'shippingPhone' => $order->shipphone,
            ],
        ];

        if ($order->auth_attempts > 0) {
            $data['orderid'] .= '_' . str_pad($order->auth_attempts, 3, '0', STR_PAD_LEFT);
        }

        if ($this->name() === 'safesave') {
            $data['merchant_defined_field_1'] = 'DS';
        }

        try {
            /** @var \Omnipay\NMI\Message\ThreeStepRedirectResponse */
            $res = $this->getRedirectApi()->purchase($data)->send();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        if ($res->isSuccessful()) {
            return new UrlResponse($res->getFormUrl());
        }

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->isSuccessful(),
            'response' => (string) $res->getCode(),
            'response_text' => (string) $res->getMessage(),
            'gateway_data' => $res->getData(),
        ]);

        throw new TransactionException($res);
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        if (! $this->request()->has('token')) {
            throw new InvalidArgumentException('Token required');
        }

        $req = $this->getRedirectApi()->completeAction([
            'token_id' => $this->request()->input('token'),
        ]);

        try {
            /** @var \Omnipay\NMI\Message\ThreeStepRedirectResponse */
            $res = $req->send();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $res = $this->createTransactionResponse(
            $this->getChargeCaptureTokenResponseToArray($res)
        );

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    protected function getChargeCaptureTokenResponseToArray(ThreeStepRedirectResponse $res): array
    {
        return [
            'completed' => (bool) $res->isSuccessful(),
            'response' => (string) $res->getCode(),
            'response_text' => (string) $res->getMessage(),
            'avs_code' => (string) $res->getAVSResponse(),
            'cvv_code' => (string) $res->getCVVResponse(),
            'transaction_id' => (string) $res->getTransactionReference(),
            'cc_number' => (string) $res->getData()->billing->{'cc-number'},
            'cc_exp' => (string) $res->getData()->billing->{'cc-exp'},
            'ach_account' => (string) $res->getData()->billing->{'account-number'},
            'ach_routing' => (string) $res->getData()->billing->{'routing-number'},
            'ach_type' => (string) $res->getData()->billing->{'account-type'},
            'ach_entity' => (string) $res->getData()->billing->{'entity-type'},
            'source_token' => (string) $res->getCardReference(),
            'gateway_data' => $res->getData(),
        ];
    }

    /**
     * Get url required for creation a source token.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param string|null $returnUrl
     * @param string|null $cancelUrl
     * @param \Ds\Domain\Commerce\SourceTokenUrlOptions|null $options
     * @return \Ds\Domain\Commerce\Responses\UrlResponse
     */
    public function getSourceTokenUrl(PaymentMethod $paymentMethod, ?string $returnUrl = null, ?string $cancelUrl = null, ?SourceTokenUrlOptions $options = null): UrlResponse
    {
        $data = [
            'clientIp' => $this->getClientIp(),
            'redirect_url' => $returnUrl,
            'card' => [
                'billingFirstName' => $paymentMethod->billing_first_name,
                'billingLastName' => $paymentMethod->billing_last_name,
                'billingAddress1' => $paymentMethod->billing_address1,
                'billingAddress2' => $paymentMethod->billing_address2,
                'billingCity' => $paymentMethod->billing_city,
                'billingState' => $paymentMethod->billing_state,
                'billingPostcode' => $paymentMethod->billing_postal,
                'billingCountry' => $paymentMethod->billing_country,
                'billingPhone' => $paymentMethod->billing_phone,
            ],
        ];

        if ($this->name() === 'safesave') {
            $data['merchant_defined_field_1'] = 'DS';
        }

        try {
            /** @var \Omnipay\NMI\Message\ThreeStepRedirectResponse */
            $res = $this->getRedirectApi()->createCard($data)->send();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        if ($res->isSuccessful()) {
            return new UrlResponse($res->getFormUrl());
        }

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->isSuccessful(),
            'response' => (string) $res->getCode(),
            'response_text' => (string) $res->getMessage(),
            'gateway_data' => $res->getData(),
        ]);

        throw new TransactionException($res);
    }

    /**
     * Create a source token.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param \Ds\Domain\Commerce\SourceTokenCreateOptions|null $options
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function createSourceToken(PaymentMethod $paymentMethod, ?SourceTokenCreateOptions $options = null): TransactionResponse
    {
        return $this->chargeCaptureToken(new Order);
    }

    /**
     * Charge a source token.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param \Ds\Domain\Commerce\Money $amount
     * @param \Ds\Domain\Commerce\SourceTokenChargeOptions $options
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeSourceToken(PaymentMethod $paymentMethod, Money $amount, SourceTokenChargeOptions $options): TransactionResponse
    {
        $data = [
            'clientIp' => $this->getClientIp(),
            'amount' => $amount->amount,
            'currency' => $amount->currency_code,
            'cardReference' => $paymentMethod->token,
        ];

        if ($this->config('duplicate_window')) {
            $data['dup_check'] = $this->config('duplicate_window');
        }

        if ($this->name() === 'safesave') {
            $data['merchant_defined_field_1'] = 'DS';
        }

        try {
            /** @var \Omnipay\NMI\Message\ThreeStepRedirectResponse */
            $res = $this->getRedirectApi()->sale($data)->send();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->isSuccessful(),
            'response' => (string) $res->getCode(),
            'response_text' => (string) $res->getMessage(),
            'avs_code' => (string) $res->getAVSResponse(),
            'cvv_code' => (string) $res->getCVVResponse(),
            'transaction_id' => (string) $res->getTransactionReference(),
            'cc_number' => $paymentMethod->cc_expiry ? $paymentMethod->account_number : '',
            'cc_exp' => fromUtcFormat($paymentMethod->cc_expiry, 'my'),
            'ach_account' => $paymentMethod->cc_expiry ? '' : $paymentMethod->account_number,
            'ach_routing' => $paymentMethod->ach_routing,
            'ach_type' => $paymentMethod->ach_account_type,
            'ach_entity' => $paymentMethod->ach_entity_type,
            'source_token' => (string) $res->getCardReference(),
            'gateway_data' => $res->getData(),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    /**
     * Generate response for handling the token return via the iframe.
     *
     * @param string $redirectTo
     * @return \Illuminate\Http\Response
     */
    public function getTokenReturnIframeResponse(string $redirectTo)
    {
        if (Str::contains($redirectTo, '?')) {
            $redirectTo .= '&token-id=' . $this->request()->input('token-id');
        } else {
            $redirectTo .= '?token-id=' . $this->request()->input('token-id');
        }

        return response("<textarea>$redirectTo</textarea>");
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
        $data = [
            'transactionReference' => $transactionId,
        ];

        if ($fullRefund === false) {
            $data['amount'] = round($amount, 2);
        }

        if ($this->name() === 'safesave') {
            $data['merchant_defined_field_1'] = 'DS';
        }

        $transaction = $this->getTransaction($transactionId);

        if (empty($transaction)) {
            throw new GatewayException('Transaction not found in gateway.');
        }

        if ($transaction['condition'] === 'pendingsettlement' && ! $fullRefund) {
            throw new GatewayException('Transaction needs to be settled before a partial refund can be performed. Please try again in 24 hours.');
        }

        if ($transaction['condition'] === 'canceled') {
            return $this->createTransactionResponse([
                'completed' => true,
                'response' => 'succeeded',
                'response_text' => 'Voided',
                'transaction_id' => $transactionId,
            ]);
        }

        if ($refund = $this->getRefundForTransaction($transactionId, $transaction)) {
            return $this->createTransactionResponse([
                'completed' => true,
                'response' => 'succeeded',
                'response_text' => 'Refunded',
                'transaction_id' => $refund['transaction_id'],
            ]);
        }

        try {
            if ($transaction['condition'] === 'pendingsettlement') {
                $res = $this->getRedirectApi()->void($data)->send();
            } else {
                $res = $this->getRedirectApi()->refund($data)->send();
            }
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->isSuccessful(),
            'response' => $res->isSuccessful() ? 'succeeded' : 'failed',
            'response_text' => (string) $res->getMessage(),
            'transaction_id' => (string) $res->getTransactionReference(),
            'gateway_data' => $res->getData(),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new RefundException($res);
    }

    /**
     * Get a charge.
     *
     * @param string $transactionId
     * @return array|void|null
     */
    public function getTransaction(string $transactionId)
    {
        try {
            $res = Http::asForm()
                ->withOptions([
                    'connect_timeout' => 1,
                    'timeout' => 1,
                ])->post($this->getQueryApiEndpoint(), [
                    'security_key' => $this->config('credential3'),
                    'transaction_id' => $transactionId,
                ]);

            $transaction = $res->throw()->xml()->transaction;
        } catch (Throwable $e) {
            return null;
        }

        return $this->convertTransactionToArray($transaction);
    }

    private function convertTransactionToArray(?object $transaction): ?array
    {
        if (empty($transaction)) {
            return null;
        }

        $data = [
            'transaction_id' => (string) $transaction->transaction_id,
            'transaction_type' => (string) $transaction->transaction_type,
            'condition' => (string) $transaction->condition,
            'order_id' => (string) $transaction->order_id,
            'authorization_code' => (string) $transaction->authorization_code,
            'first_name' => (string) $transaction->first_name,
            'last_name' => (string) $transaction->last_name,
            'address_1' => (string) $transaction->address_1,
            'address_2' => (string) $transaction->address_2,
            'company' => (string) $transaction->company,
            'city' => (string) $transaction->city,
            'state' => (string) $transaction->state,
            'postal_code' => (string) $transaction->postal_code,
            'country' => (string) $transaction->country,
            'email' => (string) $transaction->email,
            'phone' => (string) $transaction->phone,
            'fax' => (string) $transaction->fax,
            'cc_number' => (string) $transaction->cc_number,
            'cc_hash' => (string) $transaction->cc_hash,
            'cc_exp' => (string) $transaction->cc_exp,
            'avs_response' => (string) $transaction->avs_response,
            'csc_response' => (string) $transaction->csc_response,
            'cardholder_auth' => (string) $transaction->cardholder_auth,
            'cc_start_date' => (string) $transaction->cc_start_date,
            'cc_issue_number' => (string) $transaction->cc_issue_number,
            'check_account' => (string) $transaction->check_account,
            'check_hash' => (string) $transaction->check_hash,
            'check_aba' => (string) $transaction->check_aba,
            'check_name' => (string) $transaction->check_name,
            'account_holder_type' => (string) $transaction->account_holder_type,
            'account_type' => (string) $transaction->account_type,
            'sec_code' => (string) $transaction->sec_code,
            'processor_id' => (string) $transaction->processor_id,
            'currency' => (string) $transaction->currency,
            'entry_mode' => (string) $transaction->entry_mode,
            'cc_bin' => (string) $transaction->cc_bin,
            'cc_type' => (string) $transaction->cc_type,
            'original_transaction_id' => (string) $transaction->original_transaction_id,
            'actions' => [],
        ];

        foreach ($transaction->action as $action) {
            $data['actions'][] = [
                'amount' => (string) $action->amount,
                'action_type' => (string) $action->action_type,
                'date' => (string) $action->date,
                'success' => (string) $action->success,
                'ip_address' => (string) $action->ip_address,
                'source' => (string) $action->source,
                'api_method' => (string) $action->api_method,
                'username' => (string) $action->username,
                'response_text' => (string) $action->response_text,
                'batch_id' => (string) $action->batch_id,
                'processor_batch_id' => (string) $action->processor_batch_id,
                'response_code' => (string) $action->response_code,
                'processor_response_text' => (string) $action->processor_response_text,
                'processor_response_code' => (string) $action->processor_response_code,
                'requested_amount' => (string) $action->requested_amount,
                'device_license_number' => (string) $action->device_license_number,
                'device_nickname' => (string) $action->device_nickname,
            ];
        }

        return $data;
    }

    public function getRefundForTransaction(string $transactionId, array $transaction = null): ?array
    {
        if (empty($transaction)) {
            $transaction = $this->getTransaction($transactionId);
        }

        $startDate = $transaction['actions'][0]['date'] ?? null;
        $startDate = $startDate ? DateTime::createFromFormat('YmdHis', $startDate) : 'yesterday';

        $refunds = Http::asForm()->post($this->getQueryApiEndpoint(), [
            'security_key' => $this->config('credential3'),
            'action_type' => 'refund',
            'start_date' => fromUtc($startDate)->startOfDay()->format('YmdHis'),
        ])->xml();

        $refunds = $refunds->xpath('transaction');
        $refunds = collect($refunds)->keyBy('original_transaction_id');

        return $this->convertTransactionToArray($refunds[$transactionId] ?? null);
    }

    /**
     * Get the customer vault data.
     *
     * @param string $customerVaultId
     * @return array|void
     */
    public function getCustomerVault($customerVaultId)
    {
        $res = Http::asForm()->post($this->getQueryApiEndpoint(), [
            'security_key' => $this->config('credential3'),
            'report_type' => 'customer_vault',
            'customer_vault_id' => $customerVaultId,
        ]);

        try {
            $customers = $res->throw()->xml()->xpath('customer_vault/customer[@id]');
        } catch (Throwable $e) {
            return;
        }

        if (empty($customers[0])) {
            return;
        }

        return [
            'customer_vault_id' => (string) $customers[0]->customer_vault_id,
            'first_name' => (string) $customers[0]->first_name,
            'last_name' => (string) $customers[0]->last_name,
            'address_1' => (string) $customers[0]->address_1,
            'address_2' => (string) $customers[0]->address_2,
            'company' => (string) $customers[0]->company,
            'city' => (string) $customers[0]->city,
            'state' => (string) $customers[0]->state,
            'postal_code' => (string) $customers[0]->postal_code,
            'country' => (string) $customers[0]->country,
            'email' => (string) $customers[0]->email,
            'phone' => (string) $customers[0]->phone,
            'cc_number' => (string) $customers[0]->cc_number,
            'cc_hash' => (string) $customers[0]->cc_hash,
            'cc_exp' => (string) $customers[0]->cc_exp,
            'cc_start_date' => (string) $customers[0]->cc_start_date,
            'cc_issue_number' => (string) $customers[0]->cc_issue_number,
            'check_account' => (string) $customers[0]->check_account,
            'check_hash' => (string) $customers[0]->check_hash,
            'check_aba' => (string) $customers[0]->check_aba,
            'check_name' => (string) $customers[0]->check_name,
            'account_holder_type' => (string) $customers[0]->account_holder_type,
            'account_type' => (string) $customers[0]->account_type,
            'created' => (string) $customers[0]->created,
            'updated' => (string) $customers[0]->updated,
            'account_updated' => (string) $customers[0]->account_updated,
        ];
    }

    /**
     * Get the data for customer vaults updated by account updater.
     *
     * @param \DateTimeInterface|string|null $date
     * @return \Illuminate\Support\Collection
     */
    public function getAccountUpdaterCustomerVaults($date = null)
    {
        try {
            $res = Http::asForm()->post($this->getQueryApiEndpoint(), [
                'security_key' => $this->config('credential3'),
                'report_type' => 'account_updater',
                'start_date' => fromUtc($date ?? '1 month ago')->startOfDay()->format('YmdHis'),
            ])->throw();

            $customers = collect();

            foreach ($res->xml()->xpath('customer_vault/customer[@id]') as $customer) {
                $customers[] = [
                    'customer_vault_id' => (string) $customer->customer_vault_id,
                    'first_name' => (string) $customer->first_name,
                    'last_name' => (string) $customer->last_name,
                    'address_1' => (string) $customer->address_1,
                    'address_2' => (string) $customer->address_2,
                    'company' => (string) $customer->company,
                    'city' => (string) $customer->city,
                    'state' => (string) $customer->state,
                    'postal_code' => (string) $customer->postal_code,
                    'country' => (string) $customer->country,
                    'email' => (string) $customer->email,
                    'phone' => (string) $customer->phone,
                    'cc_number' => (string) $customer->cc_number,
                    'cc_hash' => (string) $customer->cc_hash,
                    'cc_exp' => (string) $customer->cc_exp,
                    'cc_start_date' => (string) $customer->cc_start_date,
                    'cc_issue_number' => (string) $customer->cc_issue_number,
                    'check_account' => (string) $customer->check_account,
                    'check_hash' => (string) $customer->check_hash,
                    'check_aba' => (string) $customer->check_aba,
                    'check_name' => (string) $customer->check_name,
                    'account_holder_type' => (string) $customer->account_holder_type,
                    'account_type' => (string) $customer->account_type,
                    'created' => (string) $customer->created,
                    'updated' => (string) $customer->updated,
                    'account_updated' => (string) $customer->account_updated,
                ];
            }
        } catch (Throwable $e) {
            return collect();
        }

        return $customers->sortBy('updated')->values();
    }

    /**
     * Get settlement transactions.
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getSettlements($startDate = 'yesterday', $endDate = null)
    {
        try {
            $res = Http::asForm()
                ->withOptions([
                    'connect_timeout' => 1,
                    'timeout' => 30,
                ])->post($this->getQueryApiEndpoint(), [
                    'security_key' => $this->config('credential3'),
                    'action_type' => 'settle',
                    'condition' => 'complete',
                    'start_date' => fromLocal($startDate)->startOfDay()->toUtc()->format('YmdHis'),
                    'end_date' => fromLocal($endDate ?? $startDate)->endOfDay()->toUtc()->format('YmdHis'),
                ])->throw();

            $transactions = collect();

            foreach ($res->xml()->transaction as $transaction) {
                $transactions[] = [
                    'transaction_id' => (string) $transaction->transaction_id,
                    'amount' => (string) $transaction->action->amount,
                    'date' => (string) $transaction->action->date,
                    'success' => (string) $transaction->action->success,
                    'batch_id' => (string) $transaction->action->batch_id,
                    'response_text' => (string) $transaction->action->response_text,
                    'response_code' => (string) $transaction->action->response_code,
                ];
            }
        } catch (Throwable $e) {
            return collect();
        }

        return $transactions;
    }

    public function syncPaymentStatus(Payment $payment): void
    {
        if (empty($payment->reference_number)) {
            return;
        }

        $transaction = $this->getTransaction($payment->reference_number);

        if (! $transaction) {
            return;
        }

        if ($transaction['condition'] === 'complete') {
            $settled = collect($transaction['actions'])
                ->where('action_type', 'settle')
                ->first();

            if (empty($settled)) {
                return;
            }

            app(MarkPaymentSucceededAction::class)->execute($payment);
        } elseif ($transaction['condition'] === 'failed') {
            app(MarkPaymentFailedAction::class)->execute($payment);
        }
    }
}
