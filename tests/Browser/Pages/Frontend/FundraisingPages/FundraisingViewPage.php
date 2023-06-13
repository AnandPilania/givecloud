<?php

namespace Tests\Browser\Pages\Frontend\FundraisingPages;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class FundraisingViewPage extends Page
{
    /** @var string */
    private $pageLink;

    /** @var \Illuminate\Database\Eloquent\Collection */
    private $variants;

    public function __construct(string $pageLink, Collection $variants)
    {
        $this->pageLink = $pageLink;
        $this->variants = $variants;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.fundraising_pages.view', $this->pageLink, false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSourceHas($this->variants->first()->price);
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@donate_button' => '#product-payment-app .btn.btn-success',
        ];
    }

    public function fillInBilling(Browser $browser): void
    {
        $browser
            ->type('billing_email', 'ashley@fiolek.test')
            ->type('billing_phone', '1234567890')
            ->type('title', 'Ms')
            ->type('billing_first_name', 'Ashley')
            ->type('billing_last_name', 'Fiolek')
            ->select('billing_country_code', 'US')
            ->type('billing_address1', '399 Devonshire St')
            ->type('billing_city', 'Dearborn') // MI
            ->select('billing_province_code', 'MI')
            ->type('billing_zip', '48124');
    }

    public function fillInCreditCard(Browser $browser): void
    {
        $browser
            ->type('number', 4242424242424242)
            ->type('exp', 1133) // MM-YY
            ->type('cvv', 123);
    }

    public function selectDonation(Browser $browser, int $variantId): void
    {
        $browser->click('#product-payment-app .product-options label[for="variant_id_' . $variantId . '"]');
    }

    public function waitLoadingElements(Browser $browser): void
    {
        $browser
            ->waitForText('Credit / Debit')
            ->waitForText($this->variants->first()->price);
    }
}
