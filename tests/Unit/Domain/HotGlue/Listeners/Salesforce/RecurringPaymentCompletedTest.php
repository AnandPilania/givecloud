<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\Salesforce\RecurringPaymentCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\RecurringBatch;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class RecurringPaymentCompletedTest extends TestCase
{
    public function testShouldQueueReturnsFalseIfExternalIdIsNotProvided(): void
    {
        $this->assertEmpty(sys_get('salesforce_recurring_donation_external_id'));
        $this->assertFalse($this->app->make(RecurringPaymentCompleted::class)->shouldQueue());
    }

    public function testShouldQueueReturnsFalseWhenPartOfABatch(): void
    {
        sys_set('feature_hotglue_salesforce', true);
        sys_set('hotglue_salesforce_linked', true);
        sys_set('salesforce_recurring_donation_external_id', 'my_external_id__c');

        $transaction = $this->createTransaction();
        $transaction->recurringBatch()->associate(RecurringBatch::factory()->create());

        $event = new RecurringPaymentWasCompleted($transaction->recurringPaymentProfile, $transaction);
        $response = $this->app->make(RecurringPaymentCompleted::class)->shouldQueue($event);

        $this->assertFalse($response);
    }

    public function testShouldQueueReturnsTrueWhenExternalIdIsProvided(): void
    {
        Config::set('services.hotglue.salesforce.target_id', 'salesforce_target_id');

        Http::fake(function () {
            return Http::response([[
                'target' => 'salesforce_target_id',
                'domain' => 'salesforce.com',
                'label' => 'Salesforce',
                'version' => 'v2',
            ]]);
        });

        sys_set('feature_hotglue_salesforce', true);
        sys_set('hotglue_salesforce_linked', true);
        sys_set('salesforce_recurring_donation_external_id', 'my_external_id__c');

        $this->assertTrue($this->app->make(RecurringPaymentCompleted::class)->shouldQueue());
    }

    public function testHandleSendsPayload(): void
    {
        Http::fake();

        $transaction = $this->createTransaction();

        $event = new RecurringPaymentWasCompleted($transaction->recurringPaymentProfile, $transaction);

        $this->app->make(RecurringPaymentCompleted::class)->handle($event);

        Http::assertSent(function (Request $request) use ($transaction) {
            $member = $transaction->recurringPaymentProfile->member;

            return
                $request['tap'] === 'api' &&
                data_get($request, 'state.Contacts.0.external_id.value') === $member->hashid &&
                data_get($request, 'state.RecurringDonations.0.external_id.value') === $transaction->hashid;
        });
    }

    private function createTransaction(): Transaction
    {
        $member = Member::factory()->create();
        $order = Order::factory()->create();

        $rpp = RecurringPaymentProfile::factory()->create([
            'member_id' => $member,
            'productorder_id' => $order,
            'productorderitem_id' => OrderItem::factory()->state(['productorderid' => $order]),
        ]);

        return Transaction::factory()->paid()->create([
            'recurring_payment_profile_id' => $rpp,
            'order_time' => toUtc(fromLocal('yesterday')),
        ]);
    }
}
