<?php

namespace Tests\Browser\Pages\Accounts;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class AccountsLoginPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.accounts.login', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee('Login')
            ->assertSee('Forgot Your Password?');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@login_email' => 'form[name="loginForm"] input[name="email"]',
            '@login_password' => 'form[name="loginForm"] input[name="password"]',
            '@reset_password_button' => 'form[name="resetPasswordForm"] button',
            '@reset_password_email' => 'form[name="resetPasswordForm"] input[name="email"]',
            '@register_account_type' => 'form[name="registerForm"] select[name="account_type_id"]',
            '@register_first_name' => 'form[name="registerForm"] input[name="first_name"]',
            '@register_last_name' => 'form[name="registerForm"] input[name="last_name"]',
            '@register_organization_name' => 'form[name="registerForm"] input[name="organization_name"]',
            '@register_email' => 'form[name="registerForm"] input[name="email"]',
            '@register_zip' => 'form[name="registerForm"] input[name="zip"]',
            '@register_password' => 'form[name="registerForm"] input[name="password"]',
            '@register_button' => 'form[name="registerForm"] button',
        ];
    }

    /**
     * Fill and submit the sign-in form.
     */
    public function signIn(Browser $browser, ?string $email = null, ?string $password = null): void
    {
        $browser
            ->type('@login_email', $email)
            ->type('@login_password', $password)
            ->press('Sign-In');
    }

    /**
     * Fill and submit the reset password form.
     */
    public function resetPassword(Browser $browser, ?string $email = null): void
    {
        $browser
            ->type('@reset_password_email', $email)
            ->click('@reset_password_button');
    }

    /**
     * Fill and submit the register form.
     */
    public function fillRegisterForm(Browser $browser, Member $member, string $password = 'Password123'): void
    {
        $browser
            ->select('@register_account_type', $member->accountType->getKey())
            ->type('@register_first_name', $member->first_name)
            ->type('@register_last_name', $member->last_name)
            ->type('@register_email', $member->email)
            ->type('@register_zip', $member->ship_zip)
            ->type('@register_password', $password);
    }
}
