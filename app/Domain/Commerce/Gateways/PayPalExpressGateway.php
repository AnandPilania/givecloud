<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentFailedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentRefundedAction;
use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentSucceededAction;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\OAuth;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Contracts\SyncablePaymentStatus;
use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RedirectException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\AccessTokenResponse;
use Ds\Domain\Commerce\Responses\RedirectToResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\Shared\DateTime;
use Ds\Models\Member as Supporter;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\DoReferenceTransactionRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\PersonNameType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\IPN\PPIPNMessage;
use PayPal\PayPalAPI\CreateBillingAgreementReq;
use PayPal\PayPalAPI\CreateBillingAgreementRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\DoReferenceTransactionReq;
use PayPal\PayPalAPI\DoReferenceTransactionRequestType;
use PayPal\PayPalAPI\GetPalDetailsReq;
use PayPal\PayPalAPI\GetPalDetailsRequestType;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\PayPalAPI\GetTransactionDetailsReq;
use PayPal\PayPalAPI\GetTransactionDetailsRequestType;
use PayPal\PayPalAPI\RefundTransactionReq;
use PayPal\PayPalAPI\RefundTransactionRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\TransactionSearchReq;
use PayPal\PayPalAPI\TransactionSearchRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\Service\PermissionsService;
use PayPal\Types\Common\RequestEnvelope;
use PayPal\Types\Perm\CancelPermissionsRequest;
use PayPal\Types\Perm\GetAccessTokenRequest;
use PayPal\Types\Perm\GetBasicPersonalDataRequest;
use PayPal\Types\Perm\GetPermissionsRequest;
use PayPal\Types\Perm\PersonalAttributeList;
use PayPal\Types\Perm\RequestPermissionsRequest;
use Throwable;

class PayPalExpressGateway extends AbstractGateway implements
    Gateway,
    OAuth,
    CaptureTokens,
    SourceTokens,
    Refunds,
    PartialRefunds,
    SyncablePaymentStatus,
    Viewable
{
    /** @var string */
    protected $configKey = 'paypal';

    /** @var \PayPal\Service\PayPalAPIInterfaceServiceService */
    protected $soapApi;

    /** @var \PayPal\Service\PermissionsService */
    protected $permissionsApi;

    /** @var array */
    protected $permissions = [
        'EXPRESS_CHECKOUT' => 'Use Express Checkout to process payments.',
        'AUTH_CAPTURE' => 'Authorize and capture your PayPal transactions.',
        'BILLING_AGREEMENT' => 'Obtain authorization for pre-approved payments and initiate pre-approved transactions.',
        'REFERENCE_TRANSACTION' => 'Process a payment based on a previous transaction.',
        'TRANSACTION_DETAILS' => 'Obtain transaction specific information.',
        'TRANSACTION_SEARCH' => 'Search your transactions for items that match specific criteria and display the results.',
        'RECURRING_PAYMENTS' => 'Create and manage recurring payments.',
        'REFUND' => 'Refund a transaction on your behalf.',
    ];

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'paypalexpress';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'PayPal Express Checkout';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.paypal.com';
    }

    /**
     * Get the soap api client.
     *
     * @return \PayPal\Service\PayPalAPIInterfaceServiceService
     */
    protected function getSoapApi()
    {
        if (! $this->soapApi) {
            $logDirectory = dirname($this->config('logging.filename', false));

            if ($logDirectory && ! is_dir($logDirectory)) {
                mkdir($logDirectory);
            }

            $this->soapApi = new PayPalAPIInterfaceServiceService([
                'mode' => $this->config('test_mode') ? 'sandbox' : 'live',
                'log.LogEnabled' => $this->config('logging.log_enabled', false),
                'log.FileName' => $this->config('logging.filename', false),
                'log.LogLevel' => $this->config('logging.log_level', false),
                'acct1.UserName' => $this->config('classic.api_username'),
                'acct1.Password' => $this->config('classic.api_password'),
                'acct1.Signature' => $this->config('classic.signature'),
                'acct1.Subject' => $this->config('credential1'),
            ]);
        }

        return $this->soapApi;
    }

    /**
     * Get the permissions api client.
     *
     * @return \PayPal\Service\PermissionsService
     */
    protected function getPermissionsApi()
    {
        if (! $this->permissionsApi) {
            $this->permissionsApi = new PermissionsService([
                'mode' => $this->config('test_mode') ? 'sandbox' : 'live',
                'log.LogEnabled' => $this->config('logging.log_enabled', false),
                'log.FileName' => $this->config('logging.filename', false),
                'log.LogLevel' => $this->config('logging.log_level', false),
                'acct1.UserName' => $this->config('classic.api_username'),
                'acct1.Password' => $this->config('classic.api_password'),
                'acct1.Signature' => $this->config('classic.signature'),
                'acct1.AppId' => $this->config('classic.app_id'),
            ]);
        }

        return $this->permissionsApi;
    }

    /**
     * Verify the PayPal connection works.
     *
     * @return bool
     */
    public function verifyConnection()
    {
        $merchantId = $this->config('credential1');

        if (empty($merchantId)) {
            return false;
        }

        $req = new GetPalDetailsReq;
        $req->GetPalDetailsRequest = new GetPalDetailsRequestType;

        try {
            $res = $this->getSoapApi()->GetPalDetails($req);
        } catch (Throwable $e) {
            return false;
        }

        return $res->Pal === $merchantId;
    }

    /**
     * Verify the Reference Transactions are enabled.
     *
     * @return bool
     */
    public function verifyReferenceTransactions()
    {
        try {
            $this->getSourceTokenUrl(new PaymentMethod, secure_site_url('/'), secure_site_url('/'));
        } catch (GatewayException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get personal data.
     *
     * @return \PayPal\Types\Perm\GetBasicPersonalDataResponse|void
     */
    public function getPersonalData()
    {
        $merchantId = $this->config('credential1');

        if (empty($merchantId)) {
            return;
        }

        $req = new GetBasicPersonalDataRequest;
        $req->requestEnvelope = new RequestEnvelope('en_US');
        $req->attributeList = new PersonalAttributeList;
        $req->attributeList->attribute = [
            'http://axschema.org/namePerson/first',
            'http://axschema.org/namePerson/last',
            'http://axschema.org/contact/email',
            'http://axschema.org/contact/fullname',
            'http://openid.net/schema/company/name',
            'http://axschema.org/contact/country/home',
            'https://www.paypal.com/webapps/auth/schema/payerID',
        ];

        $credential = new PPSignatureCredential(
            $this->config('classic.api_username'),
            $this->config('classic.api_password'),
            $this->config('classic.signature')
        );

        $credential->setApplicationId($this->config('classic.app_id'));
        $credential->setThirdPartyAuthorization(new PPTokenAuthorization(
            $this->config('credential3'),
            $this->config('credential4')
        ));

        try {
            $res = $this->getPermissionsApi()->GetBasicPersonalData($req, $credential);
        } catch (Throwable $e) {
            return;
        }

        return $res;
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
            $url = 'https://www.sandbox.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow';
        } else {
            $url = 'https://www.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow';
        }

        $params = [
            'countryCode' => 'US',
            'partnerId' => $this->config('classic.partner_id'),
            'partnerLogoUrl' => 'https://cdn.givecloud.co/static/etc/gc-logo.png',
            'productIntentID' => 'addipmt',
            'integrationType' => 'T',
            'permissionNeeded' => implode(',', array_keys($this->permissions)),
            'returnToPartnerUrl' => $returnUrl,
            'displayMode' => 'lightbox',
            'receiveCredentials' => 'true',
            'showPermissions' => 'true',
        ];

        return $url . '?' . http_build_query($params);
    }

    /**
     * Requests permissions to execute API operations on a PayPal account holder's behalf.
     * Returns a Token to that can be used to request permissions.
     *
     * @param string $returnUrl
     * @return string
     */
    public function requestPermissions(string $returnUrl): string
    {
        $req = new RequestPermissionsRequest(array_keys($this->permissions), $returnUrl);
        $req->requestEnvelope = new RequestEnvelope('en_US');

        $res = $this->getPermissionsApi()->RequestPermissions($req);

        if (in_array($res->responseEnvelope->ack, ['Success', 'SuccessWithWarning'])) {
            return $res->token;
        }

        $this->throwGatewayException($res);
    }

    /**
     * Requests permissions to execute API operations on a PayPal account holder's behalf.
     * Returns a redirect URL to PayPal that can be used to request permissions.
     *
     * @param string $returnUrl
     * @return string
     */
    public function requestPermissionsLink(string $returnUrl): string
    {
        $token = $this->requestPermissions($returnUrl);

        if ($this->config('test_mode')) {
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $url = 'https://www.paypal.com/cgi-bin/webscr';
        }

        return "{$url}?cmd=_grant-permission&request_token={$token}";
    }

    /**
     * Gets an access token for a set of permissions.
     *
     * @param string|null $returnUrl
     * @return \Ds\Domain\Commerce\Responses\AccessTokenResponse
     */
    public function getAccessToken(?string $returnUrl = null): AccessTokenResponse
    {
        $req = new GetAccessTokenRequest;
        $req->requestEnvelope = new RequestEnvelope('en_US');
        $req->token = $this->request()->input('request_token');
        $req->verifier = $this->request()->input('verification_code');

        $res = $this->getPermissionsApi()->GetAccessToken($req);

        if (in_array($res->responseEnvelope->ack, ['Success', 'SuccessWithWarning'])) {
            return new AccessTokenResponse([
                'account_id' => $this->provider()->credential1,
                'access_token' => $res->token,
                'token_secret' => $res->tokenSecret,
                'scope' => $res->scope,
            ]);
        }

        $this->throwGatewayException($res);
    }

    /**
     * Verify the access token works.
     *
     * @return bool
     */
    public function verifyAccessToken(): bool
    {
        return $this->verifyConnection();
    }

    /**
     * Gets the permissions associated with an access token.
     *
     * @return string
     */
    public function getPermissions(): string
    {
        $req = new GetPermissionsRequest($this->config('credential3'));
        $req->requestEnvelope = new RequestEnvelope('en_US');

        $res = $this->getPermissionsApi()->GetPermissions($req);

        if (in_array($res->responseEnvelope->ack, ['Success', 'SuccessWithWarning'])) {
            return $res->scope;
        }

        $this->throwGatewayException($res);
    }

    /**
     * Cancels access to a set of permissions.
     *
     * @return bool
     */
    public function cancelPermissions(): bool
    {
        $req = new CancelPermissionsRequest($this->config('credential3'));
        $req->requestEnvelope = new RequestEnvelope('en_US');

        $res = $this->getPermissionsApi()->CancelPermissions($req);

        if (in_array($res->responseEnvelope->ack, ['Success', 'SuccessWithWarning'])) {
            return true;
        }

        $this->throwGatewayException($res);
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
        $payment = new PaymentDetailsType;
        $payment->ItemTotal = new BasicAmountType($order->currency_code, round($order->subtotal + $order->dcc_total_amount, 2));
        $payment->TaxTotal = new BasicAmountType($order->currency_code, round($order->taxtotal, 2));
        $payment->ShippingTotal = new BasicAmountType($order->currency_code, round($order->shipping_amount, 2));
        $payment->OrderTotal = new BasicAmountType($order->currency_code, round($order->totalamount, 2));
        $payment->ButtonSource = $this->config('bn_code', false);

        if ($order->shipaddress1) {
            $payment->ShipToAddress = new AddressType;
            $payment->ShipToAddress->Name = $order->shipname;
            $payment->ShipToAddress->Street1 = $order->shipaddress1;
            $payment->ShipToAddress->Street2 = $order->shipaddress2;
            $payment->ShipToAddress->CityName = $order->shipcity;
            $payment->ShipToAddress->StateOrProvince = $order->shipstate;
            $payment->ShipToAddress->PostalCode = $order->shipzip;
            $payment->ShipToAddress->Country = $order->shipcountry;
            $payment->ShipToAddress->Phone = $order->shipphone;
        }

        $details = new SetExpressCheckoutRequestDetailsType;
        $details->PaymentDetails = [$payment];
        $details->ReturnURL = $returnUrl;
        $details->CancelURL = $cancelUrl;

        $req = new SetExpressCheckoutReq;
        $req->SetExpressCheckoutRequest = new SetExpressCheckoutRequestType;
        $req->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = $details;

        $res = $this->getSoapApi()->SetExpressCheckout($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            if ($this->config('test_mode')) {
                $url = 'https://www.sandbox.paypal.com/checkoutnow';
            } else {
                $url = 'https://www.paypal.com/checkoutnow';
            }

            return new RedirectToResponse("{$url}?token={$res->Token}&useraction=commit");
        }

        $this->throwGatewayException($res);
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
            throw new GatewayException('Token required to complete checkout');
        }

        $payment = new PaymentDetailsType;
        $payment->OrderTotal = new BasicAmountType($order->currency_code, $order->totalamount);
        $payment->AllowedPaymentMethod = 'InstantPaymentOnly';
        $payment->ButtonSource = $this->config('bn_code', false);

        $details = new DoExpressCheckoutPaymentRequestDetailsType;
        $details->Token = $this->request()->input('token');
        $details->PaymentDetails = [$payment];
        $details->ButtonSource = $this->config('bn_code', false);

        if ($this->request()->has('PayerID')) {
            $details->PayerID = $this->request()->input('PayerID');
        }

        $req = new DoExpressCheckoutPaymentReq;
        $req->DoExpressCheckoutPaymentRequest = new DoExpressCheckoutPaymentRequestType;
        $req->DoExpressCheckoutPaymentRequest->DoExpressCheckoutPaymentRequestDetails = $details;

        $res = $this->getSoapApi()->DoExpressCheckoutPayment($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            $res = $res->DoExpressCheckoutPaymentResponseDetails;

            $res = $this->createTransactionResponse([
                'completed' => in_array($res->PaymentInfo[0]->PaymentStatus, ['Pending', 'Completed']),
                'response' => (string) $res->PaymentInfo[0]->PaymentStatus === 'Completed' ? '1' : '2',
                'response_text' => (string) $res->PaymentInfo[0]->PaymentStatus,
                'transaction_id' => (string) $res->PaymentInfo[0]->TransactionID,
                'source_token' => (string) $res->BillingAgreementID,
            ]);

            if ($order->isForFundraisingForm() && $res->getTransactionId()) {
                $this->updateContributionAndPaymentMethodFromTransactionId($res->getTransactionId(), $order);
            }

            if ($res->isCompleted()) {
                return $res;
            }

            throw new PaymentException($res);
        }

        // https://developer.paypal.com/docs/classic/express-checkout/ht-ec-fundingfailure10486/
        if (isset($res->Errors[0]->ErrorCode) && $res->Errors[0]->ErrorCode === '10486') {
            if ($this->config('test_mode')) {
                $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            } else {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
            }

            throw new RedirectException(
                $res->Errors[0]->ShortMessage,
                $res->Errors[0]->ErrorCode,
                "{$url}?cmd=_express-checkout&token={$details->Token}"
            );
        }

        $this->throwGatewayException($res);
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
        $currencyId = $this->config('currency');

        $agreementDetails = new BillingAgreementDetailsType('MerchantInitiatedBillingSingleAgreement');
        $agreementDetails->BillingAgreementDescription = $this->config('seller_note');

        $details = new SetExpressCheckoutRequestDetailsType;
        $details->OrderTotal = new BasicAmountType($currencyId, 0);
        $details->ReturnURL = $returnUrl;
        $details->CancelURL = $cancelUrl;
        $details->MaxAmount = new BasicAmountType($currencyId, 500);
        $details->NoShipping = '1';
        $details->AddressOverride = '0';
        $details->LocaleCode = 'en_US';
        $details->BillingAgreementDetails = [$agreementDetails];
        $details->BrandName = sys_get('clientShortName');

        $req = new SetExpressCheckoutReq;
        $req->SetExpressCheckoutRequest = new SetExpressCheckoutRequestType;
        $req->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = $details;

        $res = $this->getSoapApi()->SetExpressCheckout($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            if ($this->config('test_mode')) {
                $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            } else {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
            }

            return new RedirectToResponse("{$url}?cmd=_express-checkout&token={$res->Token}", [
                'token' => $res->Token,
            ]);
        }

        $this->throwGatewayException($res);
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
        if (! $this->request()->has('token')) {
            throw new GatewayException('Token required to create billing agreement');
        }

        $req = new CreateBillingAgreementReq;
        $req->CreateBillingAgreementRequest = new CreateBillingAgreementRequestType($this->request()->input('token'));

        $res = $this->getSoapApi()->CreateBillingAgreement($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            return $this->createTransactionResponse([
                'completed' => true,
                'source_token' => (string) $res->BillingAgreementID,
            ]);
        }

        $this->throwGatewayException($res);
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

        $payment = new PaymentDetailsType;
        $payment->OrderTotal = new BasicAmountType($amount->currency_code, $amount->amount);
        $payment->ButtonSource = $this->config('bn_code', false);

        $details = new DoReferenceTransactionRequestDetailsType;
        $details->ReferenceID = $paymentMethod->token;
        $details->PaymentAction = 'Sale';
        $details->PaymentDetails = $payment;

        if ($this->request()->ip() && $this->request()->ip() !== '127.0.0.1') {
            $details->IPAddress = $this->request()->ip();
        }

        $req = new DoReferenceTransactionReq;
        $req->DoReferenceTransactionRequest = new DoReferenceTransactionRequestType;
        $req->DoReferenceTransactionRequest->DoReferenceTransactionRequestDetails = $details;

        $res = $this->getSoapApi()->DoReferenceTransaction($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            $res = $res->DoReferenceTransactionResponseDetails;

            $res = $this->createTransactionResponse([
                'completed' => in_array($res->PaymentInfo->PaymentStatus, ['Pending', 'Completed']),
                'response' => (string) $res->PaymentInfo->PaymentStatus === 'Completed' ? '1' : '2',
                'response_text' => (string) $res->PaymentInfo->PaymentStatus,
                'avs_code' => (string) $res->AVSCode,
                'cvv_code' => (string) $res->CVV2Code,
                'transaction_id' => (string) $res->PaymentInfo->TransactionID,
                'source_token' => (string) $res->BillingAgreementID,
            ]);

            if (optional($contribution)->isForFundraisingForm() && $res->getTransactionId()) {
                $this->updateContributionAndPaymentMethodFromTransactionId($res->getTransactionId(), $contribution, $paymentMethod);
            }

            if ($res->isCompleted()) {
                return $res;
            }

            throw new PaymentException($res);
        }

        $this->throwGatewayException($res);
    }

    private function updateContributionAndPaymentMethodFromTransactionId(string $transactionId, Order $contribution, ?PaymentMethod $paymentMethod = null): void
    {
        $transaction = rescueQuietly(fn () => $this->getTransaction($transactionId));

        $contribution->billing_first_name = $transaction->PayerInfo->PayerName->FirstName ?? null;
        $contribution->billing_last_name = $transaction->PayerInfo->PayerName->LastName ?? null;
        $contribution->billingemail = $transaction->PayerInfo->Payer ?? null;
        $contribution->billingaddress1 = $transaction->PayerInfo->Address->Street1 ?? null;
        $contribution->billingaddress2 = $transaction->PayerInfo->Address->Street2 ?? null;
        $contribution->billingcity = $transaction->PayerInfo->Address->CityName ?? null;
        $contribution->billingstate = $transaction->PayerInfo->Address->StateOrProvince ?? null;
        $contribution->billingzip = $transaction->PayerInfo->Address->PostalCode ?? null;
        $contribution->billingcountry = $transaction->PayerInfo->Address->Country ?? $transaction->PayerInfo->PayerCountry ?? null;
        $contribution->billingphone = $transaction->PayerInfo->Address->Phone ?? null;
        $contribution->save();

        if ($paymentMethod) {
            $paymentMethod->billing_first_name = $contribution->billing_first_name;
            $paymentMethod->billing_last_name = $contribution->billing_last_name;
            $paymentMethod->billing_email = $contribution->billingemail;
            $paymentMethod->billing_address1 = $contribution->billingaddress1;
            $paymentMethod->billing_address2 = $contribution->billingaddress2;
            $paymentMethod->billing_city = $contribution->billingcity;
            $paymentMethod->billing_state = $contribution->billingstate;
            $paymentMethod->billing_postal = $contribution->billingzip;
            $paymentMethod->billing_country = $contribution->billingcountry;
            $paymentMethod->billing_phone = $contribution->billingphone;
            $paymentMethod->paypal_payer_id = $transaction->PayerInfo->PayerID;
            $paymentMethod->save();

            // ensure a dangling PM gets linked to an actual supporter the only case
            // in which this should be occurring is the fundraising forms
            if ($contribution && empty($paymentMethod->member)) {
                $contribution->createMember();

                $paymentMethod->member_id = $contribution->member_id;
                $paymentMethod->save();

                $paymentMethod->load('member');
            }

            if ($paymentMethod->member && empty($paymentMethod->member->bill_email)) {
                if (Supporter::where('email', $paymentMethod->billing_email)->doesntExist()) {
                    $paymentMethod->member->email = $paymentMethod->billing_email;
                }

                $paymentMethod->member->first_name = $paymentMethod->billing_first_name;
                $paymentMethod->member->last_name = $paymentMethod->billing_last_name;
                $paymentMethod->member->bill_first_name = $paymentMethod->billing_first_name;
                $paymentMethod->member->bill_last_name = $paymentMethod->billing_last_name;
                $paymentMethod->member->bill_email = $paymentMethod->billing_email;
                $paymentMethod->member->bill_address_01 = $paymentMethod->billing_address1;
                $paymentMethod->member->bill_address_02 = $paymentMethod->billing_address2;
                $paymentMethod->member->bill_city = $paymentMethod->billing_city;
                $paymentMethod->member->bill_state = $paymentMethod->billing_state;
                $paymentMethod->member->bill_zip = $paymentMethod->billing_postal;
                $paymentMethod->member->bill_country = $paymentMethod->billing_country;
                $paymentMethod->member->bill_phone = $paymentMethod->billing_phone;
                $paymentMethod->member->save();
            }
        }
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
        $currencyId = $this->config('currency');

        $req = new RefundTransactionReq;
        $req->RefundTransactionRequest = new RefundTransactionRequestType;
        $req->RefundTransactionRequest->TransactionID = $transactionId;

        if ($fullRefund) {
            $req->RefundTransactionRequest->RefundType = 'Full';
        } else {
            $req->RefundTransactionRequest->RefundType = 'Partial';
            $req->RefundTransactionRequest->Amount = new BasicAmountType($currencyId, $amount);
        }

        $res = $this->getSoapApi()->RefundTransaction($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            $res = $this->createTransactionResponse([
                'completed' => $res->RefundInfo->RefundStatus === 'Instant',
                'response' => (string) $res->RefundInfo->RefundStatus === 'Instant' ? 'succeeded' : 'failed',
                'response_text' => (string) $res->RefundInfo->RefundStatus,
                'transaction_id' => (string) $res->RefundTransactionID,
                'pending_reason' => (string) $res->RefundInfo->PendingReason,
            ]);

            if ($res->isCompleted()) {
                return $res;
            }

            throw new RefundException($res);
        }

        $this->throwGatewayException($res);
    }

    /**
     * Get transactions.
     *
     * The maximum number of transactions that can be returned from a TransactionSearch API call is 100.
     *
     * @see https://developer.paypal.com/api/nvp-soap/transaction-search-soap/
     *
     * Will only return transaction search results on this API for the previous three years.
     * @see https://www.paypal.com/us/smarthelp/article/why-can't-i-access-transaction-history-greater-than-3-years-ts2241
     *
     * @param \Ds\Domain\Shared\DateTime $startDate
     * @param array $options
     * @return \PayPal\EBLBaseComponents\PaymentTransactionSearchResultType[]
     */
    public function getTransactions(DateTime $startDate, array $options = [])
    {
        $req = new TransactionSearchReq;
        $req->TransactionSearchRequest = new TransactionSearchRequestType;
        $req->TransactionSearchRequest->StartDate = $startDate->toApiFormat();
        $req->TransactionSearchRequest->EndDate = Arr::get($options, 'end_date');
        $req->TransactionSearchRequest->TransactionClass = Arr::get($options, 'transaction_class');
        $req->TransactionSearchRequest->TransactionID = Arr::get($options, 'transaction_id');
        $req->TransactionSearchRequest->ProfileID = Arr::get($options, 'profile_id');
        $req->TransactionSearchRequest->Payer = Arr::get($options, 'payer');

        if (Arr::hasAny($options, ['first_name', 'last_name'])) {
            $req->TransactionSearchRequest->PayerName = new PersonNameType;
            $req->TransactionSearchRequest->PayerName->FirstName = Arr::get($options, 'first_name');
            $req->TransactionSearchRequest->PayerName->LastName = Arr::get($options, 'last_name');
        }

        $res = $this->getSoapApi()->TransactionSearch($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            return $res->PaymentTransactions;
        }

        return [];
    }

    /**
     * Get a transaction.
     *
     * @param string $transactionId
     * @return \PayPal\EBLBaseComponents\PaymentTransactionType|null
     */
    public function getTransaction($transactionId)
    {
        $req = new GetTransactionDetailsReq;
        $req->GetTransactionDetailsRequest = new GetTransactionDetailsRequestType;
        $req->GetTransactionDetailsRequest->TransactionID = $transactionId;

        $res = $this->getSoapApi()->GetTransactionDetails($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            return $res->PaymentTransactionDetails;
        }
    }

    /**
     * Get a recurring payments profile.
     *
     * @param string $profileId
     */
    public function getRecurringPaymentsProfile($profileId)
    {
        $req = new GetRecurringPaymentsProfileDetailsReq;
        $req->GetRecurringPaymentsProfileDetailsRequest = new GetRecurringPaymentsProfileDetailsRequestType;
        $req->GetRecurringPaymentsProfileDetailsRequest->ProfileID = $profileId;

        $res = $this->getSoapApi()->GetRecurringPaymentsProfileDetails($req);

        if (in_array($res->Ack, ['Success', 'SuccessWithWarning'])) {
            return $res->GetRecurringPaymentsProfileDetailsResponseDetails;
        }
    }

    /**
     * Get a validated IPN message.
     *
     * @return \PayPal\IPN\PPIPNMessage
     */
    public function getIpnMessage(): PPIPNMessage
    {
        $message = new PPIPNMessage($this->request()->getContent(), [
            'mode' => $this->config('test_mode') ? 'SANDBOX' : 'LIVE',
        ]);

        $message->validate();

        return $message;
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

        if ($transaction->PaymentInfo->PaymentStatus === 'Completed') {
            app(MarkPaymentSucceededAction::class)->execute($payment);
        } elseif (in_array($transaction->PaymentInfo->PaymentStatus, ['Denied', 'Expired', 'Failed'], true)) {
            app(MarkPaymentFailedAction::class)->execute($payment);
        } elseif (in_array($transaction->PaymentInfo->PaymentStatus, ['Refunded', 'Reversed', 'Voided'], true)) {
            app(MarkPaymentRefundedAction::class)->execute(
                $payment,
                (string) $transaction->PaymentInfo->TransactionID,
                fromUtc($transaction->PaymentInfo->PaymentDate),
            );
        }
    }

    /**
     * Throw a GatewayException with data from a PayPal response.
     *
     * @param mixed $data
     */
    protected function throwGatewayException($data = null)
    {
        if (isset($data->error[0])) {
            throw new GatewayException($data->error[0]->message, $data->error[0]->errorId);
        }

        if (isset($data->Errors[0])) {
            throw new GatewayException($data->Errors[0]->ShortMessage, $data->Errors[0]->ErrorCode);
        }

        if (is_string($data)) {
            throw new GatewayException($data);
        }

        throw new GatewayException('An unknown error has occurred');
    }

    public function getViewConfig(): ?object
    {
        // https://developer.paypal.com/docs/classic/express-checkout/in-context/javascript_advanced_settings/
        return (object) [
            'name' => $this->name(),
            'scripts' => [['src' => 'https://www.paypalobjects.com/api/checkout.js', 'data-version-4' => true]],
            'settings' => [
                'merchant_id' => $this->config('credential1'),
                'environment' => $this->config('test_mode') ? 'sandbox' : 'production',
                'reference_transactions' => in_array($this->config('reference_transactions'), [null, true], true),
            ],
        ];
    }
}
