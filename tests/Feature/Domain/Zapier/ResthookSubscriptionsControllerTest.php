<?php

namespace Tests\Feature\Domain\Zapier;

use Ds\Domain\Zapier\Services\ResthookSubscriptionService;
use Ds\Models\ResthookSubscription;
use Ds\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group zapier
 */
class ResthookSubscriptionsControllerTest extends AbstractZapier
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

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('resthook_subscriptions.index'))
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJson($resthookSubscriptions->toArray());
    }

    public function testShow(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->create();

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('resthook_subscriptions.show', ['resthook_subscription' => $resthookSubscription]))
            ->assertOk()
            ->assertJson($resthookSubscription->toArray());
    }

    public function testStore(): void
    {
        $user = User::factory()->api()->create();
        $resthookData = ResthookSubscription::factory()->make(['user_id' => $user->getKey()])->toArray();

        $this
            ->actingAsPassportUser($user, ['zapier'])
            ->postJson(route('resthook_subscriptions.store'), $resthookData)
            ->assertCreated();

        $this->assertDatabaseHas($this->resthookSubscriptionTable, $resthookData);
    }

    public function testStoreFails(): void
    {
        $resthookData = ResthookSubscription::factory()->make()->toArray();

        // Mock ResthookSubscriptionService::store() return false
        $resthookSubscriptionServiceMock = $this->createPartialMock(ResthookSubscriptionService::class, ['store']);
        $resthookSubscriptionServiceMock
            ->expects($this->once())
            ->method('store')
            ->willReturn(null);
        $this->app->instance(ResthookSubscriptionService::class, $resthookSubscriptionServiceMock);

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->postJson(route('resthook_subscriptions.store'), $resthookData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing($this->resthookSubscriptionTable, $resthookData);
    }

    /**
     * @dataProvider storeRequiredFieldsProvider
     */
    public function testStoreFailsWithInvalidData(string $field, $value): void
    {
        $resthookData = ResthookSubscription::factory()->make()->toArray();
        if ($value === null) {
            unset($resthookData[$field]);
        } else {
            $resthookData[$field] = $value;
        }

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->postJson(route('resthook_subscriptions.store'), $resthookData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing($this->resthookSubscriptionTable, $resthookData);
    }

    public function storeRequiredFieldsProvider(): array
    {
        return [
            ['event', null],
            ['event', 'wrong format for event name'],
            ['target_url', null],
            ['target_url', 'not an url'],
        ];
    }

    public function testUpdate(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->create();
        $newResthookSubscriptionData = ResthookSubscription::factory()->make()->toArray();

        $this
            ->actingAsPassportUser($user = User::factory()->create(), ['zapier'])
            ->putJson(
                route('resthook_subscriptions.update', ['resthook_subscription' => $resthookSubscription]),
                $newResthookSubscriptionData
            )
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(
            $this->resthookSubscriptionTable,
            array_merge($newResthookSubscriptionData, ['user_id' => $user->getKey()])
        );
    }

    /**
     * @dataProvider storeRequiredFieldsProvider
     */
    public function testUpdateFailsWithInvalidData(string $field, $value): void
    {
        $resthookSubscription = ResthookSubscription::factory()->create();

        $resthookData = $resthookSubscription->toArray();
        $resthookData[$field] = $value;

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->putJson(
                route('resthook_subscriptions.update', ['resthook_subscription' => $resthookSubscription]),
                $resthookData
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing($this->resthookSubscriptionTable, $resthookData);
    }

    public function testDestroy(): void
    {
        $resthookSubscription = ResthookSubscription::factory()->create();

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->deleteJson(route('resthook_subscriptions.destroy', ['resthook_subscription' => $resthookSubscription]))
            ->assertOk();

        $this->assertDatabaseMissing($this->resthookSubscriptionTable, $resthookSubscription->toArray());
    }

    public function testDestroyFailsWhenNotFound(): void
    {
        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->deleteJson(route('resthook_subscriptions.destroy', ['resthook_subscription' => 0]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
