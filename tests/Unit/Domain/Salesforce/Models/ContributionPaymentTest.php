<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Models\ContributionPayment;
use Ds\Models\Order;
use Ds\Models\Payment;
use Tests\TestCase;

/** @group salesforce */
class ContributionPaymentTest extends TestCase
{
    public function testCanMapFieldsFromPivot(): void
    {
        $order = Order::factory()->paid()->create();
        $payment = Payment::factory()->card()->create();
        $order->payments()->attach($payment);
        $payment->load('orders');

        $pivot = $order->payments->pluck('pivot')->first();

        $fields = $this->app->make(ContributionPayment::class)->forModel($pivot)->fields();

        $expectedId = sprintf('C%d-%d', $order->id, $payment->id);

        $this->assertSame($expectedId, $fields['Givecloud__Givecloud_ContributionPayment__c']);
        $this->assertSame('C' . $order->id, $fields['Givecloud__Contribution__r']['Givecloud__Givecloud_Contribution_Identifier__c']);
        $this->assertSame($payment->id, $fields['Givecloud__Payment__r']['Givecloud__Givecloud_Payment_ID__c']);
    }
}
