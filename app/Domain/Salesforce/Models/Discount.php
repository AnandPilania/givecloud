<?php

namespace Ds\Domain\Salesforce\Models;

use Ds\Models\Order;

class Discount extends Model
{
    protected $table = 'Givecloud__Discount__c';

    public $externalKey = 'Givecloud__Givecloud_Discount_ID__c';

    public $columns = [
        'Id',
        'Name',
        'Givecloud__Givecloud_Discount_ID__c',
    ];

    public function fields(): array
    {
        $relatedOrder = new Order;
        $relatedOrder->id = $this->model->pivot->order_id;

        return [
            'Name' => $this->model->code,
            'Givecloud__Code__c' => $this->model->code,
            'Givecloud__Givecloud_Discount_ID__c' => $this->model->pivot->id,
            'Givecloud__Description__c' => $this->model->description,
            'Givecloud__Discount_Amount__c' => $this->model->discount,
            'Givecloud__Formatted__c' => $this->model->discount_formatted,
            'Givecloud__Free_Shipping__c' => $this->model->is_free_shipping,
            'Givecloud__Contribution__r' => [
                'Givecloud__Givecloud_Contribution_Identifier__c' => (new Contribution)->forModel($relatedOrder)->getCompoundKey(),
            ],
        ];
    }

    public function savesExternalReferenceLocally(): bool
    {
        return false;
    }
}
