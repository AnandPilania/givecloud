<?php

namespace Tests\Browser\Pages\Accounts;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class AccountsHomePage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.accounts.home', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee('My Home');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@content_logout' => sprintf('.page-content a[href="%s"]', route('frontend.accounts.logout', [], false)),
            '@menu_logout' => sprintf('.headroom a[href="%s"]', route('frontend.accounts.logout', [], false)),
            '@my_account' => '.headroom .nav-item.dropdown a.dropdown-toggle',
        ];
    }

    /**
     * Open "My Account" dropdown menu.
     */
    public function openMyAccountDropdownMenu(Browser $browser): void
    {
        $browser
            ->click('@my_account')
            ->waitForText('Logout');
    }
}
