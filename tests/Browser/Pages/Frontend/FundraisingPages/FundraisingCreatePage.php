<?php

namespace Tests\Browser\Pages\Frontend\FundraisingPages;

use Ds\Models\Product;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class FundraisingCreatePage extends Page
{
    /** @var \Ds\Models\Product */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.fundraising_pages.create', [], false);
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
            '@page_name' => 'input[name="page_name"]',
            '@page_type' => 'label[for="inputPageTypeId-' . $this->product->getKey() . '"]',
            '@submit' => sprintf(
                'form[action="%s"] button[type="submit"]',
                route('frontend.fundraising_pages.insert')
            ),
        ];
    }

    /**
     * Select quantity and submit the form.
     */
    public function typeNameAndSubmit(Browser $browser, string $name): void
    {
        $browser
            ->type('@page_name', $name)
            ->click('@page_type')
            ->click('@submit');
    }
}
