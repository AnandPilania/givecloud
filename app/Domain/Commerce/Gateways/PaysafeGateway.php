<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentFailedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentRefundedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentSucceededAction;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\CredentialOnFile;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\SyncablePaymentStatus;
use Ds\Domain\Commerce\Contracts\Viewable;
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
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Paysafe\CardPayments\Authorization as PaysafeAuthorization;
use Paysafe\CardPayments\Refund as PaysafeRefund;
use Paysafe\CardPayments\Settlement;
use Paysafe\CardPayments\Verification as PaysafeVerification;
use Paysafe\CustomerVault\ACHBankaccounts as PaysafeACH;
use Paysafe\CustomerVault\Card as PaysafeCard;
use Paysafe\CustomerVault\EFTBankaccounts as PaysafeEFT;
use Paysafe\CustomerVault\Profile as PaysafeProfile;
use Paysafe\DirectDebit\Purchase as PaysafePurchase;
use Paysafe\Environment as PaysafeEnvironment;
use Paysafe\PaysafeApiClient;
use Paysafe\Request as PaysafeRequest;
use Paysafe\RequestDeclinedException as PaysafeRequestDeclinedException;
use Throwable;

class PaysafeGateway extends AbstractGateway implements
    Gateway,
    CaptureTokens,
    SourceTokens,
    Refunds,
    PartialRefunds,
    CredentialOnFile,
    SyncablePaymentStatus,
    Viewable
{
    /** @var \Paysafe\PaysafeApiClient */
    protected $client;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'paysafe';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Paysafe';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.paysafe.com';
    }

    /**
     * Get the api client.
     *
     * @return \Paysafe\PaysafeApiClient
     */
    protected function getClient()
    {
        if (! $this->client) {
            $this->client = new PaysafeApiClient(
                $this->config('api_key_user'),
                $this->config('api_key_pass'),
                $this->config('test_mode') ? PaysafeEnvironment::TEST : PaysafeEnvironment::LIVE,
                $this->config('account_number')
            );

            // this is just a temporary fix. refundCharge needs to be refactored set
            // the account based on the currency being refunded
            $this->setAccount($this->config('currency'));
        }

        return $this->client;
    }

    /**
     * Get all the configured card accounts keyed by currency code.
     */
    protected function getCardAccounts(): array
    {
        $accounts = $this->config('accounts') ?: [];

        // check old single currency config as a fallback
        if (! array_key_exists($this->config('currency'), $accounts)) {
            $accounts[$this->config('currency')] = $this->config('account_number');
        }

        return array_map(fn ($accountId) => (int) $accountId, $accounts);
    }

    /**
     * Set the account based on a currency code.
     *
     * @param string $currencyCode
     * @param string $paymentMethod
     */
    protected function setAccount($currencyCode, $paymentMethod = 'cards')
    {
        $accountId = Arr::get($this->getCardAccounts(), $currencyCode);

        if ($paymentMethod === 'directdebit') {
            if ($currencyCode === 'CAD') {
                $accountId = $this->config('eft_account_number');

                if (empty($accountId)) {
                    throw new GatewayException('EFT account not configured');
                }
            } elseif ($currencyCode === 'USD') {
                $accountId = $this->config('ach_account_number');

                if (empty($accountId)) {
                    throw new GatewayException('ACH account not configured');
                }
            } else {
                throw new GatewayException("Support for $currencyCode DirectDebit is not available");
            }
        } elseif ($paymentMethod === 'interac') {
            if ($currencyCode === 'CAD') {
                $accountId = $this->config('interac_account_number');

                if (empty($accountId)) {
                    throw new GatewayException('INTERAC account not configured');
                }
            } else {
                throw new GatewayException('INTERAC support is only available for CAD');
            }
        }

        if (empty($accountId)) {
            throw new GatewayException("$currencyCode not supported");
        }

        $this->getClient()->setAccount($accountId);
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
        return new ErrorResponse('Use Paysafe.js to obtain a payment token');
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        // verify that the request contains a single-use token
        $token = $this->request()->input('token');

        if (! $token) {
            throw new InvalidArgumentException('Token required');
        }

        if (isset($token['token'])) {
            $paymentToken = $token['token'];
            $tokenType = $token['payment_method'] ?? 'cards';
        } else {
            $paymentToken = $token;
            $tokenType = 'cards';
        }

        // verify that the request contains a valid payment method
        if (! in_array($tokenType, ['cards', 'directdebit', 'interac'])) {
            throw new InvalidArgumentException('Unsupported payment method');
        }

        $merchantRefNum = $order->client_uuid;

        if ($order->auth_attempts > 1) {
            $merchantRefNum .= '_' . str_pad($order->auth_attempts, 3, '0', STR_PAD_LEFT);
        }

        $data = [
            'merchantRefNum' => $merchantRefNum,
            'amount' => (int) bcmul($order->totalamount, 100, 0),
            'currencyCode' => $order->currency_code,
            'customerIp' => $this->request()->ip(),
        ];

        $this->setAccount($data['currencyCode'], $tokenType);

        if ($tokenType === 'cards') {
            $data = array_merge($data, [
                'settleWithAuth' => true,
                'card' => [
                    'paymentToken' => $paymentToken,
                ],
                'profile' => [
                    'firstName' => $order->billing_first_name,
                    'lastName' => $order->billing_last_name,
                    'email' => $order->billingemail,
                ],
                'billingDetails' => [
                    'street' => $order->billingaddress1,
                    'city' => $order->billingcity,
                    'state' => $order->billingstate,
                    'zip' => $order->billingzip,
                    'country' => $order->billingcountry,
                ],
            ]);

            return $this->authorizeAuthorization($data);
        }

        if ($tokenType === 'directdebit') {
            if ($this->config('currency') === 'CAD') {
                $data['eft'] = [
                    'paymentToken' => $paymentToken,
                ];
            } else {
                $data['ach'] = [
                    'paymentToken' => $paymentToken,
                    'payMethod' => 'WEB',
                ];
            }
        } elseif ($tokenType === 'interac') {
            $data = array_merge($data, [
                'settleWithAuth' => false,
                'returnLinks' => [[
                    'ref' => 'default',
                    'href' => null,
                ]],
                'paymentType' => 'INTERAC',
                'paymentToken' => $paymentToken,
            ]);
        }

        return $this->submitPurchase($data);
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
        return new ErrorResponse('Use Paysafe.js to obtain a payment token');
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
        // verfiy that the payment method is linked to an account
        if (! $paymentMethod->member) {
            throw new GatewayException('Account required to setup payment method');
        }

        // create paysafe profile for account if one hasn't been created yet
        if (! $paymentMethod->member->paysafe_profile_id) {
            try {
                $profile = $this->getClient()
                    ->customerVaultService()
                    ->createProfile(new PaysafeProfile([
                        'merchantCustomerId' => $paymentMethod->member->id,
                        'locale' => 'en_US',
                        'firstName' => $paymentMethod->billing_first_name,
                        'lastName' => $paymentMethod->billing_last_name,
                        'email' => $paymentMethod->billing_email,
                        'phone' => $paymentMethod->billing_phone,
                    ]));
            } catch (Throwable $e) {
                throw new GatewayException($e->getMessage(), $e->getCode(), $e);
            }

            $paymentMethod->member->paysafe_profile_id = $profile->id;
            $paymentMethod->member->save();
        }

        // verify that the request contains a single-use token
        $token = $this->request()->input('token');

        if (! $token) {
            throw new GatewayException('Token required');
        }

        if (isset($token['token'])) {
            $paymentToken = $token['token'];
            $tokenType = $token['payment_method'] ?? 'cards';
        } else {
            $paymentToken = $token;
            $tokenType = 'cards';
        }

        // verify that the request contains a valid payment method
        if (! in_array($tokenType, ['cards', 'directdebit', 'interac'])) {
            throw new InvalidArgumentException('Unsupported payment method');
        }

        if ($tokenType === 'cards') {
            $endpoint = 'cards';

            // When using Customer Vault API to convert a single-use payment token into a permanent payment
            // token, we need to verify the token using this Card Payments API request before doing the conversion.
            // https://developer.paysafe.com/en/cards/api/#/reference/0/verifications/verification?console=1
            if ($paymentMethod->credential_on_file) {
                try {
                    $res = $this->verifyVerification($paymentToken, $paymentMethod);

                    $paymentMethod->initial_transaction_id = $res->getTransactionId();
                    $paymentMethod->save();
                } catch (Throwable $e) {
                    throw new GatewayException($e->getMessage(), $e->getCode(), $e);
                }
            }
        } elseif ($tokenType === 'directdebit') {
            if ($this->config('currency') === 'CAD') {
                $endpoint = 'eftbankaccounts';
            } else {
                $endpoint = 'achbankaccounts';
            }
        } elseif ($tokenType === 'interac') {
            throw new GatewayException('Interac is not supported for recurring billing');
        }

        // Create a card using a single-use token
        //
        //    $card = $this->getClient()
        //        ->customerVaultService()
        //        ->createCardFromSingleUseToken(new PaysafeCard([
        //            'profileID'        => $paymentMethod->member->paysafe_profile_id,
        //            'singleUseToken'   => $paymentToken,
        //        ]));
        //
        // NOTE: using PaysafeCard as JSONObject for all token types since the
        // objects for ACH/EFT are missing the singleUseToken param
        try {
            $req = new PaysafeRequest([
                'method' => PaysafeRequest::POST,
                'uri' => "customervault/v1/profiles/{$paymentMethod->member->paysafe_profile_id}/{$endpoint}",
                'body' => new PaysafeCard([
                    'profileID' => $paymentMethod->member->paysafe_profile_id,
                    'singleUseToken' => $paymentToken,
                ]),
            ]);

            /** @var array */
            $res = $this->getClient()->processRequest($req);
            $res['profileID'] = $paymentMethod->member->paysafe_profile_id;

            if ($tokenType === 'cards') {
                $res = new PaysafeCard($res);
            } elseif ($tokenType === 'directdebit') {
                if ($this->config('currency') === 'CAD') {
                    $res = new PaysafeEFT($res);
                } else {
                    $res = new PaysafeACH($res);
                }
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();

            // if card already exists on the profile the extract the card
            // ID from the error and retrieve card details
            if (Str::startsWith($error, 'Card number already in use')) {
                try {
                    $cardId = Str::after($error, 'Card number already in use - ');
                    $profileId = $paymentMethod->member->paysafe_profile_id;

                    $req = new PaysafeRequest([
                        'method' => PaysafeRequest::PUT,
                        'uri' => "customervault/v1/profiles/$profileId/cards/$cardId",
                        'body' => new PaysafeCard([
                            'profileID' => $profileId,
                            'singleUseToken' => $paymentToken,
                        ]),
                    ]);

                    /** @var array */
                    $res = $this->getClient()->processRequest($req);
                    $res['profileID'] = $profileId;

                    $res = new PaysafeCard($res);
                } catch (Throwable $e) {
                    throw new GatewayException($e->getMessage(), $e->getCode(), $e);
                }
            } else {
                throw new GatewayException($error, $e->getCode(), $e);
            }
        }

        if ($tokenType === 'cards') {
            if (isset($res->cardExpiry)) {
                $cardExpiry = str_pad($res->cardExpiry->month, 2, '0', STR_PAD_LEFT);
                $cardExpiry .= substr($res->cardExpiry->year, 2, 2);
            } else {
                $cardExpiry = '';
            }

            switch ($res->cardType) {
                case 'AM': $cardNumber = "3***********{$res->lastDigits}"; break;
                case 'DC': $cardNumber = "6***********{$res->lastDigits}"; break;
                case 'MC': $cardNumber = "5***********{$res->lastDigits}"; break;
                case 'VI': $cardNumber = "4***********{$res->lastDigits}"; break;
                case 'VD': $cardNumber = "4***********{$res->lastDigits}"; break;
                case 'VE': $cardNumber = "4***********{$res->lastDigits}"; break;
                default:   $cardNumber = "************{$res->lastDigits}";
            }

            $res = $this->createTransactionResponse([
                'completed' => $res->status === 'ACTIVE',
                'response' => $res->status === 'ACTIVE' ? '1' : '2',
                'transaction_id' => (string) data_get($res, 'id'),
                'cc_number' => $cardNumber,
                'cc_exp' => $cardExpiry,
                'token_type' => $tokenType,
                'source_token' => data_get($res, 'paymentToken'),
            ]);
        } elseif ($tokenType === 'directdebit') {
            if ($this->config('currency') === 'CAD') {
                $routingNumber = data_get($res, 'institutionId') . data_get($res, 'transitNumber');
                $accountType = 'checking';
            } else {
                $routingNumber = data_get($res, 'routingNumber');
                $accountType = strtolower(data_get($res, 'accountType'));
            }

            $res = $this->createTransactionResponse([
                'completed' => $res->status === 'ACTIVE',
                'response' => $res->status === 'ACTIVE' ? '1' : '2',
                'transaction_id' => (string) data_get($res, 'id'),
                'ach_account' => (string) data_get($res, 'accountNumber', data_get($res, 'lastDigits')),
                'ach_routing' => $routingNumber,
                'ach_type' => $accountType,
                'ach_entity' => 'personal',
                'token_type' => $tokenType,
                'source_token' => data_get($res, 'paymentToken'),
            ]);
        } else {
            throw new GatewayException('Unsupported tokenType');
        }

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
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
            'merchantRefNum' => Str::uuid64(),
            'amount' => (int) bcmul($amount->amount, 100, 0),
            'currencyCode' => $amount->currency_code,
        ];

        $this->setAccount($data['currencyCode'], $paymentMethod->token_type);

        if ($paymentMethod->token_type === 'directdebit') {
            if ($data['currencyCode'] === 'CAD') {
                $data['eft'] = [
                    'paymentToken' => $paymentMethod->token,
                ];
            } else {
                $data['ach'] = [
                    'paymentToken' => $paymentMethod->token,
                    'payMethod' => 'WEB',
                ];
            }

            return $this->submitPurchase($data);
        }

        if ($paymentMethod->credential_on_file) {
            $data['storedCredential'] = array_filter([
                'type' => $options->initiatedBy === CredentialOnFileInitiatedBy::MERCHANT ? 'TOPUP' : 'ADHOC',
                'occurrence' => empty($paymentMethod->initial_transaction_id) ? 'INITIAL' : 'SUBSEQUENT',
                'initialTransactionId' => $paymentMethod->initial_transaction_id,
            ]);
        }

        $data = array_merge($data, [
            'settleWithAuth' => true,
            'card' => [
                'paymentToken' => $paymentMethod->token,
            ],
            'profile' => [
                'firstName' => $paymentMethod->billing_first_name,
                'lastName' => $paymentMethod->billing_last_name,
                'email' => $paymentMethod->billing_email,
            ],
            'billingDetails' => [
                'street' => $paymentMethod->billing_address1,
                'city' => $paymentMethod->billing_city,
                'state' => $paymentMethod->billing_state,
                'zip' => $paymentMethod->billing_postal,
                'country' => $paymentMethod->billing_country,
            ],
        ]);

        $this->setAccount($data['currencyCode']);

        $res = $this->authorizeAuthorization($data);

        if ($paymentMethod->credential_on_file && empty($paymentMethod->initial_transaction_id)) {
            $paymentMethod->initial_transaction_id = $res->getTransactionId();
            $paymentMethod->save();
        }

        return $res;
    }

    /**
     * Verify a verification.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function verifyVerification(string $paymentToken, PaymentMethod $paymentMethod): TransactionResponse
    {
        $data = [
            'merchantRefNum' => Str::uuid64(),
            'currencyCode' => $paymentMethod->currency_code,
            'card' => [
                'paymentToken' => $paymentToken,
            ],
            'storedCredential' => [
                'type' => 'ADHOC',
                'occurrence' => 'INITIAL',
            ],
            'profile' => [
                'firstName' => $paymentMethod->billing_first_name,
                'lastName' => $paymentMethod->billing_last_name,
                'email' => $paymentMethod->billing_email,
            ],
            'billingDetails' => [
                'street' => $paymentMethod->billing_address1,
                'city' => $paymentMethod->billing_city,
                'state' => $paymentMethod->billing_state,
                'zip' => $paymentMethod->billing_postal,
                'country' => $paymentMethod->billing_country,
            ],
            'customerIp' => $this->request()->ip(),
        ];

        $this->setAccount($data['currencyCode']);

        try {
            $res = $this->getClient()->cardPaymentService()->verify(new PaysafeVerification($data));
        } catch (PaysafeRequestDeclinedException $e) {
            $res = $this->createTransactionResponse([
                'completed' => false,
                'response' => '2',
                'response_text' => $e->getMessage(),
                'transaction_id' => $e->rawResponse['id'] ?? null,
            ]);

            throw new PaymentException($res);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $res = $this->createTransactionResponse([
            'completed' => $res->status === 'COMPLETED',
            'response' => $res->status === 'COMPLETED' ? '1' : '2',
            'response_text' => (string) data_get($res, 'status'),
            'avs_code' => (string) data_get($res, 'avsResponse'),
            'cvv_code' => (string) data_get($res, 'cvvVerification'),
            'transaction_id' => (string) data_get($res, 'id'),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    /**
     * Authorize an authorization.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function authorizeAuthorization(array $data): TransactionResponse
    {
        $authorization = new PaysafeAuthorization($data);

        try {
            $res = $this->getClient()->cardPaymentService()->authorize($authorization);
        } catch (PaysafeRequestDeclinedException $e) {
            $res = $this->createTransactionResponse([
                'completed' => false,
                'response' => '2',
                'response_text' => $e->getMessage(),
                'transaction_id' => $e->rawResponse['id'] ?? null,
            ]);

            throw new PaymentException($res);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        if (isset($res->card->cardExpiry)) {
            $cardExpiry = str_pad($res->card->cardExpiry->month, 2, '0', STR_PAD_LEFT);
            $cardExpiry .= substr($res->card->cardExpiry->year, 2, 2);
        } else {
            $cardExpiry = '';
        }

        switch (data_get($res, 'card.type')) {
            case 'AM': $cardNumber = '3***********' . data_get($res, 'card.lastDigits'); break;
            case 'DC': $cardNumber = '6***********' . data_get($res, 'card.lastDigits'); break;
            case 'MC': $cardNumber = '5***********' . data_get($res, 'card.lastDigits'); break;
            case 'VI': $cardNumber = '4***********' . data_get($res, 'card.lastDigits'); break;
            case 'VD': $cardNumber = '4***********' . data_get($res, 'card.lastDigits'); break;
            case 'VE': $cardNumber = '4***********' . data_get($res, 'card.lastDigits'); break;
            default:   $cardNumber = '************' . data_get($res, 'card.lastDigits');
        }

        $res = $this->createTransactionResponse([
            'completed' => $res->status === 'COMPLETED',
            'response' => $res->status === 'COMPLETED' ? '1' : '2',
            'response_text' => (string) data_get($res, 'status'),
            'avs_code' => (string) data_get($res, 'avsResponse'),
            'cvv_code' => (string) data_get($res, 'cvvVerification'),
            'transaction_id' => (string) data_get($res, 'id'),
            'cc_number' => $cardNumber,
            'cc_exp' => $cardExpiry,
            'source_token' => data_get($data, 'card.paymentToken'),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
    }

    /**
     * Submit a purchase
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function submitPurchase(array $data): TransactionResponse
    {
        $purchase = new PaysafePurchase($data);

        try {
            $res = $this->getClient()->directDebitService()->submit($purchase);
        } catch (PaysafeRequestDeclinedException $e) {
            $res = $this->createTransactionResponse([
                'completed' => false,
                'response' => '2',
                'response_text' => $e->getMessage(),
                'transaction_id' => $e->rawResponse['id'] ?? null,
            ]);

            throw new PaymentException($res);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $transactionData = [
            'completed' => $res->status === 'COMPLETED',
            'response' => $res->status === 'COMPLETED' ? '1' : '2',
            'response_text' => (string) data_get($res, 'status'),
            'transaction_id' => (string) data_get($res, 'id'),
            'ach_entity' => 'personal',
        ];

        if (array_key_exists('eft', $data)) {
            $transactionData['ach_account'] = (string) data_get($res, 'eft.accountNumber', data_get($res, 'eft.lastDigits'));
            $transactionData['ach_routing'] = data_get($res, 'eft.institutionId') . data_get($res, 'eft.transitNumber');
            $transactionData['ach_type'] = 'checking';
            $transactionData['source_token'] = data_get($data, 'eft.paymentToken');
        } else {
            $transactionData['ach_account'] = (string) data_get($res, 'ach.accountNumber', data_get($res, 'ach.lastDigits'));
            $transactionData['ach_routing'] = (string) data_get($res, 'ach.routingNumber');
            $transactionData['ach_type'] = strtolower(data_get($res, 'ach.accountType'));
            $transactionData['source_token'] = data_get($data, 'ach.paymentToken');
        }

        $res = $this->createTransactionResponse($transactionData);

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
        $payment = Payment::query()
            ->where('reference_number', $transactionId)
            ->where('gateway_type', $this->name())
            ->first();

        $this->setAccount($payment->currency, 'cards');

        $data = [
            'merchantRefNum' => Str::uuid64(),
            'settlementID' => $transactionId,
        ];

        if (! $fullRefund) {
            $data['amount'] = (int) bcmul($amount, 100, 0);
        }

        try {
            $refund = new PaysafeRefund($data);
            $res = $this->getClient()->cardPaymentService()->refund($refund);
        } catch (PaysafeRequestDeclinedException $e) {
            try {
                $settlement = new Settlement(['id' => $transactionId]);
                $res = $this->getClient()->cardPaymentService()->cancelSettlement($settlement);
            } catch (Throwable $e) {
                throw new GatewayException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        switch ($res->status) {
            case 'COMPLETED':  $status = 'succeeded'; break;
            case 'CANCELLED':  $status = 'succeeded'; break;
            case 'PENDING':    $status = 'pending'; break;
            case 'RECEIVED':   $status = 'pending'; break;
            case 'PROCESSING': $status = 'pending'; break;
            default:           $status = 'failed';
        }

        $res = $this->createTransactionResponse([
            'completed' => ($status === 'succeeded' || $status === 'pending'),
            'response' => $status,
            'response_text' => (string) data_coalesce($res, [
                'error.message',
                'acquirerResponse.responseCode',
                'status',
            ]),
            'transaction_id' => (string) data_get($res, 'id'),
            'gateway_data' => $res->jsonSerialize(),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new RefundException($res);
    }

    /**
     * Get a profile.
     *
     * @param string $profileId
     * @return \Paysafe\CustomerVault\Profile
     */
    public function getProfile(string $profileId)
    {
        return $this->getClient()
            ->customerVaultService()
            ->getProfile(new PaysafeProfile([
                'id' => $profileId,
            ]), false, true, true, true);
    }

    /**
     * Get a card.
     *
     * @param string $profileId
     * @param string $cardId
     * @return \Paysafe\CustomerVault\Card
     */
    public function getCard(string $profileId, string $cardId)
    {
        return $this->getClient()
            ->customerVaultService()
            ->getCard(new PaysafeCard([
                'id' => $cardId,
                'profileID' => $profileId,
            ]));
    }

    /**
     * Get a settlement.
     *
     * @param string $currencyCode
     * @param string $settlementId
     * @param string $tokenType
     * @return \Paysafe\CardPayments\Settlement
     */
    public function getSettlement(string $currencyCode, string $settlementId, string $tokenType = 'cards')
    {
        $this->setAccount($currencyCode, $tokenType);

        if ($tokenType === 'directdebit') {
            return $this->getClient()
                ->directDebitService()
                ->getPurchase(new PaysafePurchase([
                    'id' => $settlementId,
                ]));
        }

        return $this->getClient()
            ->cardPaymentService()
            ->getSettlement(new Settlement([
                'id' => $settlementId,
            ]));
    }

    public function syncPaymentStatus(Payment $payment): void
    {
        if (empty($payment->reference_number)) {
            return;
        }

        $transaction = $this->getSettlement(
            $payment->currency,
            $payment->reference_number,
            $payment->type === 'bank' ? 'directdebit' : 'cards'
        );

        if (! $transaction) {
            return;
        }

        if ($transaction->status === 'COMPLETED') {
            app(MarkPaymentSucceededAction::class)->execute($payment);
        } elseif ($transaction->status === 'FAILED') {
            app(MarkPaymentFailedAction::class)->execute($payment);
        } elseif ($transaction->status === 'CANCELLED') {
            app(MarkPaymentRefundedAction::class)->execute(
                $payment,
                (string) $transaction->id,
                fromUtc($transaction->txnTime),
            );
        }
    }

    public function getViewConfig(): ?object
    {
        $scripts = ['https://hosted.paysafe.com/js/v1/latest/paysafe.min.js'];

        if ($this->config('use_checkout')) {
            $scripts[] = 'https://hosted.paysafe.com/checkout/v1/latest/paysafe.checkout.min.js';
        }

        // https://developer.paysafe.com/en/sdks/paysafejs/setup/
        // https://developer.paysafe.com/en/sdks/paysafe-checkout/overview/#including-the-sdk
        return (object) [
            'name' => $this->name(),
            'scripts' => $scripts,
            'settings' => [
                'api_key' => base64_encode($this->config('token_user') . ':' . $this->config('token_pass')),
                'environment' => $this->config('test_mode') ? 'TEST' : 'LIVE',
                'use_3ds2' => (bool) $this->config('use_3ds2'),
                'card_accounts' => $this->getCardAccounts(),
                'use_checkout' => (bool) $this->config('use_checkout'),
                'preferred_method' => $this->config('checkout_preferred'),
                'image_url' => volt_setting('favicon'),
                'button_color' => sys_get('default_color_1'),
            ],
        ];
    }
}
