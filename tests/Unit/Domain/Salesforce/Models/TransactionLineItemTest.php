<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Models\TransactionLineItem;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group salesforce
 */
class TransactionLineItemTest extends TestCase
{
    use InteractsWithRpps;
    use WithFaker;

    public function testCanMapFieldsFromTransactionOrderItem(): void
    {
        $transaction = $this->createTransactionWithRPP();
        $item = $transaction->recurringPaymentProfile->order_item;

        $fields = $this->app->make(TransactionLineItem::class)->forModel($transaction)->fields();

        $this->assertSame($transaction->id . '-' . $item->id, $fields['Givecloud__Givecloud_Line_Item_Identifier__c']);
        $this->assertSame('T' . $transaction->id, $fields['Givecloud__Contribution__r']['Givecloud__Givecloud_Contribution_Identifier__c']);
        $this->assertSame($item->id, $fields['Givecloud__Related_Line_Item__r']['Givecloud__Givecloud_Line_Item_Identifier__c']);

        $this->assertSame($item->description, $fields['Name']);
        $this->assertSame($transaction->amt, $fields['Givecloud__Total__c']);
    }
}
