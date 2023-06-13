<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\ProvidesTokenId;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Contracts\SourceTokens;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Str;

class GivecloudTestGateway extends AbstractGateway implements
    Gateway,
    CaptureTokens,
    SourceTokens,
    ProvidesTokenId,
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
        return 'givecloudtest';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Givecloud Test Gateway';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://givecloud.com';
    }

    /**
     * Check if gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
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
        if ($order->payment_type === ContributionPaymentType::WALLET_PAY) {
            $this->populateWithFakeBillingData($order);
        }

        return new UrlResponse($returnUrl);
    }

    public function getTokenId(): string
    {
        if ($this->request()->input('type') === 'bank_account') {
            $tokenData = [
                'ach_account' => $this->request()->input('last4'),
                'ach_type' => $this->request()->input('account_type'),
                'ach_entity' => $this->request()->input('account_holder_type'),
                'ach_routing' => $this->request()->input('routing_number'),
                'source_token' => 'ba_' . Str::random(12),
            ];
        } else {
            $walletPay = $this->request()->input('type') === ContributionPaymentType::WALLET_PAY;
            $applePaySession = (bool) $this->request()->input('apple_pay_session');

            $tokenData = [
                'account_type' => $this->request()->input('brand'),
                'cc_number' => $this->request()->input('last4'),
                'cc_exp' => $this->request()->input('expiry'),
                'cc_wallet' => $walletPay ? ($applePaySession ? 'apple_pay' : 'google_pay') : null,
                'source_token' => 'card_' . Str::random(12),
            ];
        }

        $token = 'tok_' . Str::random(12);

        cache()->put("givecloudtest.{$token}", $tokenData, now()->addMinutes(5));

        return $token;
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        $tokenData = cache()->pull(
            'givecloudtest.' . $this->request()->input('token')
        );

        if (empty($tokenData)) {
            throw new MessageException('The token provided is invalid.');
        }

        $res = $this->createTransactionResponseForLast4($tokenData['ach_account'] ?? $tokenData['cc_number'] ?? null);
        $res->merge($tokenData, ['transaction_id' => 'ch_' . Str::random(12)]);

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
        $contribution = $options->contribution ?? null;

        if ($this->request()->input('payment_type') === ContributionPaymentType::WALLET_PAY) {
            $this->populateWithFakeBillingData($contribution, $paymentMethod);

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

        return new UrlResponse($returnUrl);
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
        $tokenData = cache()->pull(
            'givecloudtest.' . $this->request()->input('token')
        );

        if (empty($tokenData)) {
            throw new MessageException('The token provided is invalid.');
        }

        return $this->createTransactionResponse(array_merge([
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
        ], $tokenData));
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
        $res = TransactionResponse::fromPaymentMethod($paymentMethod, [
            'transaction_id' => 'ch_' . Str::random(12),
        ]);

        $res->merge($this->createTransactionResponseForLast4($paymentMethod->account_last_four)->toArray());

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
        return $this->createTransactionResponse([
            'completed' => true,
            'response' => 'succeeded',
            'response_text' => 'Refund has been approved.',
            'transaction_id' => 're_' . Str::random(12),
        ]);
    }

    private function populateWithFakeBillingData(?Order $contribution = null, ?PaymentMethod $paymentMethod = null): void
    {
        $data = [
            'first_name' => $firstName = collect(['Adam', 'Annie', 'Dan', 'Josh', 'Sarah', 'Tim'])->random(),
            'last_name' => $lastName = collect(['Carlton', 'Fletcher', 'Knights', 'McQueen', 'Rice'])->random(),
            'email' => sprintf('%s%d@example.com', strtolower($firstName[0] . $lastName), random_int(1955, 2022)),
            'address_1' => collect([
                collect([123, 142, 325, 653])->random(),
                collect(['Campbell', 'Hawthorn', 'Pioneer', 'Quarry', 'Royalty', 'Willow'])->random(),
                collect(['Ave', 'Lane', 'Rd', 'St', 'Way'])->random(),
            ])->implode(' '),
            'city' => collect(['Alameda', 'Anaheim', 'Brentwood', 'Coachella', 'Fremont'])->random(),
            'province_code' => 'CA',
            'zip' => '90210',
            'country_code' => 'US',
        ];

        if ($contribution) {
            $contribution->billing_first_name = $data['first_name'];
            $contribution->billing_last_name = $data['last_name'];
            $contribution->billingemail = $data['email'];
            $contribution->billingaddress1 = $data['address_1'];
            $contribution->billingcity = $data['city'];
            $contribution->billingstate = $data['province_code'];
            $contribution->billingzip = $data['zip'];
            $contribution->billingcountry = $data['country_code'];
            $contribution->save();
        }

        if ($paymentMethod) {
            $paymentMethod->billing_first_name = $data['first_name'];
            $paymentMethod->billing_last_name = $data['last_name'];
            $paymentMethod->billing_email = $data['email'];
            $paymentMethod->billing_address1 = $data['address_1'];
            $paymentMethod->billing_city = $data['city'];
            $paymentMethod->billing_state = $data['province_code'];
            $paymentMethod->billing_postal = $data['zip'];
            $paymentMethod->billing_country = $data['country_code'];
            $paymentMethod->save();
        }
    }

    /**
     * Provides specific response data for testing based on the
     * last 4 digits of the payment method.
     *
     * Implements a subset of the testing number utilitzed by Stripe
     *
     * @see https://stripe.com/docs/testing
     */
    private function createTransactionResponseForLast4(?string $last4): TransactionResponse
    {
        switch ($last4) {
            case '0028':
                return $this->createTransactionResponse([
                    'completed' => true,
                    'response' => '1',
                    'response_text' => 'APPROVED',
                    'avs_code' => 'Z',
                    'cvv_code' => 'Y',
                ]);

            case '0036':
                return $this->createTransactionResponse([
                    'completed' => false,
                    'response' => '2',
                    'response_text' => 'The ZIP/postal code is incorrect.',
                    'avs_code' => 'N',
                    'cvv_code' => 'Y',
                ]);

            case '0127':
                return $this->createTransactionResponse([
                    'completed' => false,
                    'response' => '2',
                    'response_text' => 'The CVC number is incorrect.',
                    'cvv_code' => 'D',
                ]);

            case '9995':
                return $this->createTransactionResponse([
                    'completed' => false,
                    'response' => '2',
                    'response_text' => 'The card has insufficient funds to complete the purchase.',
                    'avs_code' => 'Y',
                    'cvv_code' => 'Y',
                ]);

            case '9987':
                return $this->createTransactionResponse([
                    'completed' => false,
                    'response' => '2',
                    'response_text' => 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).',
                    'avs_code' => 'Y',
                    'cvv_code' => 'Y',
                ]);

            default:
                return $this->createTransactionResponse([
                    'completed' => true,
                    'response' => '1',
                    'response_text' => 'APPROVED',
                    'avs_code' => 'Y',
                    'cvv_code' => 'Y',
                ]);
        }
    }
}
