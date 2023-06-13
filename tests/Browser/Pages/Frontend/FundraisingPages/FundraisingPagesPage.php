<?php

namespace Tests\Browser\Pages\Frontend\FundraisingPages;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class FundraisingPagesPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.fundraising_pages.list_all', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Create a new Fundraising Page link.
     */
    public function clickCreate(Browser $browser): void
    {
        $browser->clickLink('Page Maker');
    }
}
