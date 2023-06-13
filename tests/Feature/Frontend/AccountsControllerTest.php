<?php

namespace Tests\Feature\Frontend;

use Ds\Models\Member;
use Tests\TestCase;

class AccountsControllerTest extends TestCase
{
    public function testOrganizationProfileHasOrganizationField()
    {
        // Enabled only view-profile account feature
        sys_set('account_login_features', ['view-profile']);

        $this
            ->actingAsAccount(Member::factory()->organization()->create())
            ->get(route('accounts.profile'))
            ->assertOk()
            ->assertSee('Organization Name');
    }

    public function testIndividualProfileHasNoOrganizationField()
    {
        // Enabled only view-profile account feature
        sys_set('account_login_features', ['view-profile']);

        $this
            ->actingAsAccount(Member::factory()->individual()->create())
            ->get(route('accounts.profile'))
            ->assertOk()
            ->assertSee('Organization Name');
    }
}
