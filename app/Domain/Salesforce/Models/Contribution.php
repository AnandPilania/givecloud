<?php

namespace Ds\Domain\Salesforce\Models;

class Contribution extends Model
{
    protected $table = 'Givecloud__Contribution__c';

    public $externalKey = 'Givecloud__Givecloud_Contribution_Identifier__c';

    public $columns = [
        'Id',
        'Name',
        'Givecloud__Givecloud_Contribution_Identifier__c',
    ];

    public function getCompoundKey()
    {
        return 'C' . $this->model->getKey();
    }

    public function fields(): array
    {
        $fields = [
            'Name' => $this->model->invoicenumber,
            'Givecloud__Givecloud_Contribution_Type__c' => 'Contribution',
            'Givecloud__Givecloud_Contribution_Identifier__c' => $this->getCompoundKey(),
            'Givecloud__Contribution_Number__c' => $this->getCompoundKey(),

            'Givecloud__Currency__c' => $this->model->currency_code,
            'Givecloud__Customer_Comments__c' => $this->model->comments,
            'Givecloud__Notes__c' => $this->model->customer_notes,
            'Givecloud__Contribution_Paid__c' => $this->model->is_paid,
            'Givecloud__Referral_Source__c' => $this->model->referral_source,
            'Givecloud__Created_Date__c' => $this->model->createddatetime,
            'Givecloud__Order_Date__c' => $this->model->confirmationdatetime,
            'Givecloud__Cover_Costs_Amount__c' => $this->model->dcc_total_amount,
            'Givecloud__Cover_Costs_Enabled__c' => $this->model->dcc_enabled_by_customer,
            'Givecloud__Discounts_Amount__c' => $this->model->discount,
            'Givecloud__Downloadable_Item_Count__c' => $this->model->download_items,
            'Givecloud__Recurring_Item_Count__c' => $this->model->recurring_items,
            'Givecloud__Shippable_Item_Count__c' => $this->model->shippable_items,
            'Givecloud__Payment_Type__c' => $this->model->payment_type_description,
            'Givecloud__Shipping_Amount__c' => $this->model->shipping_amount,
            'Givecloud__Subtotal_Amount__c' => $this->model->subtotal,
            'Givecloud__Tax_Amount__c' => $this->model->taxtotal,
            'Givecloud__Total_Amount__c' => $this->model->totalamount,
            'Givecloud__Refunded_Amount__c' => $this->model->refunded_amt,
            'Givecloud__Refund_Date__c' => $this->model->refunded_at,
            'Givecloud__Balance_Amount__c' => $this->model->balance_amt,
            'Givecloud__Billing_Title__c' => $this->model->billing_title,
            'Givecloud__Billing_Name__c' => $this->model->billing_name,
            'Givecloud__Billing_First_Name__c' => $this->model->billing_first_name,
            'Givecloud__Billing_Last_Name__c' => $this->model->billing_last_name,
            'Givecloud__Billing_Email__c' => $this->model->billingemail,
            'Givecloud__Billing_Address_1__c' => $this->model->billingaddress1,
            'Givecloud__Billing_Address_2__c' => $this->model->billingaddress2,
            'Givecloud__Billing_Company__c' => $this->model->billing_organization_name,
            'Givecloud__Billing_City__c' => $this->model->billingcity,
            'Givecloud__Billing_Province_Code__c' => $this->model->billingstate,
            'Givecloud__Billing_Zip_Code__c' => $this->model->billingzip,
            'Givecloud__Billing_Country_Code__c' => $this->model->billingcountry,
            'Givecloud__Billing_Phone_Number__c' => $this->model->billingphone,
            'Givecloud__Shipping_Method__c' => $this->model->shipping_method,
            'Givecloud__Shipping_Title__c' => $this->model->shipping_title,
            'Givecloud__Shipping_Name__c' => $this->model->shipping_name,
            'Givecloud__Shipping_First_Name__c' => $this->model->shipping_first_name,
            'Givecloud__Shipping_Last_Name__c' => $this->model->shipping_last_name,
            'Givecloud__Shipping_Email__c' => $this->model->shipemail,
            'Givecloud__Shipping_Phone_Number__c' => $this->model->shipphone,
            'Givecloud__Shipping_Address_1__c' => $this->model->shipaddress1,
            'Givecloud__Shipping_Address_2__c' => $this->model->shipaddress2,
            'Givecloud__Shipping_Company__c' => $this->model->shipping_organization_name,
            'Givecloud__Shipping_City__c' => $this->model->shipcity,
            'Givecloud__Shipping_Province_Code__c' => $this->model->shipstate,
            'Givecloud__Shipping_Zip_Code__c' => $this->model->shipzip,
            'Givecloud__Shipping_Country_Code__c' => $this->model->shipcountry,
        ];

        if ($this->model->hasMember()) {
            $fields['Givecloud__Supporter__r'] = [
                'Givecloud__Givecloud_Supporter_ID__c' => $this->model->member->id ?? null,
            ];
        }

        return $fields;
    }
}
