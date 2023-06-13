<?php

namespace Ds\Domain\Salesforce\Models;

use Ds\Models\Order;

class ContributionPayment extends Model
{
    /** @var Transaction|Contribution */
    protected $contribution;

    protected $table = 'Givecloud__ContributionPayment__c';

    public $externalKey = 'Givecloud__Givecloud_ContributionPayment__c';

    public function forModel(\Illuminate\Database\Eloquent\Model $model): Model
    {
        parent::forModel($model);

        $this->contribution = $this->model->transaction_id
            ? (new Transaction)->forModel(\Ds\Models\Transaction::find($this->model->transaction_id))
            : (new Contribution)->forModel(Order::find($this->model->order_id));

        return $this;
    }

    public function getCompoundKey()
    {
        return sprintf(
            '%s-%d',
            $this->contribution->getCompoundKey(),
            $this->model->payment_id
        );
    }

    public function fields(): array
    {
        $payment = (new Payment)->forModel(\Ds\Models\Payment::find($this->model->payment_id));

        return [
            'Givecloud__Givecloud_ContributionPayment__c' => $this->getCompoundKey(),

            'Givecloud__Contribution__r' => [
                'Givecloud__Givecloud_Contribution_Identifier__c' => $this->contribution->getCompoundKey(),
            ],
            'Givecloud__Payment__r' => [
                'Givecloud__Givecloud_Payment_ID__c' => $payment->getCompoundKey(),
            ],
        ];
    }
}
