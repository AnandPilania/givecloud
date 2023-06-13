<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentFailedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentRefundedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentSucceededAction;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\SyncablePaymentStatus;
use Ds\Domain\Commerce\Contracts\Viewable;
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
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Throwable;

class AuthorizeNetGateway extends AbstractGateway implements
    Gateway,
    CaptureTokens,
    SourceTokens,
    Refunds,
    PartialRefunds,
    SyncablePaymentStatus,
    Viewable
{
    /** @var \net\authorize\api\contract\v1\MerchantAuthenticationType */
    protected $merchantAuthentication;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'authorizenet';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Authorize.Net';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.authorize.net';
    }

    /**
     * Get the merchant authentication.
     *
     * @return \net\authorize\api\contract\v1\MerchantAuthenticationType
     */
    protected function getMerchantAuthentication()
    {
        if (! $this->merchantAuthentication) {
            $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType;
            $this->merchantAuthentication->setName($this->config('credential1'));
            $this->merchantAuthentication->setTransactionKey($this->config('credential2'));
        }

        return $this->merchantAuthentication;
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
        return new ErrorResponse('Use Accept.js to obtain a capture token');
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
            throw new InvalidArgumentException('Opaque data required');
        }

        $transactionReq = new AnetAPI\TransactionRequestType;
        $transactionReq->setTransactionType('authCaptureTransaction');
        $transactionReq->setAmount(round($order->totalamount, 2));
        $transactionReq->setCurrencyCode($order->currency_code);
        $transactionReq->setCustomerIP($this->getClientIp());

        $opaqueData = new AnetAPI\OpaqueDataType;
        $opaqueData->setDataDescriptor('COMMON.ACCEPT.INAPP.PAYMENT');
        $opaqueData->setDataValue($this->request()->input('token'));

        $payment = new AnetAPI\PaymentType;
        $payment->setOpaqueData($opaqueData);
        $transactionReq->setPayment($payment);

        $orderType = new AnetAPI\OrderType;
        $orderType->setInvoiceNumber($order->client_uuid);
        $transactionReq->setOrder($orderType);

        $billTo = new AnetAPI\CustomerAddressType;
        $billTo->setFirstName(substr($order->billing_first_name, 0, 50));
        $billTo->setLastName(substr($order->billing_last_name, 0, 50));
        $billTo->setCompany(substr($order->billing_organization_name, 0, 50));
        $billTo->setAddress(substr($order->billingaddress1, 0, 60));
        $billTo->setCity(substr($order->billingcity, 0, 40));
        $billTo->setState(substr($order->billingstate, 0, 40));
        $billTo->setZip(substr($order->billingzip, 0, 20));
        $billTo->setCountry(substr($order->billingcountry, 0, 60));
        $billTo->setPhoneNumber(substr($order->billingphone, 0, 25));
        $transactionReq->setBillTo($billTo);

        $shipTo = new AnetAPI\CustomerAddressType;
        $shipTo->setFirstName(substr($order->shipping_first_name, 0, 50));
        $shipTo->setLastName(substr($order->shipping_last_name, 0, 50));
        $shipTo->setCompany(substr($order->shipping_organization_name, 0, 50));
        $shipTo->setAddress(substr($order->shipaddress1, 0, 60));
        $shipTo->setCity(substr($order->shipcity, 0, 40));
        $shipTo->setState(substr($order->shipstate, 0, 40));
        $shipTo->setZip(substr($order->shipzip, 0, 20));
        $shipTo->setCountry(substr($order->shipcountry, 0, 60));
        $transactionReq->setShipTo($shipTo);

        if ($order->member) {
            $customer = new AnetAPI\CustomerDataType;
            $customer->setId($order->member->account_id);
            $customer->setType(($order->member->accountType->is_organization ?? false) ? 'business' : 'individual');
            $customer->setEmail(substr($order->member->email, 0, 255));
            $transactionReq->setCustomer($customer);
        }

        if ($duplicateWindowValue = $this->config('duplicate_window')) {
            $duplicateWindow = new AnetAPI\SettingType;
            $duplicateWindow->setSettingName('duplicateWindow');
            $duplicateWindow->setSettingValue($duplicateWindowValue);
            $transactionReq->addToTransactionSettings($duplicateWindow);
        }

        $req = new AnetAPI\CreateTransactionRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setTransactionRequest($transactionReq);

        try {
            $res = $this->executeReq($req, AnetController\CreateTransactionController::class);
            $res = $this->getTransaction($res->getTransId());
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $data = [
            'completed' => (bool) $res->getResponseCode() == 1,
            'response' => (string) $res->getResponseCode(),
            'response_text' => (string) $res->getResponseReasonDescription() ?: $res->getResponseReasonCode(),
            'avs_code' => (string) $res->getAVSResponse(),
            'cvv_code' => (string) $res->getCardCodeResponse(),
            'transaction_id' => (string) $res->getTransId(),
        ];

        $res = $this->createTransactionResponse(
            $this->setPaymentInformation($data, $res->getPayment())
        );

        if ($res->isCompleted()) {
            return $res;
        }

        throw new PaymentException($res);
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
        return new ErrorResponse('Use Accept.js to obtain a capture token');
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
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType;
        $paymentProfile->setDefaultPaymentProfile(true);

        if ($paymentMethod->member->accountType->is_organization ?? false) {
            $paymentProfile->setCustomerType('business');
        } else {
            $paymentProfile->setCustomerType('individual');
        }

        $opaqueData = new AnetAPI\OpaqueDataType;
        $opaqueData->setDataDescriptor('COMMON.ACCEPT.INAPP.PAYMENT');
        $opaqueData->setDataValue($this->request()->input('token'));

        $payment = new AnetAPI\PaymentType;
        $payment->setOpaqueData($opaqueData);
        $paymentProfile->setPayment($payment);

        $billTo = new AnetAPI\CustomerAddressType;
        $billTo->setFirstName(substr($paymentMethod->billing_first_name, 0, 50));
        $billTo->setLastName(substr($paymentMethod->billing_last_name, 0, 50));
        // $billTo->setCompany(substr($paymentMethod->billing_organization_name, 0, 50));
        $billTo->setAddress(substr($paymentMethod->billing_address1, 0, 60));
        $billTo->setCity(substr($paymentMethod->billing_city, 0, 40));
        $billTo->setState(substr($paymentMethod->billing_state, 0, 40));
        $billTo->setZip(substr($paymentMethod->billing_postal, 0, 20));
        $billTo->setCountry(substr($paymentMethod->billing_country, 0, 60));
        $billTo->setPhoneNumber(substr($paymentMethod->billing_phone, 0, 25));
        $paymentProfile->setBillTo($billTo);

        if ($paymentMethod->member->authorizenet_customer_id) {
            $req = new AnetAPI\CreateCustomerPaymentProfileRequest;
            $req->setMerchantAuthentication($this->getMerchantAuthentication());
            $req->setCustomerProfileId($paymentMethod->member->authorizenet_customer_id);
            $req->setPaymentProfile($paymentProfile);

            try {
                $res = $this->executeReq($req, AnetController\CreateCustomerPaymentProfileController::class);
            } catch (Throwable $e) {
                if ($e->getCode() === 'E00039') {
                    $res = $e->getResponse();
                } else {
                    throw new GatewayException($e->getMessage(), $e->getCode(), $e);
                }
            }

            $paymentProfileId = $res->getCustomerPaymentProfileId();
        } else {
            $customerProfile = new AnetAPI\CustomerProfileType;
            $customerProfile->setEmail($paymentMethod->billing_email);
            $customerProfile->setMerchantCustomerId($paymentMethod->member->account_id);
            $customerProfile->setPaymentProfiles([$paymentProfile]);

            $req = new AnetAPI\CreateCustomerProfileRequest;
            $req->setMerchantAuthentication($this->getMerchantAuthentication());
            $req->setProfile($customerProfile);

            try {
                $res = $this->executeReq($req, AnetController\CreateCustomerProfileController::class);
            } catch (Throwable $e) {
                throw new GatewayException($e->getMessage(), $e->getCode(), $e);
            }

            $paymentMethod->member->authorizenet_customer_id = $res->getCustomerProfileId();
            $paymentMethod->member->save();

            $paymentProfileId = Arr::get($res->getCustomerPaymentProfileIdList(), 0);
        }

        $paymentMethod->authorizenet_customer_id = $paymentMethod->member->authorizenet_customer_id;
        $paymentMethod->save();

        $req = new AnetAPI\GetCustomerPaymentProfileRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setCustomerProfileId($paymentMethod->authorizenet_customer_id);
        $req->setCustomerPaymentProfileId($paymentProfileId);
        $req->setUnmaskExpirationDate(true);

        try {
            $res = $this->executeReq($req, AnetController\GetCustomerPaymentProfileController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $data = [
            'completed' => (bool) true,
            'source_token' => (string) $paymentProfileId,
        ];

        return $this->createTransactionResponse(
            $this->setPaymentInformation($data, $res->getPaymentProfile()->getPayment())
        );
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
        $paymentProfile = new AnetAPI\PaymentProfileType;
        $paymentProfile->setPaymentProfileId($paymentMethod->token);

        $customerProfile = new AnetAPI\CustomerProfilePaymentType;
        $customerProfile->setCustomerProfileId($paymentMethod->authorizenet_customer_id ?? $paymentMethod->member->authorizenet_customer_id);
        $customerProfile->setPaymentProfile($paymentProfile);

        $transactionReq = new AnetAPI\TransactionRequestType;
        $transactionReq->setTransactionType('authCaptureTransaction');
        $transactionReq->setAmount($amount->amount);
        $transactionReq->setCurrencyCode($amount->currency_code);
        $transactionReq->setCustomerIP($this->getClientIp());
        $transactionReq->setProfile($customerProfile);

        $req = new AnetAPI\CreateTransactionRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setTransactionRequest($transactionReq);

        try {
            $res = $this->executeReq($req, AnetController\CreateTransactionController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $message = Arr::get($res->getMessages(), 0);

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->getResponseCode() == 1,
            'response' => (string) $res->getResponseCode(),
            'response_text' => (string) ($message ? $message->getDescription() : ''),
            'avs_code' => (string) $res->getAvsResultCode(),
            'cvv_code' => (string) $res->getCvvResultCode(),
            'transaction_id' => (string) $res->getTransId(),
            'account_type' => $paymentMethod->account_type,
            'cc_number' => $paymentMethod->cc_expiry ? $paymentMethod->account_number : null,
            'cc_exp' => fromUtcFormat($paymentMethod->cc_expiry, 'my'),
            'ach_account' => $paymentMethod->cc_expiry ? null : $paymentMethod->account_number,
            'ach_routing' => $paymentMethod->ach_routing,
            'ach_type' => $paymentMethod->ach_account_type,
            'ach_entity' => $paymentMethod->ach_entity_type,
            'source_token' => (string) $paymentMethod->token,
        ]);

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
        try {
            $transaction = $this->getTransaction($transactionId);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $transactionReq = new AnetAPI\TransactionRequestType;
        $transactionReq->setTransactionType('voidTransaction');
        $transactionReq->setRefTransId($transactionId);

        if (! $fullRefund || $transaction->getTransactionStatus() === 'settledSuccessfully') {
            $payment = new AnetAPI\PaymentType;
            $creditCard = $transaction->getPayment()->getCreditCard();
            $bankAccount = $transaction->getPayment()->getBankAccount();

            if ($creditCard) {
                $cardType = new AnetAPI\CreditCardType;
                $cardType->setCardNumber(substr($creditCard->getCardNumber(), -4));
                $cardType->setExpirationDate('XXXX');
                $payment->setCreditCard($cardType);
            } elseif ($bankAccount) {
                $bankType = new AnetAPI\BankAccountType;
                $bankType->setAccountType($bankAccount->getAccountType());
                $bankType->setRoutingNumber($bankAccount->getRoutingNumber());
                $bankType->setAccountNumber($bankAccount->getAccountNumber());
                $bankType->setNameOnAccount($bankAccount->getNameOnAccount());
                $bankType->setEcheckType($bankAccount->getEcheckType());
                $bankType->setBankName($bankAccount->getBankName());
                $payment->setBankAccount($bankType);
            }

            if ($fullRefund) {
                $amount = $transaction->getSettleAmount();
            }

            $transactionReq->setTransactionType('refundTransaction');
            $transactionReq->setAmount(round($amount, 2));
            $transactionReq->setPayment($payment);
        }

        $req = new AnetAPI\CreateTransactionRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setTransactionRequest($transactionReq);

        try {
            $res = $this->executeReq($req, AnetController\CreateTransactionController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $message = Arr::get($res->getMessages(), 0);

        $res = $this->createTransactionResponse([
            'completed' => (bool) $res->getResponseCode() == 1,
            'response' => $res->getResponseCode() == 1 ? 'succeeded' : 'failed',
            'response_text' => (string) ($message ? $message->getDescription() : $res->getResponseCode()),
            'transaction_id' => (string) $res->getTransId(),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new RefundException($res);
    }

    /**
     * Get customer profile.
     *
     * @param string $customerProfileId
     * @return \net\authorize\api\contract\v1\CustomerProfileMaskedType
     */
    public function getCustomerProfile(string $customerProfileId, bool $isMerchantCustomerId = true)
    {
        $req = new AnetAPI\GetCustomerProfileRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());

        if ($isMerchantCustomerId) {
            $req->setMerchantCustomerId($customerProfileId);
        } else {
            $req->setCustomerProfileId($customerProfileId);
        }

        try {
            $res = $this->executeReq($req, AnetController\GetCustomerProfileController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getProfile();
    }

    /**
     * Get customer payment profile.
     *
     * @param string $customerProfileId
     * @param string $customerPaymentProfileId
     * @return \net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType
     */
    public function getCustomerPaymentProfile(string $customerProfileId, string $customerPaymentProfileId)
    {
        $req = new AnetAPI\GetCustomerPaymentProfileRequest;
        $req->setRefId(Str::random(20));
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setCustomerProfileId($customerProfileId);
        $req->setCustomerPaymentProfileId($customerPaymentProfileId);

        try {
            $res = $this->executeReq($req, AnetController\GetCustomerPaymentProfileController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getPaymentProfile();
    }

    /**
     * Update customer payment profile.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @return \net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType
     */
    public function updateCustomerPaymentProfile(PaymentMethod $paymentMethod)
    {
        $profile = $this->getCustomerPaymentProfile(
            $paymentMethod->authorizenet_customer_id ?? $paymentMethod->member->authorizenet_customer_id,
            $paymentMethod->token
        );

        $payment = new AnetAPI\PaymentType;
        $creditCard = $profile->getPayment()->getCreditCard();
        $bankAccount = $profile->getPayment()->getBankAccount();

        if ($creditCard) {
            $cardType = new AnetAPI\CreditCardType;
            $cardType->setCardNumber($creditCard->getCardNumber());
            $cardType->setExpirationDate($creditCard->getExpirationDate());
            $payment->setCreditCard($cardType);
        } elseif ($bankAccount) {
            $bankType = new AnetAPI\BankAccountType;
            $bankType->setAccountType($bankAccount->getAccountType());
            $bankType->setRoutingNumber($bankAccount->getRoutingNumber());
            $bankType->setAccountNumber($bankAccount->getAccountNumber());
            $bankType->setNameOnAccount($bankAccount->getNameOnAccount());
            $bankType->setEcheckType($bankAccount->getEcheckType());
            $bankType->setBankName($bankAccount->getBankName());
            $payment->setBankAccount($bankType);
        }

        $billTo = $profile->getBillTo();
        $billTo->setFirstName(substr($paymentMethod->billing_first_name, 0, 50));
        $billTo->setLastName(substr($paymentMethod->billing_last_name, 0, 50));
        $billTo->setAddress(substr($paymentMethod->billing_address1, 0, 60));
        $billTo->setCity(substr($paymentMethod->billing_city, 0, 40));
        $billTo->setState(substr($paymentMethod->billing_state, 0, 40));
        $billTo->setZip(substr($paymentMethod->billing_postal, 0, 20));
        $billTo->setCountry(substr($paymentMethod->billing_country, 0, 60));
        $billTo->setPhoneNumber(substr($paymentMethod->billing_phone, 0, 25));

        $paymentProfile = new AnetAPI\CustomerPaymentProfileExType;
        $paymentProfile->setCustomerPaymentProfileId($paymentMethod->token);
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($payment);

        $req = new AnetAPI\UpdateCustomerPaymentProfileRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setCustomerProfileId($paymentMethod->authorizenet_customer_id ?? $paymentMethod->member->authorizenet_customer_id);
        $req->setPaymentProfile($paymentProfile);

        try {
            $res = $this->executeReq($req, AnetController\UpdateCustomerPaymentProfileController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Validate customer payment profile.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param bool $liveMode
     * @return \net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType
     */
    public function validateCustomerPaymentProfile(PaymentMethod $paymentMethod, $liveMode = false)
    {
        $validationMode = $liveMode ? 'liveMode' : 'testMode';

        $req = new AnetAPI\ValidateCustomerPaymentProfileRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setCustomerProfileId($paymentMethod->authorizenet_customer_id ?? $paymentMethod->member->authorizenet_customer_id);
        $req->setCustomerPaymentProfileId($paymentMethod->token);
        $req->setValidationMode($validationMode);

        try {
            $res = $this->executeReq($req, AnetController\ValidateCustomerPaymentProfileController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Create payment profile from transaction.
     *
     * @param string $customerId
     * @param string $transactionId
     * @return \net\authorize\api\contract\v1\CreateCustomerProfileResponse
     */
    public function createPaymentProfileFromTransaction(string $customerId, string $transactionId)
    {
        $req = new AnetAPI\CreateCustomerProfileFromTransactionRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setTransId($transactionId);
        $req->setCustomerProfileId($customerId);

        try {
            $res = $this->executeReq($req, AnetController\CreateCustomerProfileFromTransactionController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get customer profile transactions.
     *
     * @param string $customerProfileId
     * @return \net\authorize\api\contract\v1\TransactionSummaryType[]
     */
    public function getCustomerProfileTransactions(string $customerProfileId)
    {
        $req = new AnetAPI\GetTransactionListForCustomerRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setCustomerProfileId($customerProfileId);

        try {
            $res = $this->executeReq($req, AnetController\GetTransactionListForCustomerController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getTransactions();
    }

    /**
     * Get a settled batch list.
     *
     * @param \DateTimeInterface|string $firstDate
     * @param \DateTimeInterface|string|null $lastDate
     * @return \net\authorize\api\contract\v1\BatchDetailsType[]
     */
    public function getSettlementBatches($firstDate, $lastDate = null): array
    {
        $firstDate = fromLocal($firstDate)->startOfDay();
        $lastDate = fromLocal($lastDate ?? $firstDate)->endOfDay();

        $req = new AnetAPI\GetSettledBatchListRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setIncludeStatistics(true);
        $req->setFirstSettlementDate($firstDate);
        $req->setLastSettlementDate($lastDate);

        try {
            $res = $this->executeReq($req, AnetController\GetSettledBatchListController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getBatchList() ?? [];
    }

    /**
     * Get a transactions in a batch.
     *
     * @param string $batchId
     * @param array $options
     * @return \net\authorize\api\contract\v1\TransactionSummaryType[]
     */
    public function getTransactions(string $batchId, array $options = []): array
    {
        $req = new AnetAPI\GetTransactionListRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setBatchId($batchId);

        $this->setTransactionListPagingAndSorting($req, $options);

        try {
            $res = $this->executeReq($req, AnetController\GetTransactionListController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getTransactions() ?? [];
    }

    /**
     * Get a unsettled transactions.
     *
     * @param array $options
     * @return \net\authorize\api\contract\v1\TransactionSummaryType[]
     */
    public function getUnsettledTransactions(array $options = []): array
    {
        $req = new AnetAPI\GetUnsettledTransactionListRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());

        $this->setTransactionListPagingAndSorting($req, $options);

        try {
            $res = $this->executeReq($req, AnetController\GetUnsettledTransactionListController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getTransactions() ?? [];
    }

    /**
     * Set the paging and sorting options for a transaction list request.
     *
     * @param \net\authorize\api\contract\v1\GetTransactionListRequest|\net\authorize\api\contract\v1\GetUnsettledTransactionListRequest $req
     * @param array $options
     */
    private function setTransactionListPagingAndSorting($req, array $options)
    {
        $options = Arr::defaults($options, [
            'limit' => 100,
            'offset' => 1,
            'order_by' => 'submitTimeUTC',
            'order_desc' => true,
        ]);

        $paging = new AnetAPI\PagingType;
        $paging->setLimit($options['limit']);
        $paging->setOffset($options['offset']);

        $sorting = new AnetAPI\TransactionListSortingType;
        $sorting->setOrderBy($options['order_by']);
        $sorting->setOrderDescending($options['order_desc']);

        $req->setPaging($paging);
        $req->setSorting($sorting);
    }

    /**
     * Get a charge.
     *
     * @param string $transactionId
     * @return \net\authorize\api\contract\v1\TransactionDetailsType
     */
    public function getTransaction(string $transactionId): AnetAPI\TransactionDetailsType
    {
        $req = new AnetAPI\GetTransactionDetailsRequest;
        $req->setMerchantAuthentication($this->getMerchantAuthentication());
        $req->setTransId($transactionId);

        try {
            $res = $this->executeReq($req, AnetController\GetTransactionDetailsController::class);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res->getTransaction();
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

        switch ($transaction->getTransactionStatus()) {
            case 'settledSuccessfully':
                app(MarkPaymentSucceededAction::class)->execute($payment);
                break;
            case 'communicationError':
            case 'declined':
            case 'expired':
            case 'failedReview':
            case 'generalError':
            case 'settlementError':
                app(MarkPaymentFailedAction::class)->execute($payment);
                break;
            case 'refundPendingSettlement':
            case 'refundSettledSuccessfully':
            case 'voided':
                app(MarkPaymentRefundedAction::class)->execute(
                    $payment,
                    $payment->reference_number,
                    fromUtc($payment->captured_at),
                );
                break;
        }
    }

    /**
     * Set payment information in a data array.
     *
     * @param array $data
     * @param \net\authorize\api\contract\v1\PaymentMaskedType $payment
     * @return array
     */
    private function setPaymentInformation(array &$data, AnetAPI\PaymentMaskedType $payment)
    {
        $creditCard = $payment->getCreditCard();
        $bankAccount = $payment->getBankAccount();

        if ($creditCard) {
            $data['account_type'] = (string) $creditCard->getCardType();
            $data['cc_number'] = (string) $creditCard->getCardNumber();
            $data['cc_exp'] = (string) fromUtcFormat($creditCard->getExpirationDate(), 'my');

            switch ($data['account_type']) {
                case 'AmericanExpress': $data['account_type'] = 'American Express'; break;
                case 'DinersClub':      $data['account_type'] = 'Diners Club'; break;
            }
        }

        if ($bankAccount) {
            $data['ach_bank_name'] = (string) $bankAccount->getBankName();
            $data['ach_account'] = (string) $bankAccount->getAccountNumber();
            $data['ach_routing'] = (string) $bankAccount->getRoutingNumber();
            $data['ach_type'] = (string) $bankAccount->getAccountType();
            $data['ach_entity'] = 'personal';

            if ($data['ach_type'] === 'businessChecking') {
                $data['ach_type'] = 'checking';
                $data['ach_entity'] = 'business';
            }
        }

        return $data;
    }

    /**
     * Execute a request and return the response.
     *
     * @param \net\authorize\api\contract\v1\ANetApiRequestType $req
     * @param string $klass
     * @return mixed
     */
    protected function executeReq(AnetAPI\ANetApiRequestType $req, string $klass)
    {
        $controller = new $klass($req);

        if ($this->config('test_mode')) {
            $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        $res = $controller->getApiResponse();

        if (empty($res)) {
            throw new GatewayException('No response', 422);
        }

        $messages = $res->getMessages();

        if ($res instanceof AnetAPI\CreateTransactionResponse) {
            $res = $res->getTransactionResponse();

            if (empty($res)) {
                throw new GatewayException('No transaction response', 422);
            }

            if ($error = Arr::get($res->getErrors(), 0)) {
                throw new GatewayException($error->getErrorText(), $error->getErrorCode(), null, $res);
            }
        }

        if ($messages->getResultCode() === 'Error') {
            if ($error = Arr::get($messages->getMessage(), 0)) {
                throw new GatewayException($error->getText(), $error->getCode(), null, $res);
            }

            throw new GatewayException('Request failed', 422, null, $res);
        }

        return $res;
    }

    public function getViewConfig(): ?object
    {
        $environment = $this->config('test_mode') ? 'jstest' : 'js';

        return (object) [
            'name' => $this->name(),
            'scripts' => [['src' => "https://$environment.authorize.net/v1/Accept.js", 'charset' => 'utf-8']],
            'settings' => [
                'api_login_id' => $this->config('credential1'),
                'client_key' => $this->config('credential3'),
            ],
        ];
    }
}
