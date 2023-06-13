<?php

namespace Ds\Domain\Commerce\Responses;

use BadMethodCallException;
use Carbon\Carbon;
use Ds\Domain\Commerce\ACH;
use Ds\Domain\Commerce\Contracts\GatewayResponse;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

/**
 * @method string isCompleted()
 * @method string getAchAccount()
 * @method string getAchEntity()
 * @method string getAchRouting()
 * @method string getAchType()
 * @method string getApplicationFeeAmount()
 * @method string getAVSCode()
 * @method string getCardEntryType()
 * @method string getCardNumber()
 * @method string getCardVerification()
 * @method string getCardWallet()
 * @method string getCustomerRef()
 * @method string getCVV2Code()
 * @method string getFingerprint()
 * @method string getIpAddress()
 * @method string getPendingReason()
 * @method string getResponse()
 * @method string getResponseText()
 * @method string getSourceToken()
 * @method string getStripePaymentIntent()
 * @method string getTokenType()
 * @method string getTransactionId()
 */
class TransactionResponse implements GatewayResponse
{
    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    protected $provider;

    /** @var array */
    protected $data = [];

    /** @var array */
    protected $forceCasts = [
        'ach_account' => 'string',
        'ach_entity' => 'string',
        'ach_routing' => 'string',
        'ach_type' => 'string',
        'application_fee_amount' => 'float',
        'avs_code' => 'string',
        'completed' => 'bool',
        'cc_entry_type' => 'string',
        'cc_number' => 'string',
        'cc_verification' => 'string',
        'customer_ref' => 'string',
        'cvv_code' => 'string',
        'ip_address' => 'string',
        'pending_reason' => 'string',
        'response' => 'string',
        'response_text' => 'string',
        'source_token' => 'string',
        'stripe_payment_intent' => 'string',
        'transaction_id' => 'string',
    ];

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     * @param array $data
     */
    public function __construct(PaymentProvider $provider, array $data)
    {
        $this->provider = $provider;
        $this->merge($data);
    }

    public function merge(array ...$data): self
    {
        // remove NULL keys from the arrays
        $data = array_map(fn ($a) => array_filter($a, fn ($i) => $i !== null), $data);

        $this->data = array_merge($this->data, ...$data);

        return $this;
    }

    /**
     * Get the payment provider.
     *
     * @return \Ds\Domain\Commerce\Models\PaymentProvider
     */
    public function getProvider(): PaymentProvider
    {
        return $this->provider;
    }

    /**
     * Get the card expiry.
     *
     * @return string
     */
    public function getCardExpiry(): string
    {
        if (empty($this->data['cc_exp_month']) || empty($this->data['cc_exp_year'])) {
            return (string) Arr::get($this->data, 'cc_exp');
        }

        return str_pad($this->data['cc_exp_month'] . substr($this->data['cc_exp_year'], -2, 2), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the card expiry month.
     *
     * @return string
     */
    public function getCardExpiryMonth(): string
    {
        try {
            $expiry = Carbon::createFromFormat('my-d', $this->getCardExpiry() . '-01');
        } catch (Throwable $e) {
            return '';
        }

        return (string) fromUtcFormat($expiry, 'm');
    }

    /**
     * Get the card expiry year.
     *
     * @return string
     */
    public function getCardExpiryYear(): string
    {
        try {
            $expiry = Carbon::createFromFormat('my-d', $this->getCardExpiry() . '-01');
        } catch (Throwable $e) {
            return '';
        }

        return (string) fromUtcFormat($expiry, 'Y');
    }

    /**
     * Get the ach bank name.
     *
     * @return string
     */
    public function getAchBank(): ?string
    {
        if (empty($this->data['ach_bank'])) {
            return ACH::getBankName($this->getAchRouting());
        }

        return (string) $this->data['ach_bank'];
    }

    /**
     * Get the account type.
     *
     * @return string
     */
    public function getAccountType(): string
    {
        $accountType = (string) Arr::get($this->data, 'account_type');

        if ($accountType) {
            switch ((string) Str::of($accountType)->lower()->replaceMatches('/[^a-z]/', '')) {
                case 'amex': return 'American Express';
                case 'americanexpress': return 'American Express';
                case 'dinersclub': return 'Diners Club';
                case 'discover': return 'Discover';
                case 'jcb': return 'JCB';
                case 'paypal': return 'PayPal';
                case 'maestro': return 'Maestro';
                case 'mastercard': return 'MasterCard';
                case 'venmo': return 'Venmo';
                case 'visa': return 'Visa';
                default: return $accountType;
            }
        }

        if ($this->getCardNumber()) {
            return ucwords(card_type_from_first_number($this->getCardNumber()));
        }

        if ($this->getAchType()) {
            return trim(ucwords($this->getAchEntity() . ' ' . $this->getAchType()));
        }

        if ($this->provider && $this->provider->provider_type === 'paypal') {
            return 'PayPal';
        }

        if ($this->provider) {
            return $this->provider->gateway->getDisplayName();
        }

        return '';
    }

    /**
     * Get the account last four.
     *
     * @return string
     */
    public function getAccountLastFour(): string
    {
        if ($this->getCardNumber()) {
            return substr($this->getCardNumber(), -4);
        }

        if ($this->getAchAccount()) {
            return substr($this->getAchAccount(), -4);
        }

        return substr($this->getSourceToken(), -4);
    }

    public function __call(string $name, array $arguments)
    {
        $key = (string) Str::of($name)
            ->replaceMatches('/^get/', '')
            ->replaceMatches('/^AVSCode/', 'AvsCode')
            ->replaceMatches('/^Card/', 'Cc')
            ->replaceMatches('/^CVV2Code/', 'CvvCode')
            ->replaceMatches('/^isCompleted/', 'completed')
            ->snake();

        if (isset($this->forceCasts[$key])) {
            $value = $this->data[$key] ?? null;
            settype($value, $this->forceCasts[$key]);

            return $value;
        }

        if (array_key_exists($key, $this->data) && empty($this->forceCasts[$key])) {
            return $this->data[$key];
        }

        if (Str::startsWith($name, 'get')) {
            return null;
        }

        throw new BadMethodCallException("Method $name does not exists.");
    }

    /**
     * Convert the response into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the response instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Convert the response instance to an array.
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return $this->toArray();
    }

    /**
     * Create a Transaction Response from a Payment Method.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public static function fromPaymentMethod(PaymentMethod $paymentMethod, array $data = []): TransactionResponse
    {
        $data = array_merge([
            'completed' => true,
            'response' => '1',
            'response_text' => $paymentMethod->ach_account_type ? 'PENDING' : 'APPROVED',
            'account_type' => $paymentMethod->account_type,
            'cc_number' => $paymentMethod->cc_expiry ? $paymentMethod->account_number : '',
            'cc_exp' => fromUtcFormat($paymentMethod->cc_expiry, 'my'),
            'cc_wallet' => $paymentMethod->cc_wallet,
            'ach_account' => $paymentMethod->cc_expiry ? '' : $paymentMethod->account_number,
            'ach_routing' => $paymentMethod->ach_routing,
            'ach_type' => $paymentMethod->ach_account_type,
            'ach_entity' => $paymentMethod->ach_entity_type,
            'fingerprint' => $paymentMethod->fingerprint,
            'token_type' => $paymentMethod->token_type,
            'source_token' => $paymentMethod->token,
        ], $data);

        return new TransactionResponse($paymentMethod->paymentProvider, $data);
    }
}
