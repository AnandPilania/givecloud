<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\OAuth;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\AccessTokenResponse;
use Ds\Domain\Commerce\Responses\RedirectToResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Throwable;

class GoCardlessGateway extends AbstractGateway implements
    Gateway,
    OAuth,
    SourceTokens,
    Refunds
{
    /** @var string */
    protected $configKey = 'gocardless';

    /** @var \OAuth2\Client */
    protected $oauth;

    /** @var \GoCardlessPro\Client */
    protected $client;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'gocardless';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'GoCardless';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://gocardless.com';
    }

    /**
     * Get the oauth client.
     *
     * @return \OAuth2\Client
     */
    protected function getOAuthClient()
    {
        if (! $this->oauth) {
            $this->oauth = new \OAuth2\Client(
                $this->config('client_id'),
                $this->config('client_secret')
            );
        }

        return $this->oauth;
    }

    /**
     * Get the client.
     *
     * @return \GoCardlessPro\Client
     */
    protected function getApiClient()
    {
        if (! $this->client) {
            $this->client = new \GoCardlessPro\Client([
                'access_token' => $this->config('credential2'),
                'environment' => $this->config('environment'),
            ]);
        }

        return $this->client;
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
        if ($this->config('test_mode')) {
            $url = 'https://connect-sandbox.gocardless.com/oauth/authorize';
        } else {
            $url = 'https://connect.gocardless.com/oauth/authorize';
        }

        $returnUrl = $this->config('return_url');

        return $this->getOAuthClient()->getAuthenticationUrl($url, $returnUrl, [
            'scope' => 'read_write',
            'initial_view' => 'login',
            'state' => $state,
        ]);
    }

    /**
     * Get the onboarding link for the GoCardless integration.
     *
     * @return string
     */
    public function getOnboardingUrl(): string
    {
        if ($this->config('test_mode')) {
            return 'https://verify-sandbox.gocardless.com';
        }

        return 'https://verify.gocardless.com';
    }

    /**
     * Get permanent access token which allows use of the API on behalf of the user.
     *
     * @param string|null $returnUrl
     * @return \Ds\Domain\Commerce\Responses\AccessTokenResponse
     */
    public function getAccessToken(?string $returnUrl = null): AccessTokenResponse
    {
        if ($this->config('test_mode')) {
            $url = 'https://connect-sandbox.gocardless.com/oauth/access_token';
        } else {
            $url = 'https://connect.gocardless.com/oauth/access_token';
        }

        $returnUrl = $this->config('return_url');

        $res = $this->getOAuthClient()->getAccessToken($url, 'authorization_code', [
            'code' => $this->request()->input('code'),
            'redirect_uri' => $returnUrl,
        ]);

        $errorMessage = $res['result']['error_description'] ?? $res['result']['error'] ?? null;

        if ($errorMessage) {
            throw new GatewayException($errorMessage);
        }

        return new AccessTokenResponse([
            'account_id' => Arr::get($res, 'result.organisation_id'),
            'access_token' => Arr::get($res, 'result.access_token'),
        ]);
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
            $creditor = $this->getApiClient()->creditors()->list()->records[0];
        } catch (Throwable $e) {
            return false;
        }

        return (bool) $creditor;
    }

    /**
     * Check user's verification status.
     *
     * @return string
     */
    public function getVerificationStatus(): string
    {
        try {
            $creditor = $this->getApiClient()->creditors()->list()->records[0];
        } catch (Throwable $e) {
            return 'unknown';
        }

        return $creditor->verification_status;
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
        try {
            $res = $this->getApiClient()->redirectFlows()->create([
                'params' => [
                    'scheme' => $this->getCurrencyScheme($paymentMethod),
                    'description' => 'Mandate for ' . sys_get('clientName'),
                    'session_token' => $this->request()->session()->getId(),
                    'success_redirect_url' => $returnUrl,
                    'prefilled_customer' => [
                        'given_name' => $paymentMethod->billing_first_name ?? '',
                        'family_name' => $paymentMethod->billing_last_name ?? '',
                        'email' => $paymentMethod->billing_email ?? '',
                        'address_line1' => $paymentMethod->billing_address1 ?? '',
                        'address_line2' => $paymentMethod->billing_address2 ?? '',
                        'city' => $paymentMethod->billing_city ?? '',
                        'postal_code' => $paymentMethod->billing_postal ?? '',
                        'country_code' => $paymentMethod->billing_country ?? '',
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return new RedirectToResponse($res->redirect_url);
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
        if (! $this->request()->has('redirect_flow_id')) {
            throw new InvalidArgumentException('Redirect flow is required');
        }

        try {
            $res = $this->getApiClient()->redirectFlows()->complete(
                $this->request()->input('redirect_flow_id'),
                [
                    'params' => [
                        'session_token' => $this->request()->session()->getId(),
                    ],
                ]
            );
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse([
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
            'source_token' => $res->links->mandate,
        ]);
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
        try {
            $res = $this->getApiClient()->payments()->create([
                'params' => [
                    'amount' => (int) bcmul($amount->amount, 100, 0),
                    'app_fee' => 0,
                    'currency' => $amount->currency_code,
                    'links' => [
                        'mandate' => $paymentMethod->token,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse([
            'completed' => true,
            'response' => $res->status === 'pending_submission' ? '1' : '2',
            'response_text' => $res->status,
            'transaction_id' => $res->id,
            'source_token' => $paymentMethod->token,
        ]);
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
        try {
            $res = $this->getApiClient()->refunds()->create([
                'params' => [
                    'amount' => (int) bcmul($amount, 100, 0),
                    'total_amount_confirmation' => (int) bcmul($amount, 100, 0),
                    'links' => [
                        'payment' => $transactionId,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createTransactionResponse([
            'completed' => true,
            'response' => $res->status === 'pending_submission' ? 'pending' : 'failed',
            'response_text' => $res->status,
            'transaction_id' => $res->id,
        ]);
    }

    /**
     * Get the currency scheme.
     *
     * @return string
     */
    protected function getCurrencyScheme($paymentMethod)
    {
        switch ($paymentMethod->currency_code) {
            case 'GBP': return 'bacs';
            case 'EUR': return 'autogiro';
            case 'SEK': return 'sepa_core';
        }

        throw new InvalidArgumentException('Currency must be one of [GBP, EUR, SEK]');
    }
}
