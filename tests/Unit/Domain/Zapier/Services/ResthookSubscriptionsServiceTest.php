<?php

namespace Tests\Unit\Domain\Zapier\Services;

use Ds\Domain\Zapier\Services\ResthookSubscriptionService;
use Ds\Models\ResthookSubscription;
use Tests\TestCase;

/**
 * @group zapier
 */
class ResthookSubscriptionsServiceTest extends TestCase
{
    /** @var string */
    private $resthookSubscriptionTable = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->resthookSubscriptionTable = (new ResthookSubscription)->getTable();
    }

    public function testIndex(): void
    {
        $resthookSubscriptions = ResthookSubscription::factory()->count(3)->create();

        $resthookSubscriptionsFetched = $this->app->make(ResthookSubscriptionService::class)->index();

        $this->assertEquals($resthookSubscriptions->toArray(), $resthookSubscriptionsFetched->toArray());
    }

    public function testStore(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->make();

        $resthookSubscriptionStored = $this->app->make(ResthookSubscriptionService::class)->store(
            $resthookSubscription->event,
            $resthookSubscription->target_url,
            $resthookSubscription->user_id,
        );

        $this->assertSame($resthookSubscription->event, $resthookSubscriptionStored->event);
        $this->assertSame($resthookSubscription->target_url, $resthookSubscriptionStored->target_url);
        $this->assertSame($resthookSubscription->user_id, $resthookSubscriptionStored->user_id);
        $this->assertDatabaseHas($this->resthookSubscriptionTable, $resthookSubscription->toArray());
    }

    public function testStoreFails(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->make();

        // Mock ResthookSubscription::save() return false
        $resthookSubscriptionMock = $this->createPartialMock(ResthookSubscription::class, ['newInstance', 'save']);
        $resthookSubscriptionMock
            ->expects($this->once())
            ->method('newInstance')
            ->willReturnSelf();
        $resthookSubscriptionMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $resthookSubscriptionNotStored = $this->app
            ->make(ResthookSubscriptionService::class, ['resthookSubscription' => $resthookSubscriptionMock])
            ->store($resthookSubscription->event, $resthookSubscription->target_url, $resthookSubscription->user_id);

        $this->assertNull($resthookSubscriptionNotStored);
        $this->assertDatabaseMissing($this->resthookSubscriptionTable, $resthookSubscription->toArray());
    }

    public function testUpdate(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->create();
        $resthookSubscriptionNewData = ResthookSubscription::factory()->make();

        $resthookSubscriptionUpdated = $this->app->make(ResthookSubscriptionService::class)->update(
            $resthookSubscription,
            $resthookSubscriptionNewData->event,
            $resthookSubscriptionNewData->target_url,
            $resthookSubscriptionNewData->user_id,
        );

        $this->assertTrue($resthookSubscriptionUpdated);
        $this->assertDatabaseHas($this->resthookSubscriptionTable, $resthookSubscriptionNewData->toArray());
    }
}
