<?php

namespace Tests\Browser\Accounts\Auth;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Accounts\AccountsLoginPage;
use Tests\Browser\Pages\Accounts\AccountsRegisterPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class AccountsRegisterTest extends DuskTestCase
{
    public function testRegisterAsIndividualFromLogin()
    {
        $this->browse(function (Browser $browser) {
            $account = Member::factory()->individual()->make();

            $browser
                ->visit(new AccountsLoginPage)
                ->fillRegisterForm($account)
                ->click('@register_button')
                // Wait for ajax response and route redirect
                ->waitForRoute('frontend.accounts.home');
        });
    }

    public function testRegisterAsOrganizationFromLogin()
    {
        $this->browse(function (Browser $browser) {
            $organization = Member::factory()->organization()->make();

            $browser
                ->visit(new AccountsLoginPage)
                ->fillRegisterForm($organization)
                ->type('@register_organization_name', $organization->bill_organization_name)
                ->click('@register_button')
                // Wait for ajax response and route redirect
                ->waitForRoute('frontend.accounts.home');
        });
    }

    public function testRegisterAsIndividualFromRegister()
    {
        $this->browse(function (Browser $browser) {
            $organization = Member::factory()->individual()->make();

            $browser
                ->visit(new AccountsRegisterPage)
                ->fillRegisterForm($organization)
                ->click('@register_button')
                // Wait for ajax response and route redirect
                ->waitForRoute('frontend.accounts.home');
        });
    }

    public function testRegisterAsOrganizationFromRegister()
    {
        $this->browse(function (Browser $browser) {
            $organization = Member::factory()->organization()->make();

            $browser
                ->visit(new AccountsRegisterPage)
                ->fillRegisterForm($organization)
                ->type('@register_organization_name', $organization->bill_organization_name)
                ->click('@register_button')
                // Wait for ajax response and route redirect
                ->waitForRoute('frontend.accounts.home');
        });
    }
}
