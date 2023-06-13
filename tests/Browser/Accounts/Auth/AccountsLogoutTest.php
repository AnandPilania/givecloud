<?php

namespace Tests\Browser\Accounts\Auth;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Accounts\AccountsHomePage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class AccountsLogoutTest extends DuskTestCase
{
    public function testLogoutFromMenuSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $account = Member::factory()->nps()->create();

            $browser
                ->loginAs($account, 'account_web')
                ->visit(new AccountsHomePage)
                ->openMyAccountDropdownMenu()
                ->click('@menu_logout')
                ->assertRouteIs('frontend.accounts.login');
        });
    }

    public function testLogoutSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $account = Member::factory()->create();

            $browser
                ->loginAs($account, 'account_web')
                ->visit(new AccountsHomePage)
                ->click('@content_logout')
                ->assertRouteIs('frontend.accounts.login');
        });
    }
}
