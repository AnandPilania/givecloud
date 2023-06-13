<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Models\Order;
use Ds\Models\Payment;
use Tests\TestCase;

/**
 * @group salesforce
 */
class PaymentTest extends TestCase
{
    public function testCanMapFieldsFromPayment(): void
    {
        $order = Order::factory()->paid()->create();
        $payment = Payment::factory()->card()->create();
        $order->payments()->attach($payment);
        $payment->load('orders');

        $fields = $this->app->make(\Ds\Domain\Salesforce\Models\Payment::class)->forModel($payment)->fields();

        $this->assertSame($payment->id, $fields['Givecloud__Givecloud_Payment_ID__c']);
        $this->assertSame($payment->description, $fields['Name']);
        $this->assertSame($payment->amount, $fields['Givecloud__Payment_Amount__c']);
        $this->assertSame($payment->paid, $fields['Givecloud__Paid__c']);
        $this->assertSame($payment->captured, $fields['Givecloud__Captured__c']);

        $this->assertSame($payment->type, $fields['Givecloud__Payment_Type__c']);
        $this->assertSame($payment->card_brand, $fields['Givecloud__Card_Brand__c']);
        $this->assertSame($payment->card_exp_month, $fields['Givecloud__Card_Expiration_Month__c']);
        $this->assertSame($payment->card_exp_year, $fields['Givecloud__Card_Expiration_Year__c']);
    }
}
