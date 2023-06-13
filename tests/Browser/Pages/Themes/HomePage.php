<?php

namespace Tests\Browser\Pages\Themes;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class HomePage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.home', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@show_search' => '.search-toggle',
            '@search_input' => '.site-search input[name=keywords]',
            '@search_submit' => '.site-search button[type=submit]',
        ];
    }

    /**
     * Show Search Field
     */
    public function showSearchField(Browser $browser): void
    {
        $browser->click('@show_search');
    }
}
