<?php

namespace Tests\Feature\Domain\Zapier;

use Ds\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithRpps;

/**
 * @group zapier
 */
class ContributionControllerTest extends AbstractZapier
{
    use InteractsWithRpps;
    use WithFaker;

    public function testIndex(): void
    {
        $order = Order::factory()->paid()->create();

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.contributions.index'))
            ->assertOk()
            ->assertJson([[
                'id' => $order->hashid,
                'contribution_number' => $order->client_uuid,
                'currency' => $order->currency,
            ]]);
    }

    public function testIndexWithRecurringParam(): void
    {
        $transaction = $this->createTransactionWithRPP();

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.contributions.index', ['recurring' => 1]), )
            ->assertOk()
            ->assertJson([[
                'id' => $transaction->prefixed_id,
                'contribution_number' => $transaction->transaction_id,
                'currency' => $transaction->currency_code,
            ]]);
    }

    public function testIndexWhenMissingToken(): void
    {
        $this
            ->getJson(route('zapier.contributions.index'))
            ->assertUnauthorized();
    }

    public function testIndexWhenZapierIsDisabled(): void
    {
        sys_set('zapier_enabled', false);

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.contributions.index'))
            ->assertUnauthorized();
    }

    public function testIndexWhenNoOrdersInDatabaseYet(): void
    {
        // Make sure there's no record in DB that would render this test useless.
        $this->assertDatabaseCount((new Order)->getTable(), 0);

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.contributions.index'))
            ->assertOk()
            ->assertJsonStructure([['id', 'contribution_number', 'currency']]);
    }
}
