<?php

namespace Tests\Feature\Domain\Zapier;

use Ds\Models\Member;
use Ds\Models\Membership;

/**
 * @group zapier
 */
class AccountsControllerTest extends AbstractZapier
{
    public function testIndex(): void
    {
        $account = Member::factory()->create();
        $account->groups()->save(Membership::factory()->create());

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.supporters.index'))
            ->assertOk()
            ->assertJson([[
                'first_name' => $account->first_name,
                'last_name' => $account->last_name,
            ]]);
    }

    public function testIndexWhenMissingToken(): void
    {
        $this
            ->getJson(route('zapier.supporters.index'))
            ->assertUnauthorized();
    }

    public function testIndexWhenZapierIsDisabled(): void
    {
        sys_set('zapier_enabled', false);

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.supporters.index'))
            ->assertUnauthorized();
    }

    public function testIndexWhenNoAccountsInDatabaseYet(): void
    {
        // Make sure there's no record in DB that would render this test useless.
        $this->assertDatabaseCount((new Member)->getTable(), 0);

        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.supporters.index'))
            ->assertOk()
            ->assertJsonStructure([['first_name', 'last_name']]);
    }
}
