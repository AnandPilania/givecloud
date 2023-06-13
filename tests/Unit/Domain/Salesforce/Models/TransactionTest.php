<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Models\Transaction as SalesforceTransaction;
use Ds\Models\Transaction;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group salesforce
 */
class TransactionTest extends TestCase
{
    use InteractsWithRpps;

    public function testCanMapFieldsFromTransaction(): void
    {
        $rpp = $this->generateAccountsWithPMsAndRpps()->first()->recurringPaymentProfiles->first();
        $transaction = Transaction::factory()->paid()->create();
        $transaction->recurringPaymentProfile()->associate($rpp)->save();

        $fields = $this->app->make(SalesforceTransaction::class)->forModel($transaction)->fields();

        $this->assertSame('Transaction', $fields['Givecloud__Givecloud_Contribution_Type__c']);
        $this->assertSame('T' . $transaction->id, $fields['Givecloud__Givecloud_Contribution_Identifier__c']);
        $this->assertSame('T' . $transaction->id, $fields['Givecloud__Contribution_Number__c']);

        $this->assertSame($transaction->is_payment_accepted, $fields['Givecloud__Contribution_Paid__c']);

        $this->assertSame($transaction->recurringPaymentProfile->member->id, $fields['Givecloud__Supporter__r']['Givecloud__Givecloud_Supporter_ID__c']);

        $this->assertArrayHasKey('Name', $fields);
        $this->assertArrayHasKey('Givecloud__Givecloud_Contribution_Type__c', $fields);
        $this->assertArrayHasKey('Givecloud__Givecloud_Contribution_Identifier__c', $fields);
        $this->assertArrayHasKey('Givecloud__Contribution_Number__c', $fields);
        $this->assertArrayHasKey('Givecloud__Currency__c', $fields);
        $this->assertArrayHasKey('Givecloud__Contribution_Paid__c', $fields);
        $this->assertArrayHasKey('Givecloud__Referral_Source__c', $fields);
        $this->assertArrayHasKey('Givecloud__Created_Date__c', $fields);
        $this->assertArrayHasKey('Givecloud__Order_Date__c', $fields);
        $this->assertArrayHasKey('Givecloud__Cover_Costs_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Cover_Costs_Enabled__c', $fields);
        $this->assertArrayHasKey('Givecloud__Downloadable_Item_Count__c', $fields);
        $this->assertArrayHasKey('Givecloud__Recurring_Item_Count__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shippable_Item_Count__c', $fields);
        $this->assertArrayHasKey('Givecloud__Payment_Type__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Subtotal_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Tax_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Total_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Refunded_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Refund_Date__c', $fields);
        $this->assertArrayHasKey('Givecloud__Balance_Amount__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Title__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_First_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Last_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Email__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Address_1__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Address_2__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_City__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Province_Code__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Zip_Code__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Country_Code__c', $fields);
        $this->assertArrayHasKey('Givecloud__Billing_Phone_Number__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Method__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Title__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_First_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Last_Name__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Email__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Phone_Number__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Address_1__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Address_2__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_City__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Province_Code__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Zip_Code__c', $fields);
        $this->assertArrayHasKey('Givecloud__Shipping_Country_Code__c', $fields);

        $this->assertArrayHasKey('Givecloud__Supporter__r', $fields);
    }
}
