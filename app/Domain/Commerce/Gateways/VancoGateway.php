<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\JsonpResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;

class VancoGateway extends AbstractGateway implements
    Gateway,
    SourceTokens,
    Refunds,
    PartialRefunds
{
    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'vanco';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Vanco Payment Solutions';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.vancopayments.com';
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
        // Vanco barfs with a "Field Contains Invalid Characters" error
        // if the return URL contains a query string
        $returnUrl = Str::before($returnUrl, '?');

        $nvpvar = [
            'requesttype' => 'efttransparentredirect',
            'requestid' => $this->generateRequestId(),
            'clientid' => $this->config('client_id'),
            'urltoredirect' => $returnUrl,
            'isdebitcardonly' => 'No',
        ];

        if (isset($paymentMethod->member->vanco_customer_ref)) {
            $nvpvar['customerref'] = $paymentMethod->member->vanco_customer_ref;
        }

        $data = [
            'sessionid' => $this->getSessionId(),
            'nvpvar' => $this->encryptNvp($nvpvar),
            'newcustomer' => 'false',
            'name' => "{$paymentMethod->billing_last_name}, {$paymentMethod->billing_first_name}",
            'email' => $paymentMethod->billing_email,
            'billingaddr1' => $paymentMethod->billing_address1,
            'billingaddr2' => $paymentMethod->billing_address2,
            'billingcity' => $paymentMethod->billing_city,
            'billingstate' => $paymentMethod->billing_state,
            'billingzip' => $paymentMethod->billing_postal,
        ];

        if ($paymentMethod->billing_country !== 'US') {
            $data['billingcountrycode'] = $paymentMethod->billing_country;
        }

        return new JsonpResponse($this->getEndpoint(), $data);
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
        // generate response text using combination of the
        // error list and the auth description
        if ($this->request()->input('errorlist')) {
            $errorlist = $this->getErrorMessages($this->request()->input('errorlist'));

            if (Arr::has($errorlist, '434') && $this->request()->input('ccauthdesc')) {
                $response = $this->request()->input('ccauthdesc');
            } else {
                $response = implode('. ', $errorlist);
            }
        } else {
            $response = 'AP';
        }

        // create standardized response
        $transaction = [
            'completed' => ($response === 'AP'),
            'response' => ($response === 'AP') ? '1' : '2',
            'response_text' => $response,
            'transaction_id' => (string) $this->request()->input('requestid'),
            'cc_number' => '',
            'cc_exp' => '',
            'ach_account' => '',
            'ach_routing' => '',
            'ach_type' => '',
            'ach_entity' => '',
            'customer_ref' => $this->request()->input('customerref') ?: '',
            'source_token' => $this->request()->input('paymentmethodref') ?: '',
        ];

        if ($this->request()->input('accounttype') === 'CC') {
            $transaction['account_type'] = str_replace('Mastercard', 'MasterCard', ucwords($this->request()->input('visamctype')));
            $transaction['cc_number'] = $this->request()->input('last4');
            $transaction['cc_exp'] = $this->request()->input('expmonth') . $this->request()->input('expyear');
        } else {
            $transaction['ach_account'] = $this->request()->input('last4');
            $transaction['ach_type'] = $this->request()->input('accounttype') === 'C' ? 'checking' : 'savings';
        }

        $res = $this->createTransactionResponse($transaction);

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
            'sessionid' => $this->getSessionId(),
            'nvpvar' => [
                'requesttype' => 'eftaddcompletetransaction',
                'requestid' => $this->generateRequestId(),
                'clientid' => $this->config('client_id'),
                'customerref' => $paymentMethod->member->vanco_customer_ref,
                'paymentmethodref' => $paymentMethod->token,
                'isdebitcardonly' => 'No',
                'amount' => number_format($amount->amount, 2, '.', ''),
                'startdate' => '0000-00-00',
                'frequencycode' => 'O',
                'transactiontypecode' => 'WEB',
            ],
        ];

        try {
            $res = $this->doNvpRequest($data);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        // generate response text using combination of the
        // error list and the auth description
        if (Arr::has($res, 'errorlist')) {
            $errorlist = $this->getErrorMessages($res['errorlist']);

            if (Arr::has($errorlist, '434')) {
                $response = Arr::get($res, 'ccauthdesc');
            } else {
                $response = implode('. ', $errorlist);
            }
        } else {
            $response = 'AP';
        }

        $transaction = [
            'completed' => ($response === 'AP'),
            'response' => ($response === 'AP') ? '1' : '2',
            'response_text' => $response,
            'avs_code' => (string) Arr::get($res, 'ccavsresp'),
            'cvv_code' => (string) Arr::get($res, 'cccvvresp'),
            'transaction_id' => (string) Arr::get($res, 'transactionref'),
            'source_token' => (string) Arr::get($res, 'paymentmethodref'),
        ];

        if ($paymentMethod->cc_expiry) {
            $transaction['cc_number'] = $paymentMethod->account_number;
            $transaction['cc_exp'] = fromUtcFormat($paymentMethod->cc_expiry, 'my');
        }

        if ($paymentMethod->ach_entity_type) {
            $transaction['ach_account'] = $paymentMethod->account_number;
            $transaction['ach_type'] = $paymentMethod->ach_account_type;
            $transaction['ach_entity'] = $paymentMethod->ach_entity_type;
        }

        $res = $this->createTransactionResponse($transaction);

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
        if (! $paymentMethod) {
            throw new GatewayException('Payment method is required to perform refund.');
        }

        $data = [
            'sessionid' => $this->getSessionId(),
            'nvpvar' => [
                'requesttype' => 'eftaddcredit',
                'requestid' => $this->generateRequestId(),
                'clientid' => $this->config('client_id'),
                'customerref' => $paymentMethod->member->vanco_customer_ref,
                'paymentmethodref' => $paymentMethod->token,
                'transactionref' => $transactionId,
                'contactname' => '',
                'contactphone' => '',
                'contactextension' => '',
                'reasonforcredit' => 'Refunded via Givecloud',
                'amount' => number_format($amount, 2, '.', ''),
            ],
        ];

        try {
            $res = $this->doNvpRequest($data);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $res = $this->createTransactionResponse([
            'completed' => (strtolower(Arr::get($res, 'creditrequestreceived')) === 'yes'),
            'response' => (strtolower(Arr::get($res, 'creditrequestreceived')) === 'yes') ? 'succeeded' : 'failed',
            'response_text' => (string) Arr::get($res, 'creditrequestreceived'),
            'transaction_id' => (string) Arr::get($res, 'transactionref'),
        ]);

        if ($res->isCompleted()) {
            return $res;
        }

        throw new RefundException($res);
    }

    public function getPaymentMethod(string $customerRef, string $paymentMethodRef): array
    {
        $data = [
            'Auth' => [
                'RequestType' => 'EFTGetPaymentMethod',
                'RequestID' => $this->generateRequestId(),
                'RequestTime' => fromUtcFormat('now', 'datetime'),
                'SessionID' => $this->getWsSessionId(),
                'Version' => '2.1',
            ],
            'Request' => ['RequestVars' => [
                'ClientID' => $this->config('client_id'),
                'CustomerRef' => $customerRef,
                'PaymentMethodRef' => $paymentMethodRef,
            ]],
        ];

        try {
            $res = $this->doWsRequest($data);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res['PaymentMethods']['PaymentMethod'];
    }

    /**
     * Get a transaction.
     *
     * @param string $transactionId
     * @return array
     */
    public function getTransaction(string $transactionId)
    {
        $data = [
            'Auth' => [
                'RequestType' => 'EFTTransactionFundHistory',
                'RequestID' => $this->generateRequestId(),
                'RequestTime' => fromUtcFormat('now', 'datetime'),
                'SessionID' => $this->getWsSessionId(),
                'Version' => '2.1',
            ],
            'Request' => ['RequestVars' => [
                'ClientID' => $this->config('client_id'),
                'TransactionRef' => $transactionId,
            ]],
        ];

        try {
            $res = $this->doWsRequest($data);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get transactions for a customer.
     *
     * @param string $customerId
     * @return array
     */
    public function getCustomerTransactions(string $customerId, $fromDate = null)
    {
        $data = [
            'Auth' => [
                'RequestType' => 'EFTTransactionFundHistory',
                'RequestID' => $this->generateRequestId(),
                'RequestTime' => fromUtcFormat('now', 'datetime'),
                'SessionID' => $this->getWsSessionId(),
                'Version' => '2.1',
            ],
            'Request' => ['RequestVars' => [
                'ClientID' => $this->config('client_id'),
                'CustomerRef' => $customerId,
            ]],
        ];

        if ($fromDate) {
            $data['Request']['RequestVars']['FromDate'] = fromUtcFormat($fromDate, 'Y-m-d');
        }

        try {
            $res = $this->doWsRequest($data);
        } catch (Throwable $e) {
            throw new GatewayException($e->getMessage(), $e->getCode(), $e);
        }

        return $res;
    }

    /**
     * Get Session ID.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        $key = 'vanco-sessionid-' . sha1($this->config('credential1'));

        if (cache($key)) {
            return cache($key);
        }

        $res = $this->doNvpRequest([
            'nvpvar' => '',
            'requesttype' => 'login',
            'userid' => $this->config('userid'),
            'password' => $this->config('password'),
            'requestid' => $this->generateRequestId(),
        ]);

        if (empty($res['sessionid'])) {
            throw new GatewayException('Unable to obtain Session ID');
        }

        // cache the session ID for 23 hours
        cache([$key => $res['sessionid']], now()->addHours(23));

        return $res['sessionid'];
    }

    /**
     * Get web services Session ID.
     *
     * @return string
     */
    public function getWsSessionId(): string
    {
        $key = 'vanco-ws-sessionid-' . sha1($this->config('credential1'));

        if (cache($key)) {
            return cache($key);
        }

        $res = $this->doWsRequest([
            'Auth' => [
                'RequestType' => 'Login',
                'RequestID' => $this->generateRequestId(),
                'RequestTime' => fromUtcFormat('now', 'datetime'),
                'Version' => '2.1',
            ],
            'Request' => ['RequestVars' => [
                'UserID' => $this->config('userid'),
                'Password' => $this->config('password'),
            ]],
        ]);

        if (empty($res['SessionID'])) {
            throw new GatewayException('Unable to obtain Session ID');
        }

        // cache the session ID for 23 hours
        cache([$key => $res['SessionID']], now()->addHours(23));

        return $res['SessionID'];
    }

    /**
     * A unique alphanumeric identifier for the request.
     *
     * Used to detect duplicate requests and to identify what request a
     * response is referring to.
     *
     * @return string
     */
    protected function generateRequestId(): string
    {
        return str_replace('-', '', Str::uuid());
    }

    /**
     * Get the endpoint.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        if ($this->config('test_mode')) {
            return 'https://uat.vancopayments.com/cgi-bin/wsnvp.vps';
        }

        return 'https://myvanco.vancopayments.com/cgi-bin/wsnvp.vps';
    }

    /**
     * Get the web services endpoint.
     *
     * @return string
     */
    protected function getWsEndpoint()
    {
        if ($this->config('test_mode')) {
            return 'https://uat.vancopayments.com/cgi-bin/ws2.vps';
        }

        return 'https://myvanco.vancopayments.com/cgi-bin/ws2.vps';
    }

    /**
     * Perform an web services request.
     *
     * @param array $params
     * @return array
     */
    protected function doWsRequest(array $params): array
    {
        $xml = (new ArrayToXml($params, 'VancoWS'))->toDom();
        $xml = $xml->saveXML($xml->documentElement);

        $res = Http::withOptions([
            'allow_redirects' => true,
            'verify' => true,
        ])->withBody($xml, 'text/xml')
            ->post($this->getWsEndpoint())->throw();

        $res = $res->xml();
        $res = json_decode(json_encode($res), true);

        if ($res['Response']['Errors'] ?? false) {
            throw new GatewayException(
                data_get($res, 'Response.Errors.Error.ErrorDescription'),
                data_get($res, 'Response.Errors.Error.ErrorCode')
            );
        }

        return $res['Response'];
    }

    /**
     * Perform an Nvp request.
     *
     * Provides transparent encryption for Nvp variables during
     * the POST request to Vanco.
     *
     * @param array $params
     * @return array
     */
    protected function doNvpRequest(array $params): array
    {
        // automatically encrypt and encode Nvp variables
        if (array_key_exists('nvpvar', $params)) {
            $params['nvpvar'] = $this->encryptNvp($params['nvpvar']);
        }

        // the login request doesn't use application/x-www-form-urlencoded encoding
        // and must be formatted in a similar but slightly different way
        if (array_key_exists('requesttype', $params) && $params['requesttype'] === 'login') {
            unset($params['nvpvar']);

            $body = 'nvpvar=' . http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        } else {
            $body = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        }

        $res = Http::withOptions([
            'allow_redirects' => true,
            'verify' => true,
        ])->withBody($body, 'application/x-www-form-urlencoded')
            ->post($this->getEndpoint())->throw();

        // convert from URL encoded string
        $result = [];
        parse_str((string) $res->getBody(), $result);

        // check the result for an error list
        if (empty($result['errorlist']) === false) {
            $errorlist = $this->getErrorMessages($result['errorlist']);
            throw new GatewayException(implode("\n", $errorlist));
        }

        // check for the presense of Nvp variables
        if (empty($result['nvpvar'])) {
            return $result;
        }

        // decode and decrypt Nvp variables
        return $this->decryptNvp($result['nvpvar']);
    }

    /**
     * Encrypt and encode NVP data.
     * https://devwiki.vancopayments.com/doku.php?id=nvp:envpvar
     *
     * @param array|string $data
     * @return string
     */
    protected function encryptNvp($data): string
    {
        // convert to URL encoded string
        if (is_array($data)) {
            $data = http_build_query($data, null, '&', PHP_QUERY_RFC3986);
        }

        if (empty($data)) {
            return '';
        }

        // compress
        $data = gzdeflate($data);

        // data needs to be padding since it's being encryped using
        // AES ECB in block cipher mode
        $data = $data . str_repeat(' ', strlen($data) % 16);

        // mcrypt was deprected in PHP71 and removed in PHP72 using phpseclib polyfill
        $data = phpseclib_mcrypt_encrypt('rijndael-128', $this->config('encryption_key'), $data, 'ecb');

        // url-safe base64 encode encrypted data
        return rtrim(strtr(base64_encode($data), '+/', '-_'));
    }

    /**
     * Decrypt and decode NVP data.
     * https://devwiki.vancopayments.com/doku.php?id=nvp:envpvar
     *
     * @param string $data
     * @return array
     */
    protected function decryptNvp(string $data): array
    {
        if (empty($data)) {
            return [];
        }

        // url-safe base64 decode encrypted data
        $data = base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));

        // mcrypt was deprected in PHP71 and removed in PHP72 using phpseclib polyfill
        $data = phpseclib_mcrypt_decrypt('rijndael-128', $this->config('encryption_key'), $data, 'ecb');

        // decompress
        $data = gzinflate($data);

        // convert from URL encoded string
        $result = [];
        parse_str($data, $result);

        return $result;
    }

    /**
     * Return the messages for a given set of error codes.
     *
     * @param string $codes
     * @return array
     */
    protected function getErrorMessages(string $codes): array
    {
        $codes = explode(',', $codes);

        $errors = [];
        foreach ($codes as $code) {
            $errors[$code] = $this->getErrorMessage($code);
        }

        return $errors;
    }

    /**
     * Return the message for a given error code.
     *
     * @param int $code
     * @return string
     */
    protected function getErrorMessage($code)
    {
        $errorMessages = [
            10 => 'Invalid UserID/password combination',
            11 => 'Session expired',
            25 => 'All default address fields are required',
            32 => 'Name is required',
            33 => 'Unknown bank/bankpk',
            34 => 'Valid PaymentType is required',
            35 => 'Valid Routing Number Is Required',
            63 => 'Invalid StartDate',
            65 => 'Specified fund reference is not valid.',
            66 => 'Invalid End Date',
            67 => 'Transaction must have at least one transaction fund.',
            68 => 'User is Inactive',
            69 => 'Expiration Date Invalid',
            70 => 'Account Type must be C, S for ACH and must be blank for Credit Card',
            71 => 'Class Code must be PPD, CCD, TEL, WEB, RCK or blank.',
            72 => 'Missing Client Data: Client ID',
            73 => 'Missing Customer Data: Customer ID or Name or Last Name & First Name',
            74 => 'PaymentMethod is required.',
            76 => 'Transaction Type is required',
            77 => 'Missing Credit Card Data: Card # or Expiration Date',
            78 => 'Missing ACH Data: Routing # or Account #',
            79 => 'Missing Transaction Data: Amount or Start Date',
            80 => 'Account Number has invalid characters in it',
            81 => 'Account Number has too many characters in it',
            82 => 'Customer name required',
            83 => 'Customer ID has not been set',
            86 => "NextSettlement does not fall in today's processing dates",
            87 => 'Invalid FrequencyPK',
            88 => 'Processed yesterday',
            89 => 'Duplicate Transaction (matches another with PaymentMethod and NextSettlement)',
            91 => 'Dollar amount for transaction is over the allowed limit',
            92 => 'Invalid client reference occurred. - Transaction WILL NOT process',
            94 => 'Customer ID already exists for this client',
            95 => 'Payment Method is missing Account Number',
            101 => 'Dollar Amount for transaction cannot be negative',
            102 => "Updated transaction's dollar amount violates amount limit",
            105 => 'PaymentMethod Date not valid yet.',
            125 => 'Email Address is required.',
            127 => 'User Is Not Proofed',
            134 => 'User does not have access to specified client.',
            157 => 'Client ID is required',
            158 => 'Specified Client is invalid',
            159 => 'Customer ID required',
            160 => 'Customer ID is already in use',
            161 => 'Customer name required',
            162 => 'Invalid Date Format',
            163 => 'Transaction Type is required',
            164 => 'Transaction Type is invalid',
            165 => 'Fund required',
            166 => 'Customer Required',
            167 => 'Payment Method Not Found',
            168 => 'Amount Required',
            169 => 'Amount Exceeds Limit. Set up manually.',
            170 => 'Start Date Required',
            171 => 'Invalid Start Date',
            172 => 'End Date earlier than Start Date',
            173 => 'Cannot Prenote a Credit Card',
            174 => 'Cannot Prenote processed account',
            175 => 'Transaction pending for Prenote account',
            176 => 'Invalid Account Type',
            177 => 'Account Number Required',
            178 => 'Invalid Routing Number',
            179 => "Client doesn't accept Credit Card Transactions",
            180 => 'Client is in test mode for Credit Cards',
            181 => 'Client is cancelled for Credit Cards',
            182 => 'Name on Credit Card is Required',
            183 => 'Invalid Expiration Date',
            184 => 'Complete Billing Address is Required',
            185 => 'Fund ID is Required',
            187 => 'Fund Name Required',
            195 => 'Transaction Cannot Be Deleted',
            196 => 'Recurring Telephone Entry Transaction NOT Allowed',
            197 => 'Cannot delete default fund',
            198 => 'Invalid State',
            199 => 'Start Date Is Later Than Expiration date',
            201 => 'Frequency Required',
            202 => 'Account Cannot Be Deleted, Active Transaction Exists',
            203 => 'Client Does Not Accept ACH Transactions',
            204 => 'Duplicate Transaction',
            210 => 'Recurring Credits NOT Allowed',
            211 => 'ONHold/Cancelled Customer',
            217 => 'End Date Cannot Be Earlier Than The Last Settlement Date',
            218 => 'Fund ID Cannot Be W, P, T, or C',
            223 => 'Customer ID not on file',
            224 => 'Credit Card Credits NOT Allowed - Must Be Refunded',
            231 => 'Customer Not Found For Client',
            232 => 'Invalid Account Number',
            233 => 'Invalid Country Code',
            234 => 'Transactions Are Not Allow From This Country',
            242 => 'Valid State Required',
            251 => 'Transactionref Required',
            284 => 'User Has Been Deleted',
            286 => 'Client not set up for International Credit Card Processing',
            296 => 'Client Is Cancelled',
            328 => 'Credit Pending - Cancel Date cannot be earlier than Today',
            329 => 'Credit Pending - Account cannot be placed on hold until Tomorrow',
            341 => 'Cancel Date Cannot be Greater Than Today',
            344 => 'Phone Number Must be 10 Digits Long',
            365 => 'Invalid Email Address',
            378 => 'Invalid Loginkey',
            379 => 'Requesttype Unavailable',
            380 => 'Invalid Sessionid',
            381 => 'Invalid Clientid for Session',
            383 => 'Internal Handler Error. Contact Vanco Payment Solutions.',
            384 => 'Invalid Requestid',
            385 => 'Duplicate Requestid',
            390 => 'Requesttype Not Authorized For User',
            391 => 'Requesttype Not Authorized For Client',
            392 => 'Invalid Value Format',
            393 => 'Blocked IP',
            395 => 'Transactions cannot be processed on Weekends',
            404 => 'Invalid Date',
            410 => 'Credits Cannot Be WEB or TEL',
            420 => 'Transaction Not Found',
            431 => 'Client Does Not Accept International Credit Cards',
            432 => 'Can not process credit card',
            434 => 'Credit Card Processor Error',
            445 => 'Cancel Date Cannot Be Prior to the Last Settlement Date',
            446 => 'End Date Cannot Be In The Past',
            447 => 'Masked Account',
            469 => 'Card Number Not Allowed',
            474 => 'MasterCard Not Accepted',
            475 => 'Visa Not Accepted',
            476 => 'American Express Not Accepted',
            477 => 'Discover Not Accepted',
            478 => 'Invalid Account Number',
            485 => 'Client Must Have One Default Fund',
            489 => 'Customer ID Exceeds 15 Characters',
            490 => 'Too Many Results, Please Narrow Search',
            495 => 'Field Contains Invalid Characters',
            496 => 'Field contains Too Many Characters',
            497 => 'Invalid Zip Code',
            498 => 'Invalid City',
            499 => 'Invalid Canadian Postal Code',
            500 => 'Invalid Canadian Province',
            506 => 'User Not Found',
            511 => 'Amount Exceeds Limit',
            512 => 'Client Not Set Up For Credit Card Processing',
            515 => 'Transaction Already Refunded',
            516 => 'Can Not Refund a Refund',
            517 => 'Invalid Customer',
            518 => 'Invalid Payment Method',
            519 => 'Client Only Accepts Debit Cards',
            520 => 'Transaction Max for Account Number Reached',
            521 => 'Thirty Day Max for Client Reached',
            522 => 'Service Not Allowed from Outside the Company (if using IP whitelist)',
            523 => 'Invalid Login Request',
            527 => 'Change in account/routing# or type',
            535 => 'SSN Required',
            549 => 'CVV2 Number is Required',
            550 => 'Invalid Client ID',
            556 => 'Invalid Banking Information',
            569 => 'Please Contact This Organization for Assistance with Processing This Transaction',
            570 => 'City Required',
            571 => 'Zip Code Required',
            572 => 'Canadian Province Required',
            573 => 'Canadian Postal Code Required',
            574 => 'Country Code Required',
            578 => 'Unable to Read Card Information. Please Click “Click to Swipe” Button and Try Again.',
            610 => 'Invalid Banking Information. Previous Notification of Change Received for this Account',
            629 => 'Invalid CVV2',
            641 => 'Fund ID Not Found',
            642 => 'Request Amount Exceeds Total Transaction Amount',
            643 => 'Phone Extension Required',
            645 => 'Invalid Zip Code',
            652 => 'Invalid SSN',
            653 => 'SSN Required',
            657 => 'Billing State Required',
            659 => 'Phone Number Required',
            663 => 'Version Not Supported',
            665 => 'Invalid Billing Address',
            666 => 'Customer Not On Hold',
            667 => 'Account number for fund is invalid',
            678 => 'Password Expired',
            687 => 'Fund Name is currently in use. Please choose another name. If you would like to use this Fund Name, go to the other fund and change the Fund Name to something different.',
            688 => 'Fund ID is currently in use. Please choose another number. If you would like to use this Fund ID, go to the other fund and change the Fund ID to something different.',
            705 => 'Please Limit Your Date Range To 30 Days',
            706 => 'Last Digits of Account Number Required',
            721 => 'MS Transaction Amount Cannot Be Greater Than $50,000.',
            725 => 'User ID is for Web Services Only',
            730 => 'Start Date Required',
            734 => 'Date Range Cannot Be Greater Than One Year',
            764 => 'Start Date Cannot Occur In The Past',
            800 => 'The CustomerID Does Not Match The Given CustomerRef',
            801 => 'Default Payment Method Not Found',
            838 => 'Transaction Cannot Be Processed. Please contact your organization.',
            842 => 'Invalid Pin',
            844 => 'Phone Number Must be 10 Digits Long',
            850 => 'Invalid Authentication Signature',
            857 => 'Fund Name Can Not Be Greater Than 30 Characters',
            858 => 'Fund ID Can Not Be Greater Than 20 Characters',
            859 => 'Customer Is Unproofed',
            862 => 'Invalid Start Date',
            866 => 'Invalid Track Data',
            956 => 'Amount Must Be Greater Than $0.00',
            960 => 'Date of Birth Required',
            963 => 'Missing Field',
            973 => 'No match found for these credentials.',
            974 => 'Recurring Return Fee Not Allowed',
            992 => 'No Transaction Returned Within the Past 45 Days',
            993 => 'Return Fee Must Be Collected Within 45 Days',
            994 => 'Return Fee Is Greater Than the Return Fee Allowed',
            1005 => 'Phone Extension Must Be All Digits',
            1008 => 'We are sorry. This organization does not accept online credit card transactions. Please try again using a debit card.',
            1047 => 'Invalid nvpvar variables',
            1054 => 'Invalid Debit Card Only field',
            1059 => 'No Matching Customer Found',
            1067 => 'Invalid Original Request ID',
            1070 => 'Transaction Cannot Be Voided',
            1073 => 'Transaction Processed More Than 25 Minutes Ago',
            1127 => 'Declined - Tran Not Permitted',
            1128 => 'Unable To Process, Please Try Again',
            1150 => 'Cannot determine which fund(s) to credit',
        ];

        return $errorMessages[$code] ?? "Unknown error (Code: $code)";
    }
}
