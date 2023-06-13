<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PaymentDrop extends Drop
{
    /**
     * @param \Ds\Models\Payment $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'type' => $source->type,
            'status' => $source->status,
            'amount' => $source->amount,
            'amount_refunded' => $source->amount_refunded,
            'currency' => new CurrencyDrop(currency($source->currency)),
            'paid' => $source->paid,
            'captured' => $source->captured,
            'captured_at' => $source->captured_at,
            'created_at' => $source->created_at,
            'refunded' => $source->refunded,
            'reference_number' => $source->reference_number,
            'description' => $source->description,
            'failure_code' => $source->failure_code,
            'failure_message' => $source->failure_message,
            'outcome' => $source->outcome,
        ];

        if ($source->type === 'card') {
            $this->liquid['card'] = [
                'brand' => $source->card_brand,
                'last4' => $source->card_last4,
                'exp_month' => $source->card_exp_month,
                'exp_year' => $source->card_exp_year,
                'cvc_check' => $source->card_cvc_check,
                'address_line1_check' => $source->card_address_line1_check,
                'address_zip_check' => $source->card_address_zip_check,
                'wallet' => $source->card_wallet,
            ];
        } elseif ($source->type === 'bank') {
            $this->liquid['bank'] = [
                'name' => $source->bank_name,
                'last4' => $source->bank_last4,
                'account_holder_type' => $source->bank_account_holder_type,
                'account_type' => $source->bank_account_type,
            ];
        } elseif ($source->type === 'cheque') {
            $this->liquid['cheque'] = [
                'number' => $source->cheque_number,
                'date' => $source->cheque_date,
            ];
        }
    }

    public function refunds()
    {
        return $this->source->refunds;
    }
}
