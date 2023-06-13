<?php

namespace Ds\Services;

use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\TransactionException;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Ds\Models\Refund;
use Illuminate\Support\Str;
use Throwable;

class PaymentService
{
    /**
     * Make a Payment using a Payment Method.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param float $amount
     * @param string|null $currency
     * @param string|null $description
     * @return \Ds\Models\Payment
     */
    public function makePayment(
        PaymentMethod $paymentMethod,
        float $amount,
        float $dccAmount,
        string $currency = null,
        string $description = null,
        ?string $platformFeeType = null
    ): Payment {
        if (empty($amount)) {
            throw new MessageException("Can't create a payment for zero dollars.");
        }

        if ($amount < 0) {
            throw new MessageException("Can't make negative payments.");
        }

        $payment = new Payment;
        $payment->type = $paymentMethod->ach_account_type ? 'bank' : 'card';
        $payment->livemode = ! $paymentMethod->paymentProvider->test_mode;
        $payment->status = 'pending';
        $payment->amount = $amount;
        $payment->amount_refunded = 0;
        $payment->currency = $currency ?? sys_get('dpo_currency');
        $payment->paid = false;
        $payment->description = $description ?? 'Manual charge';
        $payment->source_account_id = $paymentMethod->member_id;
        $payment->source_payment_method_id = $paymentMethod->id;
        $payment->gateway_type = $paymentMethod->paymentProvider->provider;
        $payment->gateway_source = $paymentMethod->token;
        $payment->platform_fee_type = $platformFeeType;

        if ($paymentMethod->paymentProvider->provider === 'paypalexpress') {
            $payment->type = 'paypal';
        } elseif ($paymentMethod->paymentProvider->provider === 'gocardless') {
            $payment->type = 'bank';
        }

        if ($payment->type === 'card') {
            $payment->card_funding = 'unknown';
            $payment->card_brand = ($paymentMethod->account_type ?? null) ?: null;
            $payment->card_fingerprint = ($paymentMethod->fingerprint ?? null) ?: null;
            $payment->card_last4 = ($paymentMethod->account_last_four ?? null) ?: null;
            $payment->card_exp_month = fromUtcFormat($paymentMethod->cc_expiry ?? null, 'm') ?: null;
            $payment->card_exp_year = fromUtcFormat($paymentMethod->cc_expiry ?? null, 'Y') ?: null;
            $payment->card_wallet = ($paymentMethod->cc_wallet ?? null) ?: null;
            $payment->card_country = ($paymentMethod->billing_country ?? null) ?: null;
            $payment->card_name = trim("{$paymentMethod->billing_first_name} {$paymentMethod->billing_last_name}") ?: null;
            $payment->card_address_line1 = ($paymentMethod->billing_address1 ?? null) ?: null;
            $payment->card_address_line2 = ($paymentMethod->billing_address2 ?? null) ?: null;
            $payment->card_address_city = ($paymentMethod->billing_city ?? null) ?: null;
            $payment->card_address_state = ($paymentMethod->billing_state ?? null) ?: null;
            $payment->card_address_zip = ($paymentMethod->billing_postal ?? null) ?: null;
            $payment->card_address_country = ($paymentMethod->billing_country ?? null) ?: null;
        } elseif ($payment->type === 'bank') {
            $payment->bank_name = ($paymentMethod->ach_bank_name ?? null) ?: null;
            $payment->bank_fingerprint = ($paymentMethod->fingerprint ?? null) ?: null;
            $payment->bank_last4 = ($paymentMethod->account_last_four ?? null) ?: null;
            $payment->bank_account_holder_name = trim("{$paymentMethod->billing_first_name} {$paymentMethod->billing_last_name}") ?: null;
            $payment->bank_account_holder_type = ($paymentMethod->ach_entity_type ?? null) ?: null;
            $payment->bank_routing_number = ($paymentMethod->ach_routing ?? null) ?: null;
        }

        $handleRes = function (TransactionResponse $res) use ($payment) {
            try {
                $payment->ip_address = $res->getIpAddress() ?: null;
                $payment->reference_number = $res->getTransactionId() ?? null;
                $payment->application_fee_billing = (bool) sys_get('dcc_stripe_application_fee_billing');
                $payment->application_fee_amount = $res->getApplicationFeeAmount();
                $payment->stripe_payment_intent = $res->getStripePaymentIntent();
                $payment->payment_audit_log = 'json:' . json_encode($res);

                $this->handleCvvResponse($payment, $res->getCVV2Code() ?? '');
                $this->handleAvsResponse($payment, $res->getAVSCode() ?? '');
                $this->handleResponseText($payment, $res->getResponseText() ?? '');
            } catch (Throwable $exception) {
                $this->handleResponseText($payment, $exception->getMessage());
            }
        };

        try {
            $handleRes($paymentMethod->charge(
                $payment->amount,
                $payment->currency,
                new SourceTokenChargeOptions([
                    'dccAmount' => $dccAmount,
                    'initiatedBy' => CredentialOnFileInitiatedBy::MERCHANT,
                    'recurring' => true,
                ]),
            ));
        } catch (TransactionException $exception) {
            if ($res = $exception->getResponse()) {
                $handleRes($res);
            } else {
                $this->handleResponseText($payment, $exception->getMessage());
            }
        } catch (Throwable $exception) {
            $this->handleResponseText($payment, $exception->getMessage());
        }

        if ($payment->type === 'bank' && $payment->status === 'succeeded') {
            $payment->status = 'pending';
        }

        if ($payment->status !== 'failed') {
            $payment->captured = true;
            $payment->captured_at = now();
        }

        if ($payment->status === 'succeeded' && $payment->captured) {
            $payment->paid = true;
        }

        $payment->save();

        if (optional($payment->paymentProvider)->provider === 'stripe' && $payment->captured) {
            $payment->paymentProvider->updateStripeFeesForPayment($payment);
        }

        return $payment;
    }

    /**
     * Create a Payment from an Order.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Models\Payment|null
     */
    public function createPaymentFromOrder(Order $order): ?Payment
    {
        if (empty($order->totalamount) || $order->totalamount < 0) {
            return null;
        }

        $payment = new Payment;
        $payment->livemode = ! $order->is_test;
        $payment->status = 'succeeded';
        $payment->amount = $order->totalamount;
        $payment->amount_refunded = 0;
        $payment->currency = $order->currency_code;
        $payment->paid = false;
        $payment->reference_number = $order->confirmationnumber ?: null;
        $payment->description = "Payment for Contribution #{$order->client_uuid}";
        $payment->source_account_id = $order->member_id;
        $payment->source_payment_method_id = $order->paymentMethod->id ?? null;
        $payment->gateway_source = $order->vault_id ?: $order->paymentMethod->token ?? null;
        $payment->payment_audit_log = $order->response_text ?: null;

        switch (strtolower($order->payment_type_formatted)) {
            case 'ach':         $payment->type = 'bank'; $payment->gateway_type = 'safesave'; break;
            case 'cash':        $payment->type = 'cash'; $payment->gateway_type = 'offline'; break;
            case 'cc':          $payment->type = 'card'; $payment->gateway_type = 'safesave'; break;
            case 'check':       $payment->type = 'cheque'; $payment->gateway_type = 'offline'; break;
            case 'credit card': $payment->type = 'card'; $payment->gateway_type = 'safesave'; break;
            case 'other':       $payment->type = 'unknown'; $payment->gateway_type = 'offline'; break;
            case 'paypal':      $payment->type = 'paypal'; $payment->gateway_type = 'paypalexpress'; break;
            default:
                if ($this->validCardBrand($order->billingcardtype)) {
                    $payment->gateway_type = 'safesave';
                    $payment->type = 'card';
                } elseif ($this->validBankAccountHolderType($order->billingcardtype)) {
                    $payment->gateway_type = 'safesave';
                    $payment->type = 'bank';
                } elseif (strtolower($order->billingcardtype) === 'paypal') {
                    $payment->gateway_type = 'paypalexpress';
                    $payment->type = 'paypal';
                } else {
                    $payment->gateway_type = 'unknown';
                    $payment->type = 'unknown';
                }
        }

        if ($order->paymentProvider) {
            $payment->gateway_type = $order->paymentProvider->provider;

            if (Str::contains($payment->gateway_type, 'paypal')) {
                $payment->type = 'paypal';
            }
        }

        if ($payment->type === 'card') {
            $payment->card_funding = 'unknown';
            $payment->card_brand = $order->billingcardtype;
            $payment->card_last4 = $order->billingcardlastfour;
            $payment->card_exp_month = $order->billing_card_expiry_month;
            $payment->card_exp_year = $order->billing_card_expiry_year;
            $payment->card_cvc_check = 'unchecked';
            $payment->card_wallet = $order->paymentMethod->cc_wallet ?? null;
            $payment->card_country = $order->billingcountry ?: null;
            $payment->card_name = trim("{$order->billing_first_name} {$order->billing_last_name}") ?: null;
            $payment->card_address_line1 = $order->billingaddress1 ?: null;
            $payment->card_address_line1_check = 'unchecked';
            $payment->card_address_line2 = $order->billingaddress2 ?: null;
            $payment->card_address_city = $order->billingcity ?: null;
            $payment->card_address_state = $order->billingstate ?: null;
            $payment->card_address_zip = $order->billingzip ?: null;
            $payment->card_address_zip_check = 'unchecked';
            $payment->card_address_country = $order->billingcountry ?: null;

            if (strtolower($order->source) === 'import') {
                $payment->status = 'succeeded';
                $payment->outcome = 'authorized';
            } else {
                $this->handleResponseText($payment, $order->response_text);
            }
        }

        if ($payment->type === 'bank') {
            if (Str::startsWith($payment->reference_number, 'PM')) {
                $payment->gateway_type = 'gocardless';
            }

            $payment->status = 'pending';
            $payment->bank_last4 = $order->billingcardlastfour;
            $payment->bank_account_holder_name = trim("{$order->billing_first_name} {$order->billing_last_name}");
            $payment->bank_account_holder_type = $order->billingcardtype;

            if (strtolower($order->source) === 'import') {
                $payment->status = 'succeeded';
                $payment->outcome = 'authorized';
            } else {
                $this->handleResponseText($payment, $order->response_text);

                if ($payment->status === 'succeeded') {
                    $payment->status = 'pending';
                }
            }
        }

        if ($payment->type === 'cheque') {
            $payment->amount = $order->check_amt;
            $payment->cheque_number = $order->check_number;
            $payment->cheque_date = $order->check_date;
        }

        if ($payment->type === 'cash') {
            $payment->amount = $order->cash_received;
        }

        if ($payment->type === 'unknown') {
            $payment->paid = true;
            $payment->reference_number = trim($order->payment_other_reference) ?: $payment->reference_number;
            $payment->description = trim($order->payment_other_note) ?: $payment->description;
        }

        if ($payment->status !== 'failed') {
            $payment->captured = true;
            if ($payment->gateway_type === 'offline') {
                $payment->captured_at = fromUtc($order->ordered_at);
            } else {
                $payment->captured_at = fromUtc($order->confirmationdatetime);
            }
        }

        if ($payment->status === 'succeeded' && $payment->captured) {
            $payment->paid = true;
        }

        if ($payment->gateway_type === 'offline') {
            $payment->created_at = fromUtc($order->ordered_at);
            $payment->updated_at = fromUtc($order->ordered_at);
        } else {
            $payment->created_at = fromUtc($order->started_at);
            $payment->updated_at = fromUtc($order->started_at);
        }

        $payment->save();

        if ($order->is_refunded) {
            $refund = new Refund;
            $refund->status = 'succeeded';
            $refund->reference_number = $order->refunded_auth;
            $refund->amount = $order->refunded_amt;
            $refund->currency = sys_get('dpo_currency');
            $refund->reason = 'requested_by_customer';
            $refund->refunded_by_id = $order->refunded_by ?? 1;
            $refund->created_at = $order->refunded_at;
            $refund->updated_at = $order->refunded_at;

            $payment->refunds()->save($refund);
        }

        $payment->orders()->attach($order);

        return $payment;
    }

    /**
     * Does string match a recognized card brand.
     *
     * @param string|null $value
     * @return bool
     */
    public function validCardBrand(?string $value): bool
    {
        $value = preg_replace('/[^a-z]/', '', strtolower((string) $value));

        switch ($value) {
            case 'amex':            return true;
            case 'americanexpress': return true;
            case 'carteblanche':    return true;
            case 'chinaunionpay':   return true;
            case 'dinersclub':      return true;
            case 'discover':        return true;
            case 'elo':             return true;
            case 'jcb':             return true;
            case 'laser':           return true;
            case 'maestro':         return true;
            case 'mastercard':      return true;
            case 'solo':            return true;
            case 'switch':          return true;
            case 'unionpay':        return true;
            case 'visa':            return true;
            case 'visaelectron':    return true;
        }

        return false;
    }

    /**
     * Does string match a recognized bank account holder type.
     *
     * @param string|null $value
     * @return bool
     */
    public function validBankAccountHolderType(?string $value): bool
    {
        return (bool) preg_match('/(check|checking|savings|gocardless)/i', (string) $value);
    }

    /**
     * Handle a CVV/CVC Check response.
     *
     * @param \Ds\Models\Payment $payment
     * @param string|null $cvvResponse
     */
    public function handleCvvResponse(Payment $payment, ?string $cvvResponse): void
    {
        $payment->card_cvc_check = 'unchecked';

        switch ($cvvResponse) {
            case 'M': case 'pass': $payment->card_cvc_check = 'pass'; break;
            case 'N': case 'fail': $payment->card_cvc_check = 'fail'; break;
            case 'S': case 'unavailable': $payment->card_cvc_check = 'unavailable'; break;
        }
    }

    /**
     * Handle an AVS Check response.
     *
     * @param \Ds\Models\Payment $payment
     * @param string $avsResponse
     */
    public function handleAvsResponse(Payment $payment, ?string $avsResponse): void
    {
        $payment->card_address_line1_check = 'unchecked';
        $payment->card_address_zip_check = 'unchecked';

        switch ($avsResponse) {
            case 'Y':
                if ($payment->card_brand === 'Discover') {
                    $payment->card_address_line1_check = 'pass';
                    $payment->card_address_zip_check = 'fail';
                    break;
                }

                $payment->card_address_line1_check = 'pass';
                $payment->card_address_zip_check = 'pass';
                break;
            case 'A':
                if ($payment->card_brand === 'Discover') {
                    $payment->card_address_line1_check = 'pass';
                    $payment->card_address_zip_check = 'pass';
                    break;
                }

                $payment->card_address_line1_check = 'pass';
                $payment->card_address_zip_check = 'fail';
                break;
            case 'S':
            case 'R':
            case 'U':
                $payment->card_address_line1_check = 'unavailable';
                $payment->card_address_zip_check = 'unavailable';
                break;
            case 'Z':
                $payment->card_address_line1_check = 'fail';
                $payment->card_address_zip_check = 'pass';
                break;
            case 'N':
                $payment->card_address_line1_check = 'fail';
                $payment->card_address_zip_check = 'fail';
                break;
            case 'W':
                if ($payment->card_brand === 'MasterCard' || $payment->card_brand === 'Discover') {
                    $payment->card_address_line1_check = 'pass';
                    $payment->card_address_zip_check = 'pass';
                    break;
                }

                $payment->card_address_line1_check = 'unavailable';
                $payment->card_address_zip_check = 'unavailable';
                break;
            case 'T':
                if ($payment->card_brand === 'Discover') {
                    $payment->card_address_line1_check = 'fail';
                    $payment->card_address_zip_check = 'pass';
                    break;
                }

                $payment->card_address_line1_check = 'unavailable';
                $payment->card_address_zip_check = 'unavailable';
                break;
        }

        if (preg_match('/[BPCGIDMF]/', $avsResponse)) {
            if ($payment->card_brand === 'Visa') {
                switch ($avsResponse) {
                    case 'B':
                        $payment->card_address_line1_check = 'pass';
                        $payment->card_address_zip_check = 'fail';
                        break;
                    case 'P':
                        $payment->card_address_line1_check = 'fail';
                        $payment->card_address_zip_check = 'pass';
                        break;
                    case 'C':
                    case 'G':
                    case 'I':
                        $payment->card_address_line1_check = 'fail';
                        $payment->card_address_zip_check = 'fail';
                        break;
                    case 'D':
                    case 'M':
                    case 'F':
                        if ($payment->card_country === 'US') {
                            $payment->card_address_line1_check = 'fail';
                            $payment->card_address_zip_check = 'fail';
                            break;
                        }

                        $payment->card_address_line1_check = 'pass';
                        $payment->card_address_zip_check = 'pass';
                        break;
                }
            } else {
                $payment->card_address_line1_check = 'unavailable';
                $payment->card_address_zip_check = 'unavailable';
            }
        }
    }

    /**
     * Handle the response text from the gateway.
     *
     * @param \Ds\Models\Payment $payment
     * @param string $value
     */
    public function handleResponseText(Payment $payment, $value): void
    {
        $value = strtolower($value);
        $value = preg_replace('/\((Code|ID):[^\)]+\)/i', '', $value);
        $value = preg_replace('/REFID:[0-9]+/i', '', $value);
        $value = trim($value);

        if ($payment->reference_number && empty($value)) {
            $value = 'succeeded';
        }

        switch ($value) {
            case 'ap':
            case 'ap new info':
            case 'ap-new info':
            case 'approval':
            case 'approved':
            case 'completed':
            case 'ok':
            case 'no error':
            case 'success':
            case 'succeeded':
            case 'successful.':
            case 'transaction is approved':
            case 'this transaction has been approved':
            case 'this transaction has been approved.':
            case 'your order has been received. thank you for your business!':
                $payment->status = 'succeeded';
                $payment->outcome = 'authorized';

                return;
            case 'pending':
            case 'pending_submission':
            case 'settlement pending':
                $payment->status = 'pending';
                $payment->outcome = 'authorized';

                return;
            case 'decline':
            case 'declined':
            case 'decline new info':
            case 'decline try later':
            case 'decline-try later':
            case 'declined - call issuer':
            case 'declined decline check   check limit exceeded':
            case 'declined':
            case 'limit exceeded':
            case 'this transaction has been declined':
            case 'this transaction has been declined.':
            case 'your card was declined.':
            case 'your request has been declined':
            case 'your request has been declined by the issuing bank.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'card_declined';
                $payment->failure_message = 'Your card was declined.';

                return;
            case 'expired card':
            case 'the credit card has expired.':
            case 'your card has expired.':
            case 'you submitted an expired credit card number with your request.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'expired_card';
                $payment->failure_message = 'Your card has expired.';

                return;
            case 'avs rejected':
            case 'the transaction has been declined because of an avs mismatch. the address provided does not match billing address of cardholder.':
            case 'your request has failed the avs check':
            case 'your request has failed the avs check.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'incorrect_address';
                $payment->failure_message = "The card's address is incorrect.";
                $payment->card_address_line1_check = 'fail';

                return;
            case 'card issuer declined cvv':
            case 'cvv rejected':
            case 'cvv2 mismatch':
            case 'decline cvv2/cid fail':
            case 'decline-cv2 fail':
            case 'gateway rejected: cvv':
            case 'the card code is invalid.':
            case 'your request has failed the CVV check':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'incorrect_cvc';
                $payment->failure_message = "Your card's security code is incorrect.";
                $payment->card_cvc_check = 'fail';

                return;
            case 'cvv must be 3 or 4 digits':
            case 'invalid cid':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'invalid_cvc';
                $payment->failure_message = "Your card's security code is invalid.";
                $payment->card_cvc_check = 'fail';

                return;
            case 'general error':
            case 'internal error':
            case 'transaction cannot complete.':
            case "this transaction couldn't be completed.":
            case 'an error occurred while processing your card. try again in a little bit.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'processing_error';
                $payment->failure_message = 'An error occurred while processing your card. Try again in a little bit.';

                return;
            case 'pic up':
            case 'the bank has requested that you retrieve the card from the cardholder - it may be a lost or stolen card.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'pickup_card';
                $payment->failure_message = 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).';

                return;
            case 'insufficient funds':
            case 'your card has insufficient funds.':
            case 'the card has been declined due to insufficient funds.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'insufficient_funds';
                $payment->failure_message = 'Your card has insufficient funds.';

                return;
            case 'external connection error':
            case 'issuer unavail':
            case 'processor network unavailable - try again':
                $payment->status = 'failed';
                $payment->outcome = 'invalid';
                $payment->failure_code = 'issuer_not_available';
                $payment->failure_message = 'The card issuer could not be reached, so the payment could not be authorized.';

                return;
            case 'aba number must be 9 digits':
            case 'invalid aba number':
            case 'the aba code is invalid':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'account_number_invalid';
                $payment->failure_message = 'The bank account number provided is invalid.';

                return;
            case 'declined invalid routing number':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'routing_number_invalid';
                $payment->failure_message = 'The bank routing number provided is invalid.';

                return;
            case 'a duplicate transaction has been submitted.':
            case 'duplicate order':
            case 'duplicate transaction':
            case 'gateway rejected: duplicate':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'duplicate_transaction';
                $payment->failure_message = 'A transaction with identical amount and credit card information was submitted very recently.';

                return;
            case 'do not try again':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'do_not_try_again';
                $payment->failure_message = 'The card has been declined for an unknown reason.';

                return;
            case 'cardholder stopped all billing':
            case 'revk pay ordered':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'stop_payment_order';
                $payment->failure_message = 'The card has been declined for an unknown reason.';

                return;
            case 'credit card expiration date is invalid.':
            case 'invalid expiration date':
            case 'invld exp date':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'invalid_expiry_year';
                $payment->failure_message = "The card's expiration year is incorrect.";

                return;
            case 'no such issuer':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'reenter_transaction';
                $payment->failure_message = 'The payment could not be processed by the issuer for an unknown reason.';

                return;
            case 'must provide source or customer.':
                $payment->status = 'failed';
                $payment->outcome = 'invalid';
                $payment->failure_code = 'invalid_request_error';
                $payment->failure_message = 'Must provide source or customer.';

                return;
            case 'activity limit exceeded':
            case 'cardholder transaction not permitted':
            case 'cardholder\'s activity limit exceeded':
            case 'invalid transaction':
            case 'processor declined':
            case 'tran not allowed':
            case 'transaction not allowed':
            case 'transaction not permitted by issuer':
            case 'the external processing gateway has reported the transaction is unauthorized.':
            case 'unauth trans':
            case 'your request has been declined because the issuing bank does not permit the transaction for this card.':
            case 'your request has been declined by the issuing bank.':
            case 'your request has been declined by the issuing bank due to its proprietary card activity regulations.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'transaction_not_allowed';
                $payment->failure_message = 'The card has been declined for an unknown reason.';

                return;
            case 'no initial charge required.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'amount_too_small';
                $payment->failure_message = 'The specified amount is less than the minimum amount allowed.';

                return;
            case 'amount exceeds the maximum ticket allowed':
            case 'amount exceeds maximum online-authorization allowed':
            case 'transaction max for account number reached':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'amount_too_large';
                $payment->failure_message = 'The specified amount is greater than the maximum amount allowed.';

                return;
            case 'invalid username':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'account_invalid';
                $payment->failure_message = 'The username provided is invalid.';

                return;
            case 'authentication failed':
            case 'the transaction key or api key is invalid or not present.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'api_key_expired';
                $payment->failure_message = 'The API key provided is invalid or has expired.';

                return;
            case 'max pin retries':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'pin_try_exceeded';
                $payment->failure_message = 'The allowable number of PIN tries has been exceeded.';

                return;
            case 'invld merch id':
                $payment->status = 'failed';
                $payment->outcome = 'invalid';
                $payment->failure_code = 'account_invalid';
                $payment->failure_message = 'The account/merchant ID provided is invalid.';

                return;
            case 'inv acct num':
            case 'invld acct':
            case 'no account':
            case 'invalid customer vault id specified':
            case 'customer profile id or customer payment profile id not found.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'invalid_account';
                $payment->failure_message = 'The card, or account the card is connected to, is invalid.';

                return;
            case 'issuer declined':
            case 'issuer declined mcc':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'generic_decline';
                $payment->failure_message = 'The card has been declined for an unknown reason.';

                return;
            case 'security violation':
            case 'gateway rejected: risk_threshold':
            case 'processor declined - fraud suspected':
            case 'the transaction was declined by our risk management department.':
                $payment->status = 'failed';
                $payment->outcome = 'blocked';
                $payment->failure_code = 'card_declined';
                $payment->failure_message = 'Your card was declined.';

                return;
            case 'call ae':
            case 'call cb':
            case 'call center':
            case 'call dc':
            case 'call discover':
            case 'call jb':
            case 'call nd':
            case 'call tc':
            case 'call tk':
            case 'call wc':
            case 'do not honor':
            case 'issuer or cardholder has put a restriction on the card':
            case 'rejected contact cust serv':
            case 'please contact this organization for assistance with processing this transaction':
            case 'serv not allowed':
            case "the bank has requested that you process the transaction manually by calling the card holder's credit card company.":
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'call_issuer';
                $payment->failure_message = 'The card has been declined for an unknown reason.';

                return;
            case 'invalid card number':
            case 'invalid credit card number':
            case 'the ccnumber field is required':
            case 'the credit card number is invalid.':
            case 'your card number is incorrect.':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'incorrect_number';
                $payment->failure_message = 'Your card number is invalid.';

                return;
            case 'agreement canceled':
            case 'customer requested stop of all recurring payments':
                $payment->status = 'failed';
                $payment->outcome = 'issuer_declined';
                $payment->failure_code = 'incorrect_number';
                $payment->failure_message = 'Your billing agreement is canceled.';

                return;
        }

        if (preg_match('/(is not accepted by this processor|the c[ck] payment type .* and\/or currency .* is not accepted|your card does not support this type of purchase)/i', $value)) {
            $payment->status = 'failed';
            $payment->outcome = 'issuer_declined';
            $payment->failure_code = 'card_not_supported';
            $payment->failure_message = 'The card does not support this type of purchase.';

            return;
        }

        if (preg_match('/No such token: .*/i', $value)) {
            $payment->status = 'failed';
            $payment->outcome = 'invalid';
            $payment->failure_code = 'invalid_request_error';
            $payment->failure_message = 'No such token.';

            return;
        }

        if (preg_match('/card expiration should be in the format/i', $value)) {
            $payment->status = 'failed';
            $payment->outcome = 'issuer_declined';
            $payment->failure_code = 'invalid_expiry_year';
            $payment->failure_message = "The card's expiration year is incorrect.";

            return;
        }

        if (preg_match('/pick up card/i', $value)) {
            $payment->status = 'failed';
            $payment->outcome = 'issuer_declined';
            $payment->failure_code = 'pickup_card';
            $payment->failure_message = 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).';

            return;
        }

        $payment->status = 'failed';
        $payment->outcome = 'invalid';
        $payment->failure_code = 'unhandled_response';
        $payment->failure_message = $value;
    }
}
