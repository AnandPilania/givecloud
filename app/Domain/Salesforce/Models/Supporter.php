<?php

namespace Ds\Domain\Salesforce\Models;

class Supporter extends Model
{
    protected $table = 'Givecloud__Supporter__c';

    public $externalKey = 'Givecloud__Givecloud_Supporter_ID__c';

    public $columns = [
        'Id',
        'Name',
        'Givecloud__Supporter_E_mail__c',
        'Givecloud__Givecloud_Supporter_ID__c',
    ];

    public function fields(): array
    {
        return [
            'Name' => $this->model->display_name,
            'Givecloud__Supporter_E_mail__c' => $this->model->email,
            'Givecloud__Givecloud_Supporter_ID__c' => $this->model->id,

            'Givecloud__Active__c' => $this->model->is_active,
            'Givecloud__First_Name__c' => $this->model->first_name,
            'Givecloud__Last_Name__c' => $this->model->last_name,
            'Givecloud__Email__c' => $this->model->email,
            'Givecloud__Type__c' => $this->model->accountType->name ?? null,
            'Givecloud__Email_Opt_In__c' => $this->model->email_opt_in,
            'Givecloud__Billing_Title__c' => $this->model->bill_title,
            // 'Givecloud__Billing_Name__c' => $this->model->billing_name,
            'Givecloud__Billing_First_Name__c' => $this->model->bill_first_name,
            'Givecloud__Billing_Last_Name__c' => $this->model->bill_last_name,
            'Givecloud__Billing_Email__c' => $this->model->bill_email,
            'Givecloud__Billing_Address_1__c' => $this->model->bill_address_01,
            'Givecloud__Billing_Address_2__c' => $this->model->bill_address_02,
            'Givecloud__Billing_Company__c' => $this->model->bill_organization_name,
            'Givecloud__Billing_City__c' => $this->model->bill_city,
            'Givecloud__Billing_Province_Code__c' => $this->model->bill_state,
            'Givecloud__Billing_Zip_Code__c' => $this->model->bill_zip,
            'Givecloud__Billing_Country_Code__c' => $this->model->bill_country,
            'Givecloud__Billing_Phone_Number__c' => $this->model->bill_phone,
            'Givecloud__Shipping_Method__c' => $this->model->shipping_method,
            'Givecloud__Shipping_Title__c' => $this->model->shipping_title,
            'Givecloud__Shipping_Name__c' => $this->model->shipping_name,
            'Givecloud__Shipping_First_Name__c' => $this->model->ship_first_name,
            'Givecloud__Shipping_Last_Name__c' => $this->model->ship_last_name,
            'Givecloud__Shipping_Email__c' => $this->model->ship_email,
            'Givecloud__Shipping_Phone_Number__c' => $this->model->ship_phone,
            'Givecloud__Shipping_Address_1__c' => $this->model->ship_address_01,
            'Givecloud__Shipping_Address_2__c' => $this->model->ship_address_02,
            'Givecloud__Shipping_Company__c' => $this->model->ship_organization_name,
            'Givecloud__Shipping_City__c' => $this->model->ship_city,
            'Givecloud__Shipping_Province_Code__c' => $this->model->ship_state,
            'Givecloud__Shipping_Zip_Code__c' => $this->model->ship_zip,
            'Givecloud__Shipping_Country_Code__c' => $this->model->ship_country,
            'Givecloud__Created_Date__c' => $this->model->created_at,
            'Givecloud__Updated_Date__c' => $this->model->updated_at,
        ];
    }
}
