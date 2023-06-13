<?php

namespace Tests\Browser\Themes\Search;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Themes\GlobalSearchPage;
use Tests\Browser\Pages\Themes\HomePage;
use Tests\Browser\Pages\Themes\ProductSearchPage;
use Tests\DuskTestCase;

class SearchScopeTest extends DuskTestCase
{
    /**
     * Ensure searches globally when set
     */
    public function testSearchesProductsOnly(): void
    {
        $this->setThemeSetting('menu_include_search', 1);
        $this->setThemeSetting('menu_search_scope', 'products');

        $this->browse(function (Browser $browser) {
            $searchString = 'test';
            $browser->visit(new HomePage)
                ->showSearchField()
                ->value('@search_input', $searchString)
                ->click('@search_submit')
                ->on(new ProductSearchPage($searchString));
        });
    }

    /**
     * Ensure searches globally when set
     */
    public function testSearchesGlobally(): void
    {
        $this->setThemeSetting('menu_include_search', 1);
        $this->setThemeSetting('menu_search_scope', 'everything');

        $this->browse(function (Browser $browser) {
            $searchString = 'test';
            $browser->visit(new HomePage)
                ->showSearchField()
                ->value('@search_input', $searchString)
                ->click('@search_submit')
                ->on(new GlobalSearchPage($searchString));
        });
    }
}
