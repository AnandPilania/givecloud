<?php

namespace Tests\Browser\Backend\Auth;

use Ds\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\BackendDashboardPage;
use Tests\Browser\Pages\Backend\BackendLoginPage;
use Tests\DuskTestCase;

/**
 * @group auth
 */
class BackendLoginTest extends DuskTestCase
{
    public function testLoginSuccessfulReturnsToProfileWhithoutDashboardPerms()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = User::factory()->create([
                'hashed_password' => Hash::make($password),
            ]);

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, $password)
                ->assertRouteIs('backend.profile')
                ->logout();
        });
    }

    public function testLoginSuccessfulShowsDashboardWhenPerms()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = $this->createUserWithPermissions(
                ['dashboard.'],
                ['hashed_password' => Hash::make($password)]
            );

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, $password)
                ->assertRouteIs('backend.session.index')
                ->logout();
        });
    }

    public function testLoginSuccessfulAfterSessionInvalidated()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = $this->createUserWithPermissions(
                ['dashboard.'],
                ['hashed_password' => Hash::make($password)]
            );

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, $password)
                ->on(new BackendDashboardPage);

            $this->deleteBrowserSession($browser);

            $browser
                ->visit((new BackendDashboardPage)->url())
                ->on(new BackendLoginPage)
                ->signIn($user->email, $password)
                ->on(new BackendDashboardPage)
                ->logout();
        });
    }

    public function testLoginWrongPasswordShowsErrorMessage()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'hashed_password' => Hash::make('correct-password'),
            ]);

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, 'wrong-password')
                // error page
                ->assertRouteIs('login')
                ->assertSee(trans('auth.failed'));
        });
    }

    public function testLoginAsSuperUserShowsErrorMessage()
    {
        $this->browse(function (Browser $browser) {
            $user = User::findOrFail(config('givecloud.super_user_id'));
            $user->hashed_password = Hash::make('correct-password');
            $user->save();

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, 'correct-password')
                // error page
                ->assertRouteIs('login')
                ->assertSee(trans('auth.failed'));
        });
    }

    public function testLoginWithoutCredentialsNotSubmitting()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new BackendLoginPage)
                ->signIn()
                ->assertRouteIs('login');
        });
    }

    public function testLoginWithRememberMeAuthenticatesAfterSessionInvalidated()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = $this->createUserWithPermissions(
                ['dashboard.'],
                ['hashed_password' => Hash::make($password)]
            );

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, $password, true)
                ->on(new BackendDashboardPage);

            $this->deleteBrowserSession($browser);

            $browser
                ->visit((new BackendDashboardPage)->url())
                ->on(new BackendDashboardPage);
        });
    }
}
