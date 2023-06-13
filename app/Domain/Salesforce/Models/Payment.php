<?php

namespace Ds\Domain\Salesforce\Models;

class Payment extends Model
{
    protected $table = 'Givecloud__Payment__c';

    public $externalKey = 'Givecloud__Givecloud_Payment_ID__c';

    public $columns = [
        'Id',
        'Name',
        'Givecloud__Givecloud_Payment_ID__c',
    ];

    public function fields(): array
    {
        return [
            'Name' => $this->model->description,
            'Givecloud__Givecloud_Payment_ID__c' => $this->model->id,

            'Givecloud__Captured__c' => $this->model->captured,
            'Givecloud__Captured_Date__c' => $this->model->captured_at,
            'Givecloud__Card_Address_Line_1_Check__c' => $this->model->card_address_line1_check,
            'Givecloud__Card_Address_Zip_Code_Check__c' => $this->model->card_address_zip_check,
            'Givecloud__Card_Brand__c' => $this->model->card_brand,
            'Givecloud__Card_CVC_Check__c' => $this->model->card_cvc_check,
            'Givecloud__Card_Expiration_Month__c' => $this->model->card_exp_month,
            'Givecloud__Card_Expiration_Year__c' => $this->model->card_exp_year,
            'Givecloud__Card_Last_4_Digits__c' => $this->model->card_last4,
            'Givecloud__Created_Date__c' => $this->model->created_at,
            'Givecloud__Currency_Code__c' => $this->model->currency,
            'Givecloud__Description__c' => $this->model->description,
            'Givecloud__Failure_Code__c' => $this->model->failure_code,
            'Givecloud__Failure_Message__c' => $this->model->failure_message,
            'Givecloud__Outcome__c' => $this->model->outcome,
            'Givecloud__Paid__c' => $this->model->paid,
            'Givecloud__Payment_Amount__c' => $this->model->amount,
            'Givecloud__Payment_Amount_Refunded__c' => $this->model->amount_refunded,
            'Givecloud__Payment_Status__c' => $this->model->status,
            'Givecloud__Payment_Type__c' => $this->model->type,
            'Givecloud__Reference_Number__c' => $this->model->reference_number,
            'Givecloud__Refunded__c' => $this->model->refunded,
        ];
    }

    public function savesExternalReferenceLocally(): bool
    {
        return false;
    }
}
