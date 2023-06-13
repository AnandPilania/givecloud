<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Payment */
class PaymentResource extends JsonResource
{
    /**
     * Transform the rethis into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount,
            'amount_refunded' => $this->amount_refunded,
            'currency' => new CurrencyResource(currency($this->currency)),
            'paid' => $this->paid,
            'captured' => $this->captured,
            'captured_at' => $this->captured_at,
            'created_at' => $this->created_at,
            'refunded' => $this->refunded,
            'gateway' => $this->gateway_type,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'failure_code' => $this->failure_code,
            'failure_message' => $this->failure_message,
            'outcome' => $this->outcome,
        ] + $this->getPaymentProviderInfo();
    }

    private function getPaymentProviderInfo(): array
    {
        if ($this->type === 'bank') {
            return ['bank' => [
                'name' => $this->bank_name,
                'last4' => $this->bank_last4,
                'account_holder_type' => $this->bank_account_holder_type,
                'account_type' => $this->bank_account_type,
            ]];
        }

        if ($this->type === 'card') {
            return ['card' => [
                'brand' => $this->card_brand,
                'last4' => $this->card_last4,
                'exp_month' => $this->card_exp_month,
                'exp_year' => $this->card_exp_year,
                'cvc_check' => $this->card_cvc_check,
                'address_line1_check' => $this->card_address_line1_check,
                'address_zip_check' => $this->card_address_zip_check,
                'wallet' => $this->card_wallet,
            ]];
        }

        if ($this->type === 'cheque') {
            return ['cheque' => [
                'number' => $this->cheque_number,
                'date' => $this->cheque_date,
            ]];
        }

        return [];
    }
}
