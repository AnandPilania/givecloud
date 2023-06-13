<?php

namespace Tests\Browser\Backend\Auth;

use Ds\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\BackendDashboardPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class BackendLogoutTest extends DuskTestCase
{
    public function testLogoutSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs(User::factory()->admin()->create())
                ->visit(new BackendDashboardPage)
                ->openUserMenu()
                ->click('@logout')
                ->assertRouteIs('login');
        });
    }
}
