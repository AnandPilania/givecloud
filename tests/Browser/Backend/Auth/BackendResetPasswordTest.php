<?php

namespace Tests\Browser\Backend\Auth;

use Ds\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\BackendLoginPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class BackendResetPasswordTest extends DuskTestCase
{
    public function testResetPasswordSuccessful()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = User::factory()->create([
                'password' => $password,
            ]);

            $browser
                ->visit(new BackendLoginPage)
                ->clickLink('Forgot your password?')
                // password reset page
                ->assertRouteIs('password.request')
                ->type('email', $user->email)
                ->press('Password Reset')
                // password reset is successful
                ->assertRouteIs('password.request')
                ->assertSee(trans('passwords.sent'));
        });
    }
}
