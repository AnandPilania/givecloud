<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Models\LineItem;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Tests\TestCase;

/**
 * @group salesforce
 */
class LineItemTest extends TestCase
{
    public function testCanMapFieldsFromOrderItem(): void
    {
        $item = OrderItem::factory()->for(Order::factory()->paid())->make();

        $fields = $this->app->make(LineItem::class)->forModel($item)->fields();

        $this->assertIsString($this->app->make(LineItem::class)->forModel($item)->getCompoundKey());

        $this->assertSame($item->id, $fields['Givecloud__Givecloud_Line_Item_Identifier__c']);
        $this->assertSame('C' . $item->order->id, $fields['Givecloud__Contribution__r']['Givecloud__Givecloud_Contribution_Identifier__c']);
        $this->assertSame($item->description, $fields['Name']);
        $this->assertSame($item->total, $fields['Givecloud__Total__c']);
    }
}
