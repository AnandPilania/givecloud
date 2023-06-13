<?php

namespace Tests\Unit\Domain\HotGlue\Transformers\Salesforce;

use Ds\Domain\HotGlue\Transformers\Salesforce\RecurringDonationTransformer;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group hotglue
 */
class RecurringDonationTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        Event::fake();

        $member = Member::factory()->create();
        $order = Order::factory()->create();

        $rpp = RecurringPaymentProfile::factory()->create([
            'member_id' => $member,
            'productorder_id' => $order,
            'productorderitem_id' => OrderItem::factory()->state(['productorderid' => $order]),
        ]);

        $transaction = Transaction::factory()->paid()->create([
            'recurring_payment_profile_id' => $rpp,
            'order_time' => toUtc(fromLocal('yesterday')),
        ]);

        $data = $this->app->make(RecurringDonationTransformer::class)->transform($transaction);

        $this->assertIsArray($data);
        $this->assertStringContainsString($transaction->hashid, $data['name']);
        $this->assertSame($transaction->hashid, data_get($data, 'external_id.value'));
        $this->assertSame($member->hashid, data_get($data, 'contact_external_id.value'));
        $this->assertSame($transaction->amt, $data['amount']);
        $this->assertSame($transaction->order_time->toApiFormat(), $data['created_at']);
    }
}
