<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Models\Discount;
use Ds\Models\Order;
use Ds\Models\PromoCode;
use Tests\TestCase;

/**
 * @group salesforce
 */
class DiscountTest extends TestCase
{
    public function testCanMapFieldsFromPromoCode(): void
    {
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $order = Order::factory()->paid()->hasAttached($promo)->create();

        $promo->load('orders');
        $order->load('promoCodes');

        $fields = $this->app->make(Discount::class)->forModel($order->promoCodes->first())->fields();

        $this->assertSame('C' . $order->id, $fields['Givecloud__Contribution__r']['Givecloud__Givecloud_Contribution_Identifier__c']);
        $this->assertSame($order->promoCodes->first()->pivot->id, $fields['Givecloud__Givecloud_Discount_ID__c']);
        $this->assertSame($promo->code, $fields['Name']);
        $this->assertSame($promo->code, $fields['Givecloud__Code__c']);
        $this->assertSame($promo->description, $fields['Givecloud__Description__c']);
        $this->assertSame($promo->discount, $fields['Givecloud__Discount_Amount__c']);
        $this->assertSame($promo->discount_formatted, $fields['Givecloud__Formatted__c']);
        $this->assertTrue($fields['Givecloud__Free_Shipping__c']);
    }
}
