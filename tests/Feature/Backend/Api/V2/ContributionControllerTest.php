<?php

namespace Tests\Feature\Backend\Api\V2;

use Carbon\Carbon;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Tax;
use Ds\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group api
 */
class ContributionControllerTest extends TestCase
{
    use InteractsWithRpps;
    use WithFaker;

    public function testIndexSuccess(): void
    {
        Transaction::factory()->paid();  // transaction - should not be returned w/o param
        Order::factory(2)->create(); // unpaid orders - should not be returned
        Order::factory(3)->paid()->create();

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index'))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $jsonResponse->assertCount(3, 'data');
    }

    public function testIndexFailsForGuest(): void
    {
        $this->getJson(route('admin.api.v2.contributions.index'))
            ->assertUnauthorized();
    }

    public function testIndexFailsWithoutPermission(): void
    {
        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.contributions.index'))
            ->assertForbidden();
    }

    public function testShowSuccess(): void
    {
        // we create a few orders to be certain we get back the only one we expect
        Order::factory(10)->paid()->create();

        $taxes = Tax::factory(2)->create();
        $order = Order::factory()->paid()
            ->hasItems(
                OrderItem::factory(mt_rand(1, 5))
                    ->afterMaking(function (OrderItem $item) use ($taxes) {
                        $item->taxes()->sync($taxes->random(mt_rand(0, $taxes->count()) ?: null));
                    })
            )->hasPayments(
                Payment::factory(1)
                    ->card()
                    ->state(function (array $attributes, Order $order) {
                        return ['amount' => $order->totalamount];
                    })
            )->create(['totalamount' => 123]);

        /** @var \Ds\Models\Order */

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.show', $order->hashid))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $this->assertSame($order->hashid, $jsonResponse->json('data.id'));
    }

    public function testShowFailsForGuest(): void
    {
        $this
            ->getJson(route('admin.api.v2.contributions.show', Order::factory(3)->create()->first()->hashid))
            ->assertUnauthorized();
    }

    public function testShowFailsWithoutPermission(): void
    {
        $this
            ->actingAsPassportUser()
            ->getJson(route('admin.api.v2.contributions.show', Order::factory(3)->create()->first()->hashid))
            ->assertForbidden();
    }

    public function testCreatedBeforeFilterSuccess(): void
    {
        Order::factory(5)->paid()->sequence(
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()],
        )->create();
        $filterOrderDate = Carbon::now()->subDays(10);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', ['filter[ordered_before]' => $filterOrderDate->toDateString()]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(4, $jsonResponse['meta']['total']);
    }

    public function testCreatedAfterFilterSuccess(): void
    {
        Order::factory(5)->paid()->sequence(
            ['ordered_at' => Carbon::now()->subDays(30)],
            ['ordered_at' => Carbon::now()->subDays(30)],
            ['ordered_at' => Carbon::now()->subDays(30)],
            ['ordered_at' => Carbon::now()], // should return
            ['ordered_at' => Carbon::now()], // should return
        )->create();
        $filterOrderDate = Carbon::now()->subDays(10);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', ['filter[ordered_after]' => $filterOrderDate->toDateString()]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(2, $jsonResponse['meta']['total']);
    }

    public function testCreatedBeforeAndCreatedAfterFilterSuccess(): void
    {
        Order::factory(10)->paid()->sequence(
            ['ordered_at' => Carbon::now()->subDays(60)],
            ['ordered_at' => Carbon::now()->subDays(60)],
            ['ordered_at' => Carbon::now()->subDays(60)],
            ['ordered_at' => Carbon::now()->subDays(60)],
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()->subDays(30)], // should return
            ['ordered_at' => Carbon::now()],
            ['ordered_at' => Carbon::now()],
            ['ordered_at' => Carbon::now()],
        )->create();
        $beforeFilterOrderDate = Carbon::now()->subDays(10);
        $afterFilterOrderDate = Carbon::now()->subDays(40);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[ordered_before]' => $beforeFilterOrderDate->toDateString(),
                'filter[ordered_after]' => $afterFilterOrderDate->toDateString(),
            ]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(3, $jsonResponse['meta']['total']);
    }

    public function testUpdatedBeforeFilterSuccess(): void
    {
        Order::factory(5)->paid()->sequence(
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()],
        )->create();
        $filterOrderDate = Carbon::now()->subDays(10);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', ['filter[updated_before]' => $filterOrderDate->toDateString()]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(4, $jsonResponse['meta']['total']);
    }

    public function testUpdatedAfterFilterSuccess(): void
    {
        Order::factory(5)->paid()->sequence(
            ['updated_at' => Carbon::now()->subDays(30)],
            ['updated_at' => Carbon::now()->subDays(30)],
            ['updated_at' => Carbon::now()->subDays(30)],
            ['updated_at' => Carbon::now()], // should return
            ['updated_at' => Carbon::now()], // should return
        )->create();
        $filterOrderDate = Carbon::now()->subDays(10);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', ['filter[updated_after]' => $filterOrderDate->toDateString()]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(2, $jsonResponse['meta']['total']);
    }

    public function testUpdatedBeforeAndUpdatedAfterFilterSuccess(): void
    {
        Order::factory(10)->paid()->sequence(
            ['updated_at' => Carbon::now()->subDays(60)],
            ['updated_at' => Carbon::now()->subDays(60)],
            ['updated_at' => Carbon::now()->subDays(60)],
            ['updated_at' => Carbon::now()->subDays(60)],
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()->subDays(30)], // should return
            ['updated_at' => Carbon::now()],
            ['updated_at' => Carbon::now()],
            ['updated_at' => Carbon::now()],
        )->create();
        $beforeFilterOrderDate = Carbon::now()->subDays(10);
        $afterFilterOrderDate = Carbon::now()->subDays(40);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['order.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[updated_before]' => $beforeFilterOrderDate->toDateString(),
                'filter[updated_after]' => $afterFilterOrderDate->toDateString(),
            ]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $this->assertSame(3, $jsonResponse['meta']['total']);
    }

    /*
     * Transactions
     */
    public function testIndexFailsForGuestWithRecurringParam(): void
    {
        $this->getJson(route('admin.api.v2.contributions.index', ['recurring' => 1]))
            ->assertUnauthorized();
    }

    public function testIndexFailsWithoutPermissionwithRecurringParam(): void
    {
        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.contributions.index', ['recurring' => 1]))
            ->assertForbidden();
    }

    public function testShowFailsForGuestWithRecurringParam(): void
    {
        $this
            ->getJson(route('admin.api.v2.contributions.show', [
                'recurring' => 1,
                'contribution' => Transaction::factory(3)->create()->first()->hashid,
            ]))
            ->assertUnauthorized();
    }

    public function testShowFailsWithoutPermissionWithRecurringParam(): void
    {
        $this
            ->actingAsPassportUser()
            ->getJson(route('admin.api.v2.contributions.show', [
                'recurring' => 1,
                'contribution' => Transaction::factory(3)->create()->first()->hashid,
            ]))
            ->assertForbidden();
    }

    public function testIndexForRecurringSuccess(): void
    {
        $this->createTransactionsWithRPP(7);

        Order::factory(2)->create(); // unpaid orders - should not be returned
        Order::factory(3)->paid()->create(); // orders - should not be returned

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', ['recurring' => 1]))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $jsonResponse->assertCount(7, 'data');
    }

    public function testShowWithTransactionHash(): void
    {
        // we create a few orders to be certain we get back the only one we expect
        Order::factory(10)->paid()->create();
        $transactions = $this->createTransactionsWithRPP(10);
        $transaction = $transactions->first();

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.show', $transaction->prefixed_id))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $this->assertSame($transaction->prefixed_id, $jsonResponse->json('data.id'));
    }

    public function testByHashIdFilter(): void
    {
        $transactions = $this->createTransactionsWithRPP(10);
        $transaction = $transactions->first();

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[id]' => $transaction->hashid,
                'recurring' => 1,
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $jsonResponse->assertCount(1, 'data');
        $this->assertSame($transaction->prefixed_id, $jsonResponse->json('data.0.id'));
    }

    public function testPartialTransactionIdFilter(): void
    {
        $transactions = $this->createTransactionsWithRPP(10);
        $transaction = $transactions->first();

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[contribution_number]' => $transaction->transaction_id,
                'recurring' => 1,
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $jsonResponse->assertCount(1, 'data');
        $this->assertSame($transaction->transaction_id, $jsonResponse->json('data.0.contribution_number'));
    }

    public function testTransactionBeforeDateFilter(): void
    {
        $transactions = $this->createTransactionsWithRPP(4)->map(function (Transaction $transaction) {
            $transaction->order_time = Carbon::today();
            $transaction->save();

            return $transaction;
        });

        $transaction = $transactions->first();
        $transaction->order_time = Carbon::now()->subDays(10);
        $transaction->save();

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[ordered_before]' => Carbon::yesterday()->toDateString(),
                'recurring' => 1,
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data', 'meta']);
        $this->assertSame(1, $jsonResponse['meta']['total']);
    }

    public function testTransactionAfterDateFilter(): void
    {
        $transactions = $this->createTransactionsWithRPP(4)->map(function (Transaction $transaction) {
            $transaction->order_time = Carbon::today();
            $transaction->save();

            return $transaction;
        });

        $transaction = $transactions->first();
        $transaction->order_time = Carbon::now()->subDays(10);
        $transaction->save();

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[ordered_after]' => Carbon::yesterday()->toDateString(),
                'recurring' => 1,
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data', 'meta']);
        $this->assertSame(3, $jsonResponse['meta']['total']);
    }

    public function testTransactionBeforeAndAfterDateFilter(): void
    {
        $transactions = $this->createTransactionsWithRPP(6)->map(function (Transaction $transaction) {
            $transaction->order_time = Carbon::now()->subDays(60);
            $transaction->save();

            return $transaction;
        });

        $transaction = $transactions->first();

        // These should return.
        $transactions->take(2)->each(function (Transaction $transaction) {
            $transaction->order_time = Carbon::now()->subDays(30);
            $transaction->save();
        });

        $transactions->skip(2)->take(2)->each(function (Transaction $transaction) {
            $transaction->order_time = Carbon::now()->subDays(10);
            $transaction->save();
        });

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['recurringpaymentprofile.']))
            ->getJson(route('admin.api.v2.contributions.index', [
                'filter[ordered_before]' => Carbon::now()->subDays(11)->toDateString(),
                'filter[ordered_after]' => Carbon::now()->subDays(59)->toDateString(),
                'recurring' => 1,
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data', 'meta']);
        $this->assertSame(2, $jsonResponse['meta']['total']);
        $this->assertSame($transaction->transaction_id, $jsonResponse->json('data.0.contribution_number'));
    }
}
