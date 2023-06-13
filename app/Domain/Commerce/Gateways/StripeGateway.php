<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\ApplePayMerchantDomains;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\OAuth;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\Exception;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\AccessTokenResponse;
use Ds\Domain\Commerce\Responses\JsonResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Ds\Repositories\PaymentMethodRepository;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

class StripeGateway extends AbstractGateway implements
    Gateway,
    OAuth,
    CaptureTokens,
    SourceTokens,
    Refunds,
    PartialRefunds,
    ApplePayMerchantDomains,
    Viewable
{
    use Stripe\GatewaySupportForStripeV2;

    /** @var string */
    protected $configKey = 'stripe';

    /** @var \Stripe\StripeClient */
    protected $stripe;

    /**
     * Create an instance.
     */
    public function __construct(PaymentProvider $provider, Config $config, Request $request)
    {
        parent::__construct($provider, $config, $request);

        $this->stripe = app(StripeClient::class, [
            'config' => [
                'api_key' => $this->config('secret_key'),
                'client_id' => sys_get('use_stripe_connect') ? $this->config('client_id') : null,
                'stripe_version' => $this->config('api_version'),
            ],
        ]);
    }

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'stripe';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Stripe';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.stripe.com';
    }

    /**
     * Check if gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        $provider = $this->provider();

        if (! sys_get('use_stripe_connect')) {
            return preg_match('/_test_/', $provider->config['secret_key'] ?? '_live_') === 1;
        }

        if ($provider->credential2) {
            return preg_match('/_test_/', $provider->credential2) === 1;
        }

        return parent::isTestMode();
    }

    /**
     * Create an authentication URL.
     *
     * @param string $state
     * @param string|null $returnUrl
     * @return string
     */
    public function getAuthenticationUrl(string $state, ?string $returnUrl = null): string
    {
        try {
            return $this->stripe->oauth->authorizeUrl([
                'scope' => 'read_write',
                'state' => $state,
            ]);
        } catch (\Stripe\Exception\OAuth\OAuthErrorException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get permanent access token which allows use of the API on behalf of the user.
     *
     * @param string|null $returnUrl
     * @return \Ds\Domain\Commerce\Responses\AccessTokenResponse
     */
    public function getAccessToken(?string $returnUrl = null): AccessTokenResponse
    {
        if ($this->request()->input('code')) {
            try {
                $res = $this->stripe->oauth->token([
                    'grant_type' => 'authorization_code',
                    'code' => $this->request()->input('code'),
                ]);
            } catch (\Stripe\Exception\OAuth\OAuthErrorException $e) {
                throw new GatewayException($e->getMessage(), $e->getCode(), $e);
            }

            return new AccessTokenResponse([
                'account_id' => $res->stripe_user_id,
                'access_token' => $res->access_token,
                'refresh_token' => $res->refresh_token,
                'publishable_key' => $res->stripe_publishable_key,
            ]);
        }

        if ($this->request()->input('error')) {
            $error = sprintf(
                '%s (Code: %s)',
                $this->request()->input('error_description'),
                $this->request()->input('error')
            );
            throw new GatewayException($error);
        }

        throw new GatewayException('Unable to obtain access token');
    }

    public function getDefaultCurrency(): string
    {
        $currencyCode = $this->config('default_currency');

        if (sys_get('use_stripe_connect') && empty($currencyCode)) {
            $account = rescueQuietly(fn () => $this->stripe->accounts->retrieve($this->config('credential1')));

            $currencyCode = strtoupper($account->default_currency ?? '') ?: null;
            $this->provider->config = array_merge($this->provider->config ?? [], ['default_currency' => $currencyCode]);
            $this->provider->save();
        }

        return $currencyCode ?: sys_get('dpo_currency');
    }

    /**
     * Get any applicable options to be passed into requests
     * made using the Stripe ApiResource classes.
     *
     * @return array
     */
    protected function getApiResourceOptions(): array
    {
        if (sys_get('use_stripe_connect')) {
            return [
                'stripe_account' => $this->config('credential1'),
            ];
        }

        return [];
    }

    /**
     * Verify the access token works.
     *
     * @return bool
     */
    public function verifyAccessToken(): bool
    {
        $accessToken = $this->config('credential2');

        if (empty($accessToken)) {
            return false;
        }

        try {
            $this->stripe->customers->all(['limit' => 1], $this->getApiResourceOptions());
        } catch (Throwable $e) {
            return false;
        }

        return true;
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
        if ($this->usingStripeV2()) {
            return $this->getCaptureTokenUrlV2();
        }

        $stripePaymentMethod = $this->request()->filled('payment_method')
            ? rescueQuietly(fn () => $this->getPaymentMethod($this->request()->input('payment_method')))
            : null;

        if ($this->request()->input('payment_type') === ContributionPaymentType::WALLET_PAY) {
            $this->updateBillingDetailsFromPaymentMethod($stripePaymentMethod, $order, null);
        }

        $customer = $this->firstOrCreateCustomer(
            $order->member->stripe_customer_id ?? null,
            $order->billingemail ?? null,
            $order->currency_code ?? null,
            [
                'name' => $order->billing_display_name ?? null,
                'email' => $order->billingemail ?? null,
                'phone' => $order->billingphone ?? null,
                'address' => [
                    'line1' => $order->billingaddress1 ?? null,
                    'line2' => $order->billingaddress2 ?? null,
                    'city' => $order->billingcity ?? null,
                    'postal_code' => $order->billingstate ?? null,
                    'state' => $order->billingzip ?? null,
                    'country' => $order->billingcountry ?? null,
                ],
            ],
        );

        return $this->generatePaymentIntentResponse($order, $customer, null, $stripePaymentMethod);
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        if ($this->usingStripeV2()) {
            return $this->chargeCaptureTokenV2($order);
        }

        $res = $this->createTransactionResponse([]);
        $token = $this->request()->input('token');

        if (Str::startsWith($token, 'pi_')) {
            $stripePaymentMethod = $this->validatePaymentIntentSucceeded($res, $token);
        } else {
            throw new InvalidArgumentException('Token required');
        }

        if ($order->member) {
            $order->member->stripe_customer_id ??= $res->getCustomerRef() ?: null;
            $order->member->save();
        }

        if ($res->getCardWallet()) {
            $this->updateContributionAndPaymentMethodFromStripePaymentMethod($stripePaymentMethod, $order, null);
        }

        return $res;
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
        if ($this->usingStripeV2()) {
            return $this->getSourceTokenUrlV2();
        }

        $contribution = $options->contribution ?? null;

        $stripePaymentMethod = $this->request()->filled('payment_method')
            ? rescueQuietly(fn () => $this->getPaymentMethod($this->request()->input('payment_method')))
            : null;

        if ($this->request()->input('payment_type') === ContributionPaymentType::WALLET_PAY) {
            $this->updateBillingDetailsFromPaymentMethod($stripePaymentMethod, $contribution, $paymentMethod);
        }

        $customer = $this->firstOrCreateCustomer(
            $paymentMethod->stripe_customer_id ?? $paymentMethod->member->stripe_customer_id ?? null,
            $paymentMethod->billing_email ?? $contribution->billingemail ?? null,
            $paymentMethod->currency_code ?? $contribution->currency_code ?? null,
            [
                'name' => $paymentMethod->billing_name ?? null,
                'email' => $paymentMethod->billing_email ?? null,
                'phone' => $paymentMethod->billing_phone ?? null,
                'address' => [
                    'line1' => $paymentMethod->billing_address1 ?? null,
                    'line2' => $paymentMethod->billing_address2 ?? null,
                    'city' => $paymentMethod->billing_city ?? null,
                    'postal_code' => $paymentMethod->billing_state ?? null,
                    'state' => $paymentMethod->billing_postal ?? null,
                    'country' => $paymentMethod->billing_country ?? null,
                ],
            ],
        );

        if ($contribution && $contribution->totalamount > 0) {
            return $this->generatePaymentIntentResponse($contribution, $customer, $paymentMethod, $stripePaymentMethod ?? null);
        }

        return $this->generateSetupIntentResponse($paymentMethod, $customer);
    }

    private function updateBillingDetailsFromPaymentMethod(?StripePaymentMethod $stripePaymentMethod, ?Order $contribution, ?PaymentMethod $paymentMethod): void
    {
        if (empty($stripePaymentMethod)) {
            throw new GatewayException('Wallet pay requires a payment method.');
        }

        $this->updateContributionAndPaymentMethodFromStripePaymentMethod($stripePaymentMethod, $contribution, $paymentMethod);

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

    private function firstOrCreateCustomer(?string $customerId, ?string $customerEmail, ?string $currency, array $customerDetails): ?Customer
    {
        $customer = null;

        if ($customerId) {
            $customer ??= rescueQuietly(fn () => $this->getCustomer($customerId));
        }

        if ($customerEmail) {
            $customer ??= rescueQuietly(function () use ($customerEmail, $currency) {
                return $this->getCustomersForEmail($customerEmail)
                    ->filter(function (Customer $customer) use ($currency) {
                        return empty($customer->currency) || $customer->currency === strtolower($currency);
                    })->first();
            });
        }

        $customer ??= rescueQuietly(function () use ($customerDetails) {
            // don't create customer in Stripe unless we have an email this
            // will prevent flooding Stripe with a bunch of garbage customers
            if (empty($customerDetails['email'])) {
                return;
            }

            return $this->stripe->customers->create($customerDetails, $this->getApiResourceOptions());
        });

        return $customer;
    }

    private function generatePaymentIntentResponse(
        Order $contribution,
        ?Customer $customer,
        ?PaymentMethod $paymentMethod = null,
        ?StripePaymentMethod $stripePaymentMethod = null
    ): JsonResponse {
        $params = [
            'amount' => money($contribution->totalamount, $contribution->currency_code)->getAmountInSubunits(),
            'currency' => $contribution->currency_code,
            'description' => sprintf('Contribution: %s', $contribution->client_uuid),
        ];

        if ($customer) {
            $params['customer'] = $customer->id;
        }

        if ($this->usingStripeApplicationFeeBilling() && $contribution->dcc_total_amount > 0) {
            $params['application_fee_amount'] = $this->getApplicationFeeAmount(
                $contribution->totalamount,
                $contribution->dcc_total_amount,
                $contribution->currency_code,
            );
        }

        // You may only update the payment_method_types of a PaymentIntent with one of the following statuses:
        // requires_payment_method, requires_confirmation, requires_action.
        if (empty($contribution->stripe_payment_intent)) {
            $params['payment_method_types'] = ['card'];
        }

        if ($paymentMethod && isset($stripePaymentMethod->card->fingerprint)) {
            $originalPaymentMethod = app(PaymentMethodRepository::class)->getFingerprintMatch(
                $paymentMethod->member,
                $this->provider,
                $stripePaymentMethod->card->fingerprint,
            );

            if ($originalPaymentMethod) {
                $paymentMethod->status = 'DUPLICATE';
                $paymentMethod->save();

                $contribution->payment_method_id = $originalPaymentMethod->id;
                $contribution->save();
            }
        }

        // if the payment method is not a duplicate then we'll configure
        // the payment intent to setup the payment method for future usage
        if ($paymentMethod && empty($originalPaymentMethod)) {
            $params['setup_future_usage'] = 'off_session';
        }

        try {
            $paymentIntent = empty($contribution->stripe_payment_intent)
                ? $this->stripe->paymentIntents->create($params, $this->getApiResourceOptions())
                : $this->stripe->paymentIntents->update($contribution->stripe_payment_intent, $params, $this->getApiResourceOptions());
        } catch (ApiErrorException $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $contribution->stripe_payment_intent = $paymentIntent->id;
        $contribution->save();

        return new JsonResponse([
            'id' => $paymentIntent->id,
            'object' => 'payment_intent',
            'client_secret' => $paymentIntent->client_secret,
            'payment_method' => $originalPaymentMethod->token ?? $stripePaymentMethod->id ?? null,
        ]);
    }

    private function generateSetupIntentResponse(PaymentMethod $paymentMethod, ?Customer $customer): JsonResponse
    {
        $params = ['payment_method_types' => ['card']];

        if ($customer) {
            $params['customer'] = $customer->id;
        }

        try {
            $setupIntent = empty($paymentMethod->stripe_setup_intent)
                ? $this->stripe->setupIntents->create($params, $this->getApiResourceOptions())
                : $this->stripe->setupIntents->retrieve($paymentMethod->stripe_setup_intent, null, $this->getApiResourceOptions());
        } catch (ApiErrorException $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $paymentMethod->stripe_setup_intent = $setupIntent->id;
        $paymentMethod->save();

        return new JsonResponse([
            'id' => $setupIntent->id,
            'object' => 'setup_intent',
            'client_secret' => $setupIntent->client_secret,
        ]);
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
        if ($this->usingStripeV2()) {
            return $this->createSourceTokenV2($paymentMethod);
        }

        $res = $this->createTransactionResponse([]);

        $token = $this->request()->input('token');
        // $token = $options->contribution->stripe_payment_intent ?? $paymentMethod->stripe_setup_intent;

        if (Str::startsWith($token, 'pi_')) {
            $stripePaymentMethod = $this->validatePaymentIntentSucceeded($res, $token);
        } elseif (Str::startsWith($token, 'seti_')) {
            $stripePaymentMethod = $this->validateSetupIntentSucceeded($res, $token);
        } else {
            throw new InvalidArgumentException('Token required');
        }

        $paymentMethod->stripe_customer_id = $res->getCustomerRef() ?: null;
        $paymentMethod->save();

        $paymentMethod->member->stripe_customer_id = $res->getCustomerRef() ?: null;
        $paymentMethod->member->save();

        if ($res->getCardWallet()) {
            $this->updateContributionAndPaymentMethodFromStripePaymentMethod($stripePaymentMethod, $options->contribution ?? null, $paymentMethod);
        }

        return $res;
    }

    private function paymentIntentSuccessfulOrProcessing(PaymentIntent $paymentIntent): bool
    {
        return in_array($paymentIntent->status, [PaymentIntent::STATUS_SUCCEEDED, PaymentIntent::STATUS_PROCESSING], true);
    }

    private function validatePaymentIntentSucceeded(Stripe\TransactionResponse $res, string $paymentIntentId): StripePaymentMethod
    {
        try {
            $paymentIntent = $this->getPaymentIntent($paymentIntentId, ['expand' => ['payment_method.billing_details']]);
        } catch (Throwable $e) {
            throw new GatewayException(trans('payments.payment_was_not_successful'));
        }

        $charge = $paymentIntent->charges->data[0] ?? null;

        if (empty($charge)) {
            throw new GatewayException(trans('payments.payment_was_not_successful'));
        }

        $res->setCharge($charge);

        if ($paymentIntent->payment_method) {
            $res->setPaymentMethod($paymentIntent->payment_method);
        }

        if (! $this->paymentIntentSuccessfulOrProcessing($paymentIntent) || ! $res->isCompleted()) {
            throw new PaymentException($res);
        }

        $res->merge([
            'transaction_id' => $charge->id,
            'gateway_data' => $paymentIntent->toArray(),
        ]);

        return $paymentIntent->payment_method;
    }

    private function validateSetupIntentSucceeded(Stripe\TransactionResponse $res, string $setupIntentId): StripePaymentMethod
    {
        try {
            $setupIntent = $this->getSetupIntent($setupIntentId, ['expand' => ['payment_method.billing_details']]);
        } catch (Throwable $e) {
            throw new GatewayException(trans('payments.payment_was_not_successful'));
        }

        if ($setupIntent->status !== SetupIntent::STATUS_SUCCEEDED) {
            throw new GatewayException(trans('payments.payment_method_setup_not_successful'));
        }

        $res->setPaymentMethod($setupIntent->payment_method);

        $res->merge([
            'completed' => true,
            'transaction_id' => $setupIntent->payment_method->id,
            'gateway_data' => $setupIntent->toArray(),
        ]);

        return $setupIntent->payment_method;
    }

    private function updateContributionAndPaymentMethodFromStripePaymentMethod(StripePaymentMethod $stripePaymentMethod, ?Order $contribution, ?PaymentMethod $paymentMethod): void
    {
        if ($contribution) {
            $contribution->billing_first_name = Str::firstName($stripePaymentMethod->billing_details->name ?? null);
            $contribution->billing_last_name = Str::lastName($stripePaymentMethod->billing_details->name ?? null);
            $contribution->billingemail = $stripePaymentMethod->billing_details->email ?? null;
            $contribution->billingaddress1 = $stripePaymentMethod->billing_details->address->line1 ?? null;
            $contribution->billingaddress2 = $stripePaymentMethod->billing_details->address->line2 ?? null;
            $contribution->billingcity = $stripePaymentMethod->billing_details->address->city ?? null;
            $contribution->billingstate = $stripePaymentMethod->billing_details->address->state ?? null;
            $contribution->billingzip = $stripePaymentMethod->billing_details->address->postal_code ?? null;
            $contribution->billingcountry = $stripePaymentMethod->billing_details->address->country ?? null;
            $contribution->billingphone = $stripePaymentMethod->billing_details->phone ?? null;
            $contribution->save();
        }

        if ($paymentMethod) {
            $paymentMethod->billing_first_name = Str::firstName($stripePaymentMethod->billing_details->name ?? null);
            $paymentMethod->billing_last_name = Str::lastName($stripePaymentMethod->billing_details->name ?? null);
            $paymentMethod->billing_email = $stripePaymentMethod->billing_details->email ?? null;
            $paymentMethod->billing_address1 = $stripePaymentMethod->billing_details->address->line1 ?? null;
            $paymentMethod->billing_address2 = $stripePaymentMethod->billing_details->address->line2 ?? null;
            $paymentMethod->billing_city = $stripePaymentMethod->billing_details->address->city ?? null;
            $paymentMethod->billing_state = $stripePaymentMethod->billing_details->address->state ?? null;
            $paymentMethod->billing_postal = $stripePaymentMethod->billing_details->address->postal_code ?? null;
            $paymentMethod->billing_country = $stripePaymentMethod->billing_details->address->country ?? null;
            $paymentMethod->billing_phone = $stripePaymentMethod->billing_details->phone ?? null;
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
        $contribution = $options->contribution ?? null;

        $params = [
            'amount' => $amount->getAmountInSubunits(),
            'currency' => $amount->currency_code,
            'customer' => $paymentMethod->stripe_customer_id ?? $paymentMethod->member->stripe_customer_id ?? null,
            'payment_method' => $paymentMethod->token,
            'off_session' => $options->initiatedBy === CredentialOnFileInitiatedBy::MERCHANT,
            'confirm' => true,
        ];

        if ($this->usingStripeApplicationFeeBilling() && $options->dccAmount > 0) {
            $params['application_fee_amount'] = $this->getApplicationFeeAmount(
                $amount->getAmount(),
                $options->dccAmount,
                $amount->currency_code,
            );
        }

        try {
            if (isset($contribution->stripe_payment_intent)) {
                $paymentIntent = $this->getPaymentIntent($contribution->stripe_payment_intent);

                if ($this->paymentIntentSuccessfulOrProcessing($paymentIntent)) {
                    return $this->createTransactionResponse()->setCharge($paymentIntent->charges->data[0]);
                }
            }

            $res = empty($paymentIntent)
                ? $this->stripe->paymentIntents->create($params, $this->getApiResourceOptions())
                : $this->stripe->paymentIntents->update($paymentIntent->id, $params, $this->getApiResourceOptions());

            if ($res->charges->isEmpty()) {
                throw new GatewayException('Failure to create charge for payment intent.');
            }

            $res = $res->charges->data[0];
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse()->setCharge($res);
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
            'charge' => $transactionId,
        ];

        $charge = $this->getCharge($transactionId);

        if ($fullRefund === false) {
            $data['amount'] = money($amount, $charge->currency)->getAmountInSubunits();
        }

        if ($charge->application_fee_amount) {
            $data['refund_application_fee'] = true;
        }

        try {
            if ($charge->refunded) {
                $res = optional($charge->refunds)->first();
            }

            $res ??= $this->stripe->refunds->create($data, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse([
            'completed' => $res->status === 'succeeded',
            'response' => $res->status,
            'response_text' => $res->status,
            'transaction_id' => $res->id,
        ]);
    }

    public function updateStripeFeesForPayment(Payment $payment): void
    {
        $charge = rescueQuietly(fn () => $this->getCharge($payment->reference_number, ['expand' => ['balance_transaction']]));
        $stripeFee = collect($charge->balance_transaction->fee_details ?? null)->firstWhere('type', 'stripe_fee');

        if (empty($stripeFee)) {
            return;
        }

        $payment->stripe_fee_amount = money($stripeFee->amount, $stripeFee->currency, true)->getAmount();
        $payment->stripe_fee_exchange_rate = $charge->balance_transaction->exchange_rate;
        $payment->stripe_fee_currency_code = strtoupper($stripeFee->currency);
        $payment->save();
    }

    /**
     * Register an Apple Pay Merchant Domain.
     *
     * All domains, whether in production or testing, must be registered with a live secret key.
     *
     * Stripe Connect accounts created with a testkey can't be using with a live secret and
     * accounts not created with a testkey are always connected in livemode. As a result testing
     * Apple Pay via Stripe Connect isn't possible if not in livemode.
     *
     * To test Apple Pay not in livemode disable Stripe Connect and manually register the domains being
     * used in the Stripe Dashboard.
     *
     * @see https://developer.apple.com/documentation/apple_pay_on_the_web/maintaining_your_environment
     * @see https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
     */
    public function registerApplePayMerchantDomain(string $domain): bool
    {
        if ($this->isTestMode()) {
            return false;
        }

        try {
            $this->stripe->applePayDomains->create(['domain_name' => $domain], $this->getApiResourceOptions());
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function getAppleDeveloperMerchantIdDomainAssociationFile(): string
    {
        return (string) Http::get('https://stripe.com/files/apple-pay/apple-developer-merchantid-domain-association');
    }

    /**
     * Get a card.
     *
     * @param string $customerId
     * @param string $cardId
     * @return \Stripe\Card
     */
    public function getCard(string $customerId, string $cardId)
    {
        try {
            $res = $this->stripe->customers->retrieveSource($customerId, $cardId, null, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a payment method.
     *
     * @param string $paymentMethodId
     * @param array $params
     * @return \Stripe\PaymentMethod
     */
    public function getPaymentMethod(string $paymentMethodId, array $params = null)
    {
        try {
            $res = $this->stripe->paymentMethods->retrieve($paymentMethodId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a customer.
     *
     * @param string $customerId
     * @param array $params
     * @return \Stripe\Customer
     */
    public function getCustomer(string $customerId, array $params = null)
    {
        try {
            $res = $this->stripe->customers->retrieve($customerId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    public function getCustomersForEmail(?string $email): Collection
    {
        if (empty($email)) {
            return collect();
        }

        try {
            $customers = $this->stripe->customers->all(compact('email'), $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return collect(iterator_to_array($customers))->sortByDesc('created');
    }

    public function getPaymentIntent(string $paymentIntentId, array $params = null)
    {
        try {
            return $this->stripe->paymentIntents->retrieve($paymentIntentId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSetupIntent(string $setupIntentId, array $params = null)
    {
        try {
            return $this->stripe->setupIntents->retrieve($setupIntentId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get a customers payment methods.
     *
     * @param string $customerId
     * @return \Stripe\Collection<\Stripe\PaymentMethod>
     */
    public function getPaymentMethods(string $customerId)
    {
        try {
            $res = $this->stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => 'card',
            ], $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a charge.
     *
     * @param string $transactionId
     * @param array $params
     * @return \Stripe\Charge
     */
    public function getCharge(string $transactionId, array $params = null)
    {
        try {
            $res = $this->stripe->charges->retrieve($transactionId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get charges.
     *
     * @param array $params
     * @return \Stripe\Collection
     */
    public function getCharges(array $params = [])
    {
        $params = array_merge(['limit' => 100], $params);

        try {
            $res = $this->stripe->charges->all($params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a invoice.
     *
     * @param string $invoiceId
     * @param array $params
     * @return \Stripe\Invoice
     */
    public function getInvoice(string $invoiceId, array $params = null)
    {
        try {
            $res = $this->stripe->invoices->retrieve($invoiceId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get invoices.
     *
     * @param array $params
     * @return \Stripe\Collection
     */
    public function getInvoices(array $params = [])
    {
        $params = array_merge(['limit' => 100], $params);

        try {
            $res = $this->stripe->invoices->all($params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a subscription.
     *
     * @param string $subscriptionId
     * @param array $params
     * @return \Stripe\Subscription
     */
    public function getSubscription($subscriptionId, array $params = null)
    {
        try {
            $res = $this->stripe->subscriptions->retrieve($subscriptionId, $params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a subscription.
     *
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        try {
            $res = $this->getSubscription($subscriptionId)->cancel();
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get subscriptions.
     *
     * @param array $params
     * @return \Stripe\Collection
     */
    public function getSubscriptions(array $params = [])
    {
        $params = array_merge(['limit' => 100], $params);

        try {
            $res = $this->stripe->subscriptions->all($params, $this->getApiResourceOptions());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get a charge.
     *
     * @param string $transactionId
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function getTransaction(string $transactionId)
    {
        return $this->createTransactionResponse()->setCharge($this->getCharge($transactionId));
    }

    /**
     * Retrieve a verified Stripe Webhook event.
     *
     * @return \Stripe\Event
     */
    public function getWebhookEvent(): Event
    {
        $secret = $this->config('signing_secret');

        if (empty($secret)) {
            throw new MessageException('Stripe signing secret has not been configured.');
        }

        try {
            return Webhook::constructEvent(
                $this->request()->getContent(),
                $this->request()->header('Stripe-Signature'),
                $secret
            );
        } catch (Throwable $e) {
            throw new MessageException('Unable to verify Stripe payload.');
        }
    }

    /**
     * Create a transaction response.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Gateways\Stripe\TransactionResponse
     */
    protected function createTransactionResponse(array $data = []): Stripe\TransactionResponse
    {
        if (! array_key_exists('ip_address', $data)) {
            $data['ip_address'] = $this->getClientIp();
        }

        return new Stripe\TransactionResponse($this->provider(), $data);
    }

    public function getApplicationFeeAmount(float $amount, float $dccAmount, string $currencyCode): int
    {
        // stripe fees are calculated after converting the amount to the default currency
        // for the stripe account which means we need to do likewise when estimating

        // was are also not taking into account 0.6% for international cards and 2% for currecy conversions

        $percentage = 0.029;
        $fixedAmountInSubunits = money(0.3, $this->getDefaultCurrency())->getAmountInSubunits();

        $amountInSubunits = money($amount, $currencyCode)
            ->toCurrency($this->getDefaultCurrency())
            ->getAmountInSubunits();

        $dccAmountInSubunits = money($dccAmount, $currencyCode)
            ->toCurrency($this->getDefaultCurrency())
            ->getAmountInSubunits();

        $stripeFeeInSubunits = ceil($amountInSubunits * $percentage + $fixedAmountInSubunits);

        return money($dccAmountInSubunits - $stripeFeeInSubunits, $this->getDefaultCurrency(), true)
            ->toCurrency($currencyCode)
            ->getAmountInSubunits();
    }

    private function getPublishableKey(): string
    {
        return sys_get('use_stripe_connect')
            ? $this->config('credential4')
            : $this->config('publishable_key');
    }

    private function usingStripeApplicationFeeBilling(): bool
    {
        return sys_get('use_stripe_connect') && sys_get('dcc_stripe_application_fee_billing');
    }

    private function usingStripeV2(): bool
    {
        if ($this->configureForKioskApp) {
            return true;
        }

        if ($this->request()->wantsJson()) {
            return $this->request()->input('using_stripe_v2') || $this->request()->header('origin') === 'ionic://localhost';
        }

        return false;
    }

    public function getViewConfig(): ?object
    {
        $scripts = $this->usingStripeV2()
            ? ['https://js.stripe.com/v2/']
            : ['https://js.stripe.com/v3/'];

        // https://stripe.com/docs/stripe-js
        // https://stripe.com/docs/stripe-js/v2
        return (object) [
            'name' => $this->name(),
            'scripts' => $scripts,
            'settings' => [
                'stripeAccount' => sys_get('use_stripe_connect') ? $this->config('credential1') : null,
                'publishableKey' => $this->getPublishableKey(),
                'usingStripeV2' => $this->usingStripeV2(),
            ],
        ];
    }

    public function getView(): string
    {
        if (! $this->usingStripeV2()) {
            return parent::getView();
        }

        $key = $this->getPublishableKey();

        // https://stripe.com/docs/stripe-js/v2
        return <<<HTML
            <script>
            (function(i,s,o,g,r,a,m){
            m=function(){i[r].setPublishableKey('$key')};
            if(i[r])m();else{a=s.createElement(o);a.onload=m;a.src=g;s.head.appendChild(a);}
            })(window,document,'script','https://js.stripe.com/v2/','Stripe');
            </script>
            HTML;
    }
}
