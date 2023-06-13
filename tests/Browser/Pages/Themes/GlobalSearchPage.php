<?php

namespace Tests\Browser\Pages\Themes;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class GlobalSearchPage extends Page
{
    protected $searchTerm;

    public function __construct(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.search.results', [$this->searchTerm], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }
}
