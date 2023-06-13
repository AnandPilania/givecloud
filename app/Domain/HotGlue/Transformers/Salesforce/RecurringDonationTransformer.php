<?php

namespace Ds\Domain\HotGlue\Transformers\Salesforce;

use Ds\Models\Transaction;
use League\Fractal\TransformerAbstract;

class RecurringDonationTransformer extends TransformerAbstract
{
    public function transform(Transaction $transaction): array
    {
        return [
            'external_id' => [
                'name' => sys_get('salesforce_recurring_donation_external_id'),
                'value' => $transaction->hashid,
            ],
            'name' => sprintf(
                'Transaction %s for Profile %s',
                $transaction->hashid,
                $transaction->recurringPaymentProfile->profile_id
            ),

            'installment_period' => $this->installment($transaction),
            'amount' => $transaction->amt,
            'created_at' => $transaction->order_time->toApiFormat(),
            'contact_external_id' => [
                'name' => sys_get('salesforce_contact_external_id'),
                'value' => $transaction->recurringPaymentProfile->member->hashid,
            ],
        ];
    }

    public function installment(Transaction $transaction): ?string
    {
        switch ($transaction->recurringPaymentProfile->billing_period) {
            case 'Week':      return 'Weekly';
            case 'Month':     return 'Monthly';
            case 'Quarter':   return 'Quarterly';
            case 'Year':
            case 'SemiYear':
            default:
                    return 'Yearly';
        }
    }
}
