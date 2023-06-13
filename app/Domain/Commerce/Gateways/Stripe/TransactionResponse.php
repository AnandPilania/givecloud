<?php

namespace Ds\Domain\Commerce\Gateways\Stripe;

use Ds\Domain\Commerce\Responses\TransactionResponse as Response;
use Illuminate\Support\Str;
use Stripe\BankAccount;
use Stripe\Card;
use Stripe\Charge;
use Stripe\PaymentMethod;
use Stripe\StripeObject;

class TransactionResponse extends Response
{
    public function setCharge(Charge $charge): self
    {
        $this->merge([
            'completed' => $charge->status === 'succeeded',
            'response' => $charge->status === 'succeeded' ? '1' : '2',
            'response_text' => $charge->failure_message ?: $charge->status,
            'transaction_id' => $charge->id,
            'source_token' => $charge->payment_method ?? null,
            'customer_ref' => $charge->customer->id ?? $charge->customer ?? null,
            'application_fee_amount' => $charge->application_fee_amount === null ? null : money($charge->application_fee_amount, $charge->currency, true)->getAmount(),
            'stripe_payment_intent' => $charge->payment_intent->id ?? $charge->payment_intent ?? null,
            'gateway_data' => $charge->toArray(),
        ]);

        if (isset($charge->billing_details)) {
            $this->setBillingDetails($charge->billing_details);
        }

        if (isset($charge->source) && $charge->source instanceof BankAccount) {
            $this->setBankAccount($charge->source);
        } elseif (isset($charge->source) && $charge->source instanceof Card) {
            $this->setCard($charge->source);
        }

        if (isset($charge->payment_method_details->ach_debit)) {
            $this->setPaymentMethodDetailsAchDebit($charge->payment_method_details->ach_debit);
        } elseif (isset($charge->payment_method_details->card)) {
            $this->setPaymentMethodDetailsCard($charge->payment_method_details->card);
        }

        return $this;
    }

    public function setBankAccount(BankAccount $bankAccount): self
    {
        return $this->merge([
            'token_type' => 'bank_account',
            'source_token' => $bankAccount->id ?? null,
            'customer_ref' => $bankAccount->customer ?? null,
            'ach_bank' => $bankAccount->bank_name ?? null,
            'ach_account' => $bankAccount->last4 ?? null,
            'ach_routing' => $bankAccount->routing_number ?? null,
            'ach_type' => $bankAccount->account_holder_type ?? null,
            'fingerprint' => $bankAccount->fingerprint ?? null,
        ]);
    }

    public function setCard(Card $card): self
    {
        return $this->merge([
            'token_type' => 'card',
            'source_token' => $card->id ?? null,
            'customer_ref' => $card->customer ?? null,
            'account_type' => $card->brand ?? null,
            'cc_number' => $card->last4 ?? null,
            'cc_exp_month' => $card->exp_month ?? null,
            'cc_exp_year' => $card->exp_year ?? null,
            'cc_country' => $card->country ?? null,
            'cc_wallet' => $this->getWalletFromTokenizationMethod($card->tokenization_method ?? null),
            'fingerprint' => $card->fingerprint ?? null,
            'billing_address1' => $card->address_line1 ?? null,
            'billing_address2' => $card->address_line2 ?? null,
            'billing_city' => $card->address_city ?? null,
            'billing_state' => $card->address_state ?? null,
            'billing_postal' => $card->address_zip ?? null,
            'billing_country' => $this->getCountryCode($card->address_country ?? null),
            'cvv_code' => $card->cvc_check ?? null,
            'avs_code' => $this->getAvsCodeFromLine1CheckAndZipCheck(
                $card->address_line1_check ?? null,
                $card->address_zip_check ?? null,
            ),
        ]);
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): self
    {
        $this->merge([
            'source_token' => $paymentMethod->id,
            'customer_ref' => $paymentMethod->customer->id ?? $paymentMethod->customer ?? null,
        ]);

        if (isset($paymentMethod->ach_debit)) {
            $this->setPaymentMethodDetailsAchDebit($paymentMethod->ach_debit);
        } elseif (isset($paymentMethod->card)) {
            $this->setPaymentMethodDetailsCard($paymentMethod->card);
        }

        if (isset($paymentMethod->billing_details)) {
            $this->setBillingDetails($paymentMethod->billing_details);
        }

        return $this;
    }

    private function setBillingDetails(StripeObject $billingDetails): self
    {
        return $this->merge([
            'billing_first_name' => Str::firstName($billingDetails->name ?? null),
            'billing_last_name' => Str::lastName($billingDetails->name ?? null),
            'billing_email' => $billingDetails->email ?? null,
            'billing_phone' => $billingDetails->phone ?? null,
            'billing_address1' => $billingDetails->address->line1 ?? null,
            'billing_address2' => $billingDetails->address->line2 ?? null,
            'billing_city' => $billingDetails->address->city ?? null,
            'billing_state' => $billingDetails->address->state ?? null,
            'billing_postal' => $billingDetails->address->postal_code ?? null,
            'billing_country' => $billingDetails->address->country ?? null,
        ]);
    }

    private function setPaymentMethodDetailsAchDebit(StripeObject $achDebit): self
    {
        return $this->merge([
            'token_type' => 'ach_debit',
            'ach_bank' => $achDebit->bank_name ?? null,
            'ach_account' => $achDebit->last4 ?? null,
            'ach_routing' => $achDebit->routing_number ?? null,
            'ach_type' => $achDebit->account_holder_type ?? null,
            'fingerprint' => $achDebit->fingerprint ?? null,
        ]);
    }

    private function setPaymentMethodDetailsCard(StripeObject $card): self
    {
        return $this->merge([
            'token_type' => 'card',
            'account_type' => $card->brand ?? null,
            'cc_number' => $card->last4 ?? null,
            'cc_exp_month' => $card->exp_month ?? null,
            'cc_exp_year' => $card->exp_year ?? null,
            'cc_country' => $card->country ?? null,
            'cc_wallet' => $card->wallet->type ?? null,
            'fingerprint' => $card->fingerprint ?? null,
            'cvv_code' => $card->checks->cvc_check ?? null,
            'avs_code' => $this->getAvsCodeFromLine1CheckAndZipCheck(
                $card->checks->address_line1_check ?? null,
                $card->checks->address_zip_check ?? null,
            ),
        ]);
    }

    private function getCountryCode(?string $country): ?string
    {
        return mb_strlen($country) > 2 ? app('iso3166')->country($country, 'alpha_2') : $country;
    }

    private function getWalletFromTokenizationMethod(?string $tokenizationMethod): ?string
    {
        return $tokenizationMethod === 'android_pay' ? 'google_pay' : $tokenizationMethod;
    }

    private function getAvsCodeFromLine1CheckAndZipCheck(?string $line1Check, ?string $zipCheck): ?string
    {
        switch ($line1Check) {
            case 'pass':
                switch ($zipCheck) {
                    case 'pass': return 'Y';
                    case 'fail': return 'A';
                    case 'unchecked': return 'B';
                    default: return null;
                }
                // no break
            case 'fail':
                switch ($zipCheck) {
                    case 'pass': return 'Z';
                    case 'fail': return 'N';
                    case 'unchecked': return 'N';
                    default: return null;
                }
                // no break
            case 'unchecked':
                switch ($zipCheck) {
                    case 'pass': return 'P';
                    case 'fail': return 'N';
                    case 'unchecked': return 'U';
                    default: return null;
                }
                // no break
            default: return null;
        }
    }
}
