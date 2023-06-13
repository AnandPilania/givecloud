<?php

namespace Tests\Browser\Accounts\Auth;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Accounts\AccountsLoginPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class AccountsLoginTest extends DuskTestCase
{
    public function testLoginSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $account = Member::factory()->create([
                'password' => bcrypt($password),
            ]);

            $browser
                ->visit(new AccountsLoginPage)
                ->signIn($account->email, $password)
                // Wait for ajax response and route redirect
                ->waitForRoute('frontend.accounts.home');
        });
    }

    public function testLoginWrongPasswordShowsErrorMessage()
    {
        $this->browse(function (Browser $browser) {
            $account = Member::factory()->create([
                'password' => bcrypt('correct-password'),
            ]);

            $browser
                ->visit(new AccountsLoginPage)
                ->signIn($account->email, 'wrong-password')
                // Wait for ajax response and error message
                ->waitForText('Incorrect email/password');
        });
    }

    public function testLoginWithoutCredentialsNotSubmitting()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new AccountsLoginPage)
                ->signIn()
                ->assertRouteIs('frontend.accounts.login');
        });
    }
}
