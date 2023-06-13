<?php

namespace Ds\Domain\Commerce\Gateways\Braintree;

use Braintree\ApplePayCard;
use Braintree\CreditCard;
use Braintree\GooglePayCard;
use Braintree\Transaction;
use Braintree\UsBankAccount;
use Ds\Domain\Commerce\Responses\TransactionResponse as Response;
use Illuminate\Support\Str;

class TransactionResponse extends Response
{
    /**
     * @param \Braintree\Result\Successful|\Braintree\Result\Error $result
     * @return self
     */
    public function setResult($result): self
    {
        $riskData = $result->transaction->riskData ?? null;

        // Workaround for strange error, when transaction is made using a vaulted payment method then
        // LiabilityShift object with NULL attributes is added to the riskData which results in it triggering
        // a PHP warning when the result is serialized using toArray
        //   array_map(): Expected parameter 2 to be an array, null given
        if (isset($riskData->liabilityShift) && $riskData->liabilityShift->jsonSerialize() === null) {
            $riskData->_set('liabilityShift', null);
        }

        $this->merge([
            'completed' => (bool) $result->success,
            'response' => $result->success ? '1' : '2',
            'response_text' => $result->message ?? null,
            'gateway_data' => $result->toArray(),
        ]);

        if (isset($result->paymentMethod)) {
            return $this->setPaymentMethod($result->paymentMethod);
        }

        if (isset($result->transaction)) {
            return $this->setTransaction($result->transaction);
        }

        return $this;
    }

    public function setTransaction(Transaction $transaction): self
    {
        // required as fallback for transactions that don't have a
        // processorResponseType. for example ACH transactions, etc
        $hasCompletedStatus = in_array($transaction->status, [
            Transaction::AUTHORIZED,
            Transaction::AUTHORIZING,
            Transaction::SETTLEMENT_PENDING,
            Transaction::SETTLED,
            Transaction::SETTLING,
            Transaction::SUBMITTED_FOR_SETTLEMENT,
        ], true);

        $completed = $transaction->processorResponseType === 'approved' || $hasCompletedStatus;

        $this->merge([
            'completed' => $completed,
            'response' => $completed ? '1' : '2',
            'response_text' => $transaction->processorResponseText ?: $transaction->processorResponseCode ?: null,
            'transaction_id' => $transaction->id,
            'avs_code' => $transaction->avsErrorResponseCode ?? $transaction->avsPostalCodeResponseCode ?? $transaction->avsStreetAddressResponseCode ?? null,
            'cvv_code' => $transaction->cvvResponseCode ?? null,
            'customer_ref' => $transaction->customerDetails->id ?? null,
            'gateway_data' => $transaction->toArray(),
        ]);

        if (isset($transaction->applePayCardDetails)) {
            $this->setApplePayCard($transaction->applePayCardDetails);
        } elseif (isset($transaction->googlePayCardDetails)) {
            $this->setGooglePayCard($transaction->googlePayCardDetails);
        } elseif (isset($transaction->creditCardDetails)) {
            // order is important here apple/google pay checks need to happen first because
            // because apple/google pay transactions will also have creditCardDetails with blank details
            $this->setCreditCard($transaction->creditCardDetails);
        } elseif (isset($transaction->usBankAccount)) {
            $this->setUsBankAccount($transaction->usBankAccount);
        }

        return $this;
    }

    public function setPaymentMethod($paymentMethod): self
    {
        if ($paymentMethod instanceof ApplePayCard) {
            return $this->setApplePayCard($paymentMethod);
        }

        if ($paymentMethod instanceof CreditCard) {
            return $this->setCreditCard($paymentMethod);
        }

        if ($paymentMethod instanceof GooglePayCard) {
            return $this->setGooglePayCard($paymentMethod);
        }

        if ($paymentMethod instanceof UsBankAccount) {
            return $this->setUsBankAccount($paymentMethod);
        }

        return $this;
    }

    /**
     * @param \Braintree\ApplePayCard|\Braintree\Transaction\ApplePayCardDetails
     * @return self
     */
    public function setApplePayCard($card): self
    {
        return $this->merge([
            'token_type' => 'card',
            'source_token' => $card->token,
            'customer_ref' => $card->customerId ?? null,
            'account_type' => Str::after($card->cardType, 'Apple Pay - '),
            'cc_number' => "{$card->bin}******{$card->last4}",
            'cc_exp_month' => $card->expirationMonth,
            'cc_exp_year' => $card->expirationYear,
            'cc_wallet' => 'apple_pay',
        ]);
    }

    /**
     * @param \Braintree\CreditCard|\Braintree\Transaction\CreditCardDetails
     * @return self
     */
    public function setCreditCard($card): self
    {
        return $this->merge([
            'token_type' => 'card',
            'source_token' => $card->token,
            'customer_ref' => $card->customerId ?? null,
            'account_type' => $card->cardType === 'Unknown' ? null : $card->cardType,
            'cc_number' => $card->maskedNumber,
            'cc_exp_month' => $card->expirationMonth,
            'cc_exp_year' => $card->expirationYear,
            'fingerprint' => $card->uniqueNumberIdentifier,
            'billing_first_name' => Str::firstName($card->cardholderName),
            'billing_last_name' => Str::lastName($card->cardholderName),
            // $card->verification for AVS/CVV results
        ]);
    }

    /**
     * @param \Braintree\GooglePayCard|\Braintree\Transaction\GooglePayCardDetails
     * @return self
     */
    public function setGooglePayCard($card): self
    {
        $last4 = $card->sourceCardLast4 ?? $card->virtualCardLast4 ?? null;

        return $this->merge([
            'token_type' => 'card',
            'source_token' => $card->token,
            'customer_ref' => $card->customerId ?? null,
            'account_type' => $card->sourceCardType ?? $card->virtualCardType ?? null,
            'cc_number' => "{$card->bin}******{$last4}",
            'cc_exp_month' => $card->expirationMonth,
            'cc_exp_year' => $card->expirationYear,
            'cc_wallet' => 'google_pay',
        ]);
    }

    /**
     * @param \Braintree\UsBankAccount|\Braintree\Transaction\UsBankAccountDetails
     * @return self
     */
    public function setUsBankAccount($bankAccount): self
    {
        return $this->merge([
            'token_type' => 'us_bank_account',
            'source_token' => $bankAccount->token,
            'customer_ref' => $bankAccount->customerId ?? null,
            'ach_account' => $bankAccount->last4,
            'ach_routing' => $bankAccount->routingNumber,
            'ach_type' => $bankAccount->accountType,
            'ach_entity' => $bankAccount->ownershipType,
        ]);
    }
}
