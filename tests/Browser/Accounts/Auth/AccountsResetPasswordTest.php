<?php

namespace Tests\Browser\Accounts\Auth;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Accounts\AccountsLoginPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class AccountsResetPasswordTest extends DuskTestCase
{
    public function testResetPasswordSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $account = Member::factory()->create([
                'password' => bcrypt($password),
            ]);

            $browser
                ->visit(new AccountsLoginPage)
                ->resetPassword($account->email)
                // Form submitted
                ->assertSourceHas('alert-success')
                ->waitForText(__('frontend/api.emailed_you_a_password_reset_link'));
        });
    }
}
