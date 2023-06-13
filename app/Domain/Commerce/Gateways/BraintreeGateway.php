<?php

namespace Ds\Domain\Commerce\Gateways;

use Braintree\Customer as BraintreeCustomer;
use Braintree\Gateway as BraintreeClientGateway;
use Braintree\Result\UsBankAccountVerification;
use Braintree\Transaction as BraintreeTransaction;
use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\ApplePayMerchantDomains;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\ErrorResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class BraintreeGateway extends AbstractGateway implements
    Gateway,
    SourceTokens,
    CaptureTokens,
    Refunds,
    PartialRefunds,
    ApplePayMerchantDomains,
    Viewable
{
    /** @var \Braintree\Gateway */
    protected $gateway;

    public function name(): string
    {
        return 'braintree';
    }

    public function getDisplayName(): string
    {
        return 'Braintree';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.braintreepayments.com';
    }

    public function gateway(): BraintreeClientGateway
    {
        if (! $this->gateway) {
            return $this->gateway = app(BraintreeClientGateway::class, [
                'config' => [
                    'environment' => $this->config('test_mode') ? 'sandbox' : 'production',
                    'merchantId' => $this->config('merchant_id'),
                    'publicKey' => $this->config('api_public_key'),
                    'privateKey' => $this->config('api_private_key'),
                ],
            ]);
        }

        return $this->gateway;
    }

    public function getMerchantAccountIdForCurrency(?string $currency = null): ?string
    {
        $merchantAccountIds = Arr::wrap($this->config('merchant_account_id'));

        // Find with specified currency, fallback to default
        if ($merchantAccountId = Arr::get($merchantAccountIds, $currency ?? sys_get('dpo_currency'), false)) {
            return $merchantAccountId;
        }

        // If currency is not specified, return first.
        if ($currency === null && $merchantAccountId = Arr::first($merchantAccountIds, null, false)) {
            return $merchantAccountId;
        }

        return null;
    }

    public function getCaptureTokenUrl(
        Order $order,
        ?string $returnUrl = null,
        ?string $cancelUrl = null
    ): UrlResponse {
        if ($this->request()->input('payment_type') === ContributionPaymentType::WALLET_PAY) {
            $billingContact = $this->request()->input('billing_contact');
            $this->updateBillingDetailsFromBillingContact($billingContact, $order, null);
        }

        return new ErrorResponse('Use Braintree JavaScript v3 SDK to obtain a token');
    }

    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        if (! $token = $this->request()->input('token.nonce')) {
            throw new GatewayException('Token required');
        }

        $paymentNonceKey = 'paymentMethodNonce';
        $paymentNonce = $token;

        if ($this->request()->input('token.type') === 'us_bank_account') {
            $customerId = $order->member->braintree_customer_id ?? null;

            $customerId ??= $this->createCustomer([
                'firstName' => $order->billing_first_name,
                'lastName' => $order->billing_last_name,
                'email' => $order->billingemail,
                'phone' => $order->billingphone,
            ]);

            $response = $this->vaultPaymentMethod($token, $customerId, [
                'usBankAccountVerificationMethod' => UsBankAccountVerification::NETWORK_CHECK,
            ]);

            $paymentNonceKey = 'paymentMethodToken';
            $paymentNonce = $response->paymentMethod->token;
        }

        return $this->charge(money($order->totalamount, $order->currency_code), [
            $paymentNonceKey => $paymentNonce,
            'orderId' => $order->id,
        ]);
    }

    public function getSourceTokenUrl(
        PaymentMethod $paymentMethod,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
        ?SourceTokenUrlOptions $options = null
    ): UrlResponse {
        $contribution = $options->contribution ?? null;

        if ($this->request()->input('payment_type') === ContributionPaymentType::WALLET_PAY) {
            $billingContact = $this->request()->input('billing_contact');
            $this->updateBillingDetailsFromBillingContact($billingContact, $contribution, $paymentMethod);
        }

        return new ErrorResponse('Use Braintree JavaScript v3 SDK to obtain a token');
    }

    private function updateBillingDetailsFromBillingContact(?array $billingContact, ?Order $contribution, ?PaymentMethod $paymentMethod): void
    {
        if (empty($billingContact)) {
            throw new GatewayException('Wallet pay requires a billing contact.');
        }

        if ($contribution) {
            $contribution->billing_first_name = $billingContact['first_name'] ?? Str::firstName($billingContact['name'] ?? null);
            $contribution->billing_last_name = $billingContact['last_name'] ?? Str::lastName($billingContact['name'] ?? null);
            $contribution->billingemail = $billingContact['email'] ?? null;
            $contribution->billingaddress1 = $billingContact['address_line1'] ?? null;
            $contribution->billingaddress2 = $billingContact['address_line2'] ?? null;
            $contribution->billingcity = $billingContact['city'] ?? null;
            $contribution->billingstate = $billingContact['state'] ?? null;
            $contribution->billingzip = $billingContact['postal_code'] ?? null;
            $contribution->billingcountry = $billingContact['country'] ?? null;
            $contribution->billingphone = $billingContact['phone'] ?? null;
            $contribution->save();
        }

        if ($paymentMethod) {
            $paymentMethod->billing_first_name = $billingContact['first_name'] ?? Str::firstName($billingContact['name'] ?? null);
            $paymentMethod->billing_last_name = $billingContact['last_name'] ?? Str::lastName($billingContact['name'] ?? null);
            $paymentMethod->billing_email = $billingContact['email'] ?? null;
            $paymentMethod->billing_address1 = $billingContact['address_line1'] ?? null;
            $paymentMethod->billing_address2 = $billingContact['address_line2'] ?? null;
            $paymentMethod->billing_city = $billingContact['city'] ?? null;
            $paymentMethod->billing_state = $billingContact['state'] ?? null;
            $paymentMethod->billing_postal = $billingContact['postal_code'] ?? null;
            $paymentMethod->billing_country = $billingContact['country'] ?? null;
            $paymentMethod->billing_phone = $billingContact['phone'] ?? null;
            $paymentMethod->save();

            if ($paymentMethod->member && empty($paymentMethod->member->bill_address_01)) {
                $paymentMethod->member->bill_address_01 = $paymentMethod->billing_address1;
                $paymentMethod->member->bill_address_02 = $paymentMethod->billing_address2;
                $paymentMethod->member->bill_city = $paymentMethod->billing_city;
                $paymentMethod->member->bill_state = $paymentMethod->billing_state;
                $paymentMethod->member->bill_zip = $paymentMethod->billing_postal;
                $paymentMethod->member->bill_country = $paymentMethod->billing_country;
                $paymentMethod->member->save();
            }
        }

        // in the case of wallet pay the billing information required to create
        // a supporter isn't available prior to the getSourceTokenUrl() call so after
        // we'll need to create the supporter and attach to payment method
        if ($contribution && $paymentMethod) {
            $contribution->createMember();

            $paymentMethod->member_id = $contribution->member_id;
            $paymentMethod->save();

            $paymentMethod->load('member');
        }
    }

    public function createSourceToken(PaymentMethod $paymentMethod, ?SourceTokenCreateOptions $options = null): TransactionResponse
    {
        // verify that the payment method is linked to an account
        if (! $paymentMethod->member) {
            throw new GatewayException('Account required to setup payment method');
        }

        if (! $token = $this->request()->input('token.nonce')) {
            throw new GatewayException('Token required');
        }

        if (! $paymentMethod->member->braintree_customer_id) {
            $customerId = $this->createCustomer([
                'firstName' => $paymentMethod->billing_first_name,
                'lastName' => $paymentMethod->billing_last_name,
                'email' => $paymentMethod->billing_email,
                'phone' => $paymentMethod->billing_phone,
            ]);

            $paymentMethod->member->braintree_customer_id = $customerId;
            $paymentMethod->member->save();
        }

        $options = [
            'makeDefault' => true,
        ];

        if ($this->request()->input('token.type') === 'us_bank_account') {
            $options['usBankAccountVerificationMethod'] = UsBankAccountVerification::NETWORK_CHECK;
        } else {
            $options['verifyCard'] = true;
        }

        return $this->createTransactionResponse()->setResult(
            $this->vaultPaymentMethod($token, $paymentMethod->member->braintree_customer_id, $options),
        );
    }

    public function createCustomer(array $billingData): string
    {
        $result = $this->gateway()->customer()->create($billingData);

        if (! $result->success) {
            throw new GatewayException($result->message);
        }

        return $result->customer->id;
    }

    /**
     * @return \Braintree\Result\Successful|\stdClass
     */
    public function vaultPaymentMethod(string $paymentMethodNonce, string $customerId, array $options = [])
    {
        $result = $this->gateway()->paymentMethod()->create([
            'customerId' => $customerId,
            'paymentMethodNonce' => $paymentMethodNonce,
            'options' => $options,
        ]);

        if (! $result->success) {
            throw new GatewayException($result->message);
        }

        return $result;
    }

    public function chargeSourceToken(PaymentMethod $paymentMethod, Money $amount, SourceTokenChargeOptions $options): TransactionResponse
    {
        $data = ['paymentMethodToken' => $paymentMethod->token];

        if ($options->initiatedBy === CredentialOnFileInitiatedBy::MERCHANT) {
            // $data['transactionSource'] = $options->recurring ? 'recurring' : 'unscheduled';
        }

        return $this->charge($amount, $data);
    }

    protected function charge(Money $amount, array $data = []): TransactionResponse
    {
        $data = array_merge_recursive($data, [
            'amount' => $amount->getAmount(),
            'merchantAccountId' => $this->getMerchantAccountIdForCurrency($amount->getCurrencyCode()),
            'options' => [
                'submitForSettlement' => true,
            ],
            'riskData' => [
                'customerIp' => $this->request()->ip(),
            ],
        ]);

        try {
            $res = $this->createTransactionResponse()->setResult(
                $this->gateway()->transaction()->sale($data),
            );
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    public function refundCharge(
        string $transactionId,
        ?float $amount = null,
        bool $fullRefund = true,
        ?PaymentMethod $paymentMethod = null
    ): TransactionResponse {
        $transaction = $this->gateway()->transaction()->find($transactionId);

        if ($transaction->status === 'submitted_for_settlement' && ! $fullRefund) {
            throw new GatewayException('Transaction needs to be settled before a partial refund can be performed. Please try again in 24 hours.');
        }

        if ($transaction->status === 'submitted_for_settlement' && $fullRefund) {
            $response = $this->gateway()->transaction()->void($transactionId);
        } else {
            $options = ['merchantAccountId' => $this->getMerchantAccountIdForCurrency($transaction->currencyIsoCode)];

            if (! $fullRefund) {
                $options['amount'] = round($amount, 2);
            }

            $response = $this->gateway()->transaction()->refund($transactionId, $options);
        }

        $result = $this->createTransactionResponse([
            'completed' => $response->success,
            'response' => $response->success ? 'succeeded' : 'failed',
            'response_text' => ! $response->success ? $response->message : '',
            'gateway_data' => (array) $response,
            'transaction_id' => $response->success ? $response->transaction->id : '',
        ]);

        if ($result->isCompleted()) {
            return $result;
        }

        throw new RefundException($result);
    }

    public function getCustomer(string $customerId): ?BraintreeCustomer
    {
        return $this->gateway()->customer()->find($customerId) ?: null;
    }

    public function getPaymentMethod(string $paymentMethodId): ?object
    {
        return $this->gateway()->paymentMethod()->find($paymentMethodId) ?: null;
    }

    public function getTransaction(string $transactionId): ?BraintreeTransaction
    {
        return $this->gateway()->transaction()->find($transactionId) ?: null;
    }

    public function registerApplePayMerchantDomain(string $domain): bool
    {
        try {
            $res = $this->gateway()->applePay()->registerDomain($domain);
        } catch (Throwable $e) {
            return false;
        }

        return (bool) $res->success;
    }

    public function getAppleDeveloperMerchantIdDomainAssociationFile(): string
    {
        return (string) Http::get('https://assets.braintreegateway.com/web/static/apple-pay/apple-developer-merchantid-domain-association');
    }

    /**
     * Create a transaction response.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Gateways\Braintree\TransactionResponse
     */
    protected function createTransactionResponse(array $data = []): Braintree\TransactionResponse
    {
        if (! array_key_exists('ip_address', $data)) {
            $data['ip_address'] = $this->getClientIp();
        }

        return new Braintree\TransactionResponse($this->provider(), $data);
    }

    public function getViewConfig(): ?object
    {
        $scripts = [
            'https://js.braintreegateway.com/web/3.85.5/js/client.min.js',
            'https://js.braintreegateway.com/web/3.85.5/js/data-collector.min.js',
            'https://js.braintreegateway.com/web/3.85.5/js/hosted-fields.min.js',
        ];

        if ($this->config('is_ach_allowed')) {
            $scripts[] = 'https://js.braintreegateway.com/web/3.85.5/js/us-bank-account.min.js';
        }

        if ($this->config('is_apple_pay_allowed')) {
            $scripts[] = 'https://js.braintreegateway.com/web/3.85.5/js/apple-pay.min.js';
        }

        if ($this->config('is_google_pay_allowed')) {
            $scripts[] = 'https://pay.google.com/gp/p/js/pay.js';
            $scripts[] = 'https://js.braintreegateway.com/web/3.85.5/js/google-payment.min.js';
        }

        return (object) [
            'name' => $this->name(),
            'scripts' => $scripts,
            'settings' => [
                'environment' => $this->config('test_mode') ? 'sandbox' : 'production',
                'authorization' => rescueQuietly(fn () => $this->gateway()->clientToken()->generate()),
                'ach_allowed' => (int) $this->config('is_ach_allowed'),
                'apple_pay_allowed' => (int) $this->config('is_apple_pay_allowed'),
                'google_pay_allowed' => (int) $this->config('is_google_pay_allowed'),
                'google_merchant_id' => $this->config('google_merchant_id'),
            ],
        ];
    }
}
