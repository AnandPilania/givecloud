<?php

namespace Tests\Unit\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Listeners\OrderPaid;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Domain\Salesforce\Services\SalesforceDiscountsService;
use Ds\Domain\Salesforce\Services\SalesforceLineItemService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Account;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\PromoCode;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * @group salesforce
 */
class OrderUpdatedTest extends TestCase
{
    public function testUpsertsContributionWhenOrderIsCompleted(): void
    {
        $this->mock(SalesforceContributionService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceSupporterService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceLineItemService::class)->shouldNotReceive('upsertMultiple');
        $this->mock(SalesforcePaymentsService::class)->shouldNotReceive('upsertMultiple');
        $this->mock(SalesforceContributionPaymentService::class)->shouldNotReceive('upsertMultiple');
        $this->mock(SalesforceDiscountsService::class)->shouldNotReceive('upsertMultiple');

        $event = new OrderWasCompleted(Order::factory()->paid()->create());

        $this->app->make(OrderPaid::class)->handle($event);
    }

    public function testDoesNotUpsertSupporterWhenNoSupporterForOrder(): void
    {
        $this->mock(SalesforceContributionService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceSupporterService::class)->shouldNotReceive('upsert');
        $this->mock(SalesforceLineItemService::class)->shouldNotReceive('upsertMultiple');
        $this->mock(SalesforcePaymentsService::class)->shouldNotReceive('upsertMultiple');
        $this->mock(SalesforceDiscountsService::class)->shouldNotReceive('upsertMultiple');

        $event = new OrderWasCompleted(Order::factory()->paid()->create([
            'member_id' => null,
        ]));

        $this->app->make(OrderPaid::class)->handle($event);
    }

    public function testUpsertsRelatedItemsWhenOrderIsCompleted(): void
    {
        $this->mock(SalesforceContributionService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceSupporterService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceLineItemService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforcePaymentsService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceContributionPaymentService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceDiscountsService::class)->shouldReceive('upsertMultiple')->once();

        $account = Account::factory()->create();
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $order = Order::factory()->paid()->hasAttached($promo)->create(['member_id' => $account]);
        $order->items()->saveMany(OrderItem::factory(3)->make());
        $order->payments()->saveMany(Payment::factory(2)->by($account)->paid()->create());
        $order->load('items');

        $event = new OrderWasCompleted($order);

        $this->app->make(OrderPaid::class)->handle($event);
    }

    public function testShouldNotDispatchIfSalesforceIsDisabled(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', false);

        dispatch_sync(new OrderWasCompleted(Order::factory()->paid()->create()));

        Bus::assertNotDispatched(OrderPaid::class);
        $this->assertFalse($this->app->make(OrderPaid::class)->shouldQueue());
    }

    public function testShouldNotDispatchIfSyncingSupportersIsDisable(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', true);

        $this->mock(SalesforceContributionService::class)->shouldReceive('shouldSync')->andReturnFalse();

        dispatch_sync(new OrderWasCompleted(Order::factory()->paid()->create()));

        $this->assertFalse($this->app->make(OrderPaid::class)->shouldQueue());
        Bus::assertNotDispatched(OrderPaid::class);
    }
}
