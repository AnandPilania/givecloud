<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PaymentMethodDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'name' => $source->display_name,
            'account_type' => $source->account_type,
            'account_number' => $source->account_number,
            'is_default' => $source->use_as_default,
            'is_expired' => $source->is_expired,
        ];

        if ($source->cc_expiry) {
            $this->liquid['card'] = [
                'brand' => $source->account_type,
                'last4' => $source->account_last_four,
                'exp_month' => nullable_cast('int', fromUtcFormat($source->cc_expiry, 'n')),
                'exp_year' => nullable_cast('int', fromUtcFormat($source->cc_expiry, 'Y')),
            ];
        } elseif ($source->ach_account_type) {
            $this->liquid['bank'] = [
                'name' => $source->ach_bank_name,
                'last4' => $source->account_last_four,
                'account_holder_type' => $source->ach_entity_type,
                'account_type' => $source->ach_account_type,
            ];
        }
    }

    public function billing_address()
    {
        return new AddressDrop($this->source);
    }

    public function payment_provider()
    {
        return $this->source->paymentProvider;
    }

    public function subscriptions()
    {
        return $this->source->recurringPaymentProfiles;
    }
}
