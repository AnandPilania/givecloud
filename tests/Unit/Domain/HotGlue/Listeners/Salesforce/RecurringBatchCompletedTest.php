<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\Salesforce\RecurringBatchCompleted;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\RecurringBatch;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class RecurringBatchCompletedTest extends TestCase
{
    public function testShouldQueueReturnsFalseIfExternalIdIsNotProvided(): void
    {
        $this->assertEmpty(sys_get('salesforce_recurring_donation_external_id'));
        $this->assertFalse($this->app->make(RecurringBatchCompleted::class)->shouldQueue());
    }

    public function testHandleSendsPayload(): void
    {
        Http::fake();

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

        $batch = RecurringBatch::factory()->create();
        $batch->transactions()->save($transaction);

        $event = new \Ds\Events\RecurringBatchCompleted($batch);

        $this->app->make(RecurringBatchCompleted::class)->handle($event);

        Http::assertSent(function (Request $request) use ($transaction, $member) {
            return
                $request['tap'] === 'api' &&
                data_get($request, 'state.Contacts.0.external_id.value') === $member->hashid &&
                data_get($request, 'state.RecurringDonations.0.external_id.value') === $transaction->hashid;
        });
    }
}
