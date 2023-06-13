<?php

namespace Tests\Browser\Pages\Accounts;

use Laravel\Dusk\Browser;

/**
 * Based on AccountsLoginPage as these 2 pages are very similar,
 * mainly the url being different.
 */
class AccountsRegisterPage extends AccountsLoginPage
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.accounts.register', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee('Create an Account');
    }
}
