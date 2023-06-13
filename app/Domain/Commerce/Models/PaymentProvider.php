<?php

namespace Ds\Domain\Commerce\Models;

use Ds\Domain\Commerce\Contracts\ApplePayMerchantDomains as ApplePayMerchantDomainsContract;
use Ds\Domain\Commerce\Contracts\CaptureTokens as CaptureTokensContract;
use Ds\Domain\Commerce\Contracts\CredentialOnFile as CredentialOnFileContract;
use Ds\Domain\Commerce\Contracts\OAuth as OAuthContract;
use Ds\Domain\Commerce\Contracts\PartialRefunds as PartialRefundsContract;
use Ds\Domain\Commerce\Contracts\Refunds as RefundsContract;
use Ds\Domain\Commerce\Contracts\SourceTokens as SourceTokensContract;
use Ds\Domain\Commerce\Contracts\SyncablePaymentStatus as SyncablePaymentStatusContract;
use Ds\Domain\Commerce\GatewayFactory;
use Ds\Domain\Commerce\Gateways\StripeGateway;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Casts\AustinheapEncryption;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @template T
 * @mixin T
 */
class PaymentProvider extends Model implements CaptureTokensContract, SourceTokensContract, RefundsContract, Liquidable
{
    use HasFactory;
    use SoftDeletes;

    /** @var \Ds\Domain\Commerce\Contracts\Gateway */
    protected $gatewayInstance;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'credential1',
        'credential2',
        'credential3',
        'credential4',
        'config',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
        'credential1' => AustinheapEncryption::class,
        'credential2' => AustinheapEncryption::class,
        'credential3' => AustinheapEncryption::class,
        'credential4' => AustinheapEncryption::class,
        'show_payment_method' => 'boolean',
        'require_cvv' => 'boolean',
        'card_verification' => 'boolean',
        'deny_if_prepaid' => 'boolean',
        'is_ach_allowed' => 'boolean',
        'is_wallet_pay_allowed' => 'boolean',
        'duplicate_window' => 'int',
        'test_mode' => 'boolean',
        'config' => AustinheapEncryption::class . ':json',
        'transaction_cost' => 'double',
        'transaction_rate' => 'double',
    ];

    /** @var array */
    public static $creditCardProviders = [
        'givecloudtest',
        'authorizenet',
        'braintree',
        'caymangateway',
        'nmi',
        'paysafe',
        'safesave',
        'stripe',
        'vanco',
    ];

    /** @var array */
    public static $bankAccountProviders = [
        'givecloudtest',
        'authorizenet',
        'braintree',
        'gocardless',
        'nmi',
        'safesave',
        'vanco',
    ];

    /** @var array */
    public static $kioskProviders = [
        'givecloudtest',
        'authorizenet',
        'caymangateway',
        'nmi',
        'paysafe',
        'safesave',
        'stripe',
        'vanco',
    ];

    /** @var array */
    public static $walletPayProviders = [
        'braintree',
        'givecloudtest',
        'stripe',
    ];

    /**
     * Attribute Accessor: Currency Code
     *
     * @return string
     */
    public function getCurrencyCodeAttribute()
    {
        return strtoupper(sys_get('dpo_currency'));
    }

    /**
     * Attribute Accessor: Gateway
     *
     * @return \Ds\Domain\Commerce\Contracts\Gateway
     */
    public function getGatewayAttribute()
    {
        if (! $this->gatewayInstance) {
            $this->gatewayInstance = app(GatewayFactory::class)->make($this);
        }

        return $this->gatewayInstance;
    }

    /**
     * Attribute Accessor: Test Mode
     *
     * @return bool
     */
    public function getTestModeAttribute()
    {
        return $this->gateway->isTestMode();
    }

    /**
     * Scope: Enabled
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeEnabled($query)
    {
        $query->where('enabled', true);
    }

    /**
     * Scope: Enabled
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeProvider($query, $provider)
    {
        $query->where('provider', $provider);
    }

    /**
     * Check if gateway supports feature.
     *
     * @param string $key
     * @return bool
     */
    public function supports($key)
    {
        if ($key === 'apple_pay_merchant_domains') {
            return $this->gateway instanceof ApplePayMerchantDomainsContract;
        }

        if ($key === 'capture_tokens') {
            return $this->gateway instanceof CaptureTokensContract;
        }

        if ($key === 'credential_on_file') {
            return $this->gateway instanceof CredentialOnFileContract;
        }

        if ($key === 'oauth') {
            if ($this->gateway instanceof StripeGateway && ! sys_get('use_stripe_connect')) {
                return false;
            }

            return $this->gateway instanceof OAuthContract;
        }

        if ($key === 'refunds') {
            return $this->gateway instanceof RefundsContract;
        }

        if ($key === 'partial_refunds') {
            return $this->gateway instanceof PartialRefundsContract;
        }

        if ($key === 'syncable_payment_status') {
            return $this->gateway instanceof SyncablePaymentStatusContract;
        }

        if ($key === 'source_tokens') {
            return $this->gateway instanceof SourceTokensContract;
        }

        return false;
    }

    /**
     * Create an authentication URL.
     *
     * @param string|null $returnUrl
     * @return string
     */
    public function getAuthenticationUrl(?string $returnUrl = null): string
    {
        $state = sys_get('ds_account_name');

        if ($this->supports('oauth')) {
            /** @var \Ds\Domain\Commerce\Contracts\OAuth */
            $gateway = $this->gateway;

            return $gateway->getAuthenticationUrl($state, $returnUrl);
        }

        throw new \BadMethodCallException;
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
        if ($this->supports('capture_tokens')) {
            /** @var \Ds\Domain\Commerce\Contracts\CaptureTokens */
            $gateway = $this->gateway;

            return $gateway->getCaptureTokenUrl($order, $returnUrl, $cancelUrl);
        }

        throw new \BadMethodCallException;
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        if ($this->supports('capture_tokens')) {
            /** @var \Ds\Domain\Commerce\Contracts\CaptureTokens */
            $gateway = $this->gateway;

            return $gateway->chargeCaptureToken($order);
        }

        throw new \BadMethodCallException;
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
        if ($this->supports('source_tokens')) {
            /** @var \Ds\Domain\Commerce\Contracts\SourceTokens */
            $gateway = $this->gateway;

            return $gateway->getSourceTokenUrl($paymentMethod, $returnUrl, $cancelUrl, $options);
        }

        throw new \BadMethodCallException;
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
        if ($this->supports('source_tokens')) {
            /** @var \Ds\Domain\Commerce\Contracts\SourceTokens */
            $gateway = $this->gateway;

            return $gateway->createSourceToken($paymentMethod, $options);
        }

        throw new \BadMethodCallException;
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
        if ($this->supports('source_tokens')) {
            /** @var \Ds\Domain\Commerce\Contracts\SourceTokens */
            $gateway = $this->gateway;

            return $gateway->chargeSourceToken($paymentMethod, $amount, $options);
        }

        throw new \BadMethodCallException;
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
        if ($this->supports('refunds')) {
            /** @var \Ds\Domain\Commerce\Contracts\Refunds */
            $gateway = $this->gateway;

            return $gateway->refundCharge($transactionId, $amount, $fullRefund, $paymentMethod);
        }

        throw new \BadMethodCallException;
    }

    public function usingCredentialOnFile(): bool
    {
        if (! $this->supports('credential_on_file')) {
            return false;
        }

        return (bool) sys_get('use_credential_on_file_if_available');
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->provider && method_exists($this->gateway, $method)) {
            return $this->gateway->{$method}(...$parameters);
        }

        return parent::__call($method, $parameters);
    }

    public static function shouldUseTestmodeProvider(): bool
    {
        return auth()->user() && user()->getTestmodeToken() === request('testmode_token');
    }

    /**
     * Get the active credit card gateway.
     *
     * @param bool $fail
     * @return self|null
     */
    public static function getCreditCardProvider($fail = true)
    {
        if (self::shouldUseTestmodeProvider()) {
            $provider = self::where('provider', 'givecloudtest');

            return $fail ? $provider->firstOrFail() : $provider->first();
        }

        $provider = self::query()
            ->where('enabled', true)
            ->orderBy('provider', 'asc');

        $creditCardProvider = sys_get('credit_card_provider');

        if (empty($creditCardProvider)) {
            $provider->whereIn('provider', self::$creditCardProviders);
        } else {
            $provider->where('provider', $creditCardProvider);
        }

        return $fail ? $provider->firstOrFail() : $provider->first();
    }

    /**
     * Get the active bank account gateway.
     *
     * @param bool $fail
     * @return self|null
     */
    public static function getBankAccountProvider($fail = true)
    {
        if (self::shouldUseTestmodeProvider()) {
            $provider = self::query()
                ->where('provider', 'givecloudtest')
                ->where('is_ach_allowed', true);

            return $fail ? $provider->firstOrFail() : $provider->first();
        }

        $provider = self::query()
            ->where('enabled', true)
            ->where('is_ach_allowed', true)
            ->orderBy('provider', 'asc');

        $bankAccountProvider = sys_get('bank_account_provider');

        if (empty($bankAccountProvider)) {
            $provider->whereIn('provider', self::$bankAccountProviders);
        } else {
            $provider->where('provider', $bankAccountProvider);
        }

        return $fail ? $provider->firstOrFail() : $provider->first();
    }

    /**
     * Get the possible bank account gateways.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getBankAccountProviders()
    {
        return self::query()
            ->where('enabled', true)
            ->where('is_ach_allowed', true)
            ->whereIn('provider', static::$bankAccountProviders)
            ->orderBy('provider', 'asc')
            ->pluck('provider')
            ->unique();
    }

    /**
     * Get the active kiosk gateway.
     *
     * @param bool $fail
     * @return self|null
     */
    public static function getKioskProvider($fail = true)
    {
        $provider = self::query()
            ->where('enabled', true)
            ->when(
                sys_get('kiosk_provider') ?: sys_get('credit_card_provider'),
                fn ($query, $provider) => $query->where('provider', $provider)
            )->whereIn('provider', static::$kioskProviders)
            ->orderBy('provider', 'asc');

        return $fail ? $provider->firstOrFail() : $provider->first();
    }

    /**
     * Get the offline provider id.
     *
     * @return int
     */
    public static function getOfflineProviderId()
    {
        $provider = static::query()
            ->where('enabled', true)
            ->where('provider_type', 'offline')
            ->orderBy('provider', 'asc')
            ->first();

        if (empty($provider)) {
            $provider = static::create([
                'enabled' => true,
                'display_name' => 'Offline Payments',
                'provider' => 'offline',
                'provider_type' => 'offline',
                'duplicate_window' => 0,
            ]);
        }

        return $provider->id;
    }

    /**
     * Get the active paypal gateway.
     *
     * @param bool $fail
     * @return self|null
     */
    public static function getPayPalProvider($fail = true)
    {
        if (self::shouldUseTestmodeProvider()) {
            return $fail ? throw_if(true, new ModelNotFoundException) : null;
        }

        $provider = self::query()
            ->where('enabled', true)
            ->whereIn('provider_type', ['paypal'])
            ->orderBy('provider', 'asc')
            ->first();

        if ($provider && $provider->credential1) {
            return $provider;
        }

        if ($fail) {
            throw (new ModelNotFoundException)->setModel(self::class);
        }
    }

    /**
     * Get the active wallet pay gateway.
     *
     * @param bool $fail
     * @return object
     */
    public static function getWalletPayProvider($fail = true)
    {
        if (self::shouldUseTestmodeProvider()) {
            $provider = self::query()
                ->where('provider', 'givecloudtest')
                ->where('is_wallet_pay_allowed', true);

            return $fail ? $provider->firstOrFail() : $provider->first();
        }

        $creditCardProvider = self::getCreditCardProvider($fail);
        $walletPayProviderName = sys_get('wallet_pay_provider') ?: optional($creditCardProvider)->provider;

        // for provider without wallet pay support attempt to use stripe
        if (! in_array($walletPayProviderName, static::$walletPayProviders, true)) {
            $walletPayProviderName = 'stripe';
        }

        $provider = self::query()
            ->where('enabled', true)
            ->where('is_wallet_pay_allowed', true)
            ->where('provider', $walletPayProviderName)
            ->whereIn('provider', self::$walletPayProviders)
            ->orderBy('provider', 'asc');

        return $fail ? $provider->firstOrFail() : $provider->first();
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PaymentProvider');
    }
}
