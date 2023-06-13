<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\Salesforce\OrderPaid;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Order;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class OrderPaidListenerTest extends TestCase
{
    public function testShouldQueueReturnsFalseIfExternalIdIsNotProvided(): void
    {
        $this->assertEmpty(sys_get('salesforce_contact_external_id'));
        $this->assertFalse($this->app->make(OrderPaid::class)->shouldQueue());
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

        sys_set('salesforce_opportunity_external_id', 'my_external_id__c');

        $this->assertTrue($this->app->make(OrderPaid::class)->shouldQueue());
    }

    public function testHandleSendsPayload(): void
    {
        Http::fake();

        $order = Order::factory()->paid()->create();
        $event = new OrderWasCompleted($order);

        $this->app->make(OrderPaid::class)->handle($event);

        Http::assertSent(function (Request $request) use ($order) {
            return
                $request['tap'] === 'api' &&
                data_get($request, 'state.Contacts.0.external_id.value') === $order->member->hashid &&
                data_get($request, 'state.Deals.0.external_id.value') === $order->hashid;
        });
    }
}
