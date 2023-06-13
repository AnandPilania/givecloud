<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class BackendDashboardPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('backend.session.index', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@user_menu' => 'div[data-testid=topBarMenuUser] div[id^=headlessui-popover-button-] button',
            '@logout' => sprintf('a[href="%s"]', route('backend.session.logout', [], false)),
        ];
    }

    /**
     * Open user dropdown menu.
     */
    public function openUserMenu(Browser $browser): void
    {
        $browser->click('@user_menu');
    }
}
