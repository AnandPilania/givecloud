<?php

namespace Ds\Domain\Salesforce\Models;

class Transaction extends Contribution
{
    public function getCompoundKey()
    {
        return 'T' . $this->model->id;
    }

    public function fields(): array
    {
        $order = $this->model->recurringPaymentProfile->order;

        $fields = [
            'Name' => $this->model->transaction_id,
            'Givecloud__Givecloud_Contribution_Type__c' => 'Transaction',
            'Givecloud__Givecloud_Contribution_Identifier__c' => 'T' . $this->model->id,
            'Givecloud__Contribution_Number__c' => 'T' . $this->model->id,

            'Givecloud__Currency__c' => $this->model->currency_code,
            'Givecloud__Contribution_Paid__c' => $this->model->is_payment_accepted,
            'Givecloud__Referral_Source__c' => $order->referral_source,
            'Givecloud__Created_Date__c' => $this->model->order_time,
            'Givecloud__Order_Date__c' => $this->model->order_time,
            'Givecloud__Cover_Costs_Amount__c' => $this->model->dcc_amount,
            'Givecloud__Cover_Costs_Enabled__c' => $order->dcc_enabled_by_customer,

            'Givecloud__Downloadable_Item_Count__c' => $order->download_items,
            'Givecloud__Recurring_Item_Count__c' => $order->recurring_items,
            'Givecloud__Shippable_Item_Count__c' => $order->shippable_items,
            'Givecloud__Payment_Type__c' => $this->model->payment_description,
            'Givecloud__Shipping_Amount__c' => $this->model->shipping_amt,
            'Givecloud__Subtotal_Amount__c' => $this->model->amt,
            'Givecloud__Tax_Amount__c' => $this->model->tax_amt,
            'Givecloud__Total_Amount__c' => $this->model->amt,
            'Givecloud__Refunded_Amount__c' => $this->model->refunded_amt,
            'Givecloud__Refund_Date__c' => $this->model->refunded_at,
            'Givecloud__Balance_Amount__c' => $this->model->amt - $this->model->refunded_amt,
            'Givecloud__Billing_Title__c' => $order->billing_title,
            'Givecloud__Billing_Name__c' => $order->billing_name,
            'Givecloud__Billing_First_Name__c' => $order->billing_first_name,
            'Givecloud__Billing_Last_Name__c' => $order->billing_last_name,
            'Givecloud__Billing_Email__c' => $order->billingemail,
            'Givecloud__Billing_Address_1__c' => $order->billingaddress1,
            'Givecloud__Billing_Address_2__c' => $order->billingaddress2,
            'Givecloud__Billing_Company__c' => $order->billing_organization_name,
            'Givecloud__Billing_City__c' => $order->billingcity,
            'Givecloud__Billing_Province_Code__c' => $order->billingstate,
            'Givecloud__Billing_Zip_Code__c' => $order->billingzip,
            'Givecloud__Billing_Country_Code__c' => $order->billingcountry,
            'Givecloud__Billing_Phone_Number__c' => $order->billingphone,
            'Givecloud__Shipping_Method__c' => $order->shipping_method,
            'Givecloud__Shipping_Title__c' => $order->shipping_title,
            'Givecloud__Shipping_Name__c' => $this->model->ship_to_name,
            'Givecloud__Shipping_First_Name__c' => $order->shipping_first_name,
            'Givecloud__Shipping_Last_Name__c' => $order->shipping_last_name,
            'Givecloud__Shipping_Email__c' => $order->shipemail,
            'Givecloud__Shipping_Phone_Number__c' => $this->model->ship_to_phone_num,
            'Givecloud__Shipping_Address_1__c' => $this->model->ship_to_street,
            'Givecloud__Shipping_Address_2__c' => $this->model->ship_to_street2,
            'Givecloud__Shipping_Company__c' => $this->model->ship_to_name,
            'Givecloud__Shipping_City__c' => $this->model->ship_to_city,
            'Givecloud__Shipping_Province_Code__c' => $this->model->ship_to_state,
            'Givecloud__Shipping_Zip_Code__c' => $this->model->ship_to_zip,
            'Givecloud__Shipping_Country_Code__c' => $this->model->ship_to_country,
        ];

        if ($this->model->recurringPaymentProfile->member) {
            $fields['Givecloud__Supporter__r'] = [
                'Givecloud__Givecloud_Supporter_ID__c' => $this->model->recurringPaymentProfile->member->id,
            ];
        }

        return $fields;
    }
}
