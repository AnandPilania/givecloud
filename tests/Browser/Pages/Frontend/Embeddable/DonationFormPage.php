<?php

namespace Tests\Browser\Pages\Frontend\Embeddable;

use Ds\Models\Product;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class DonationFormPage extends Page
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
        return route('embeddable.donation', [$this->product->code], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->waitFor('@next_step');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@donation_value' => '#donationValue',
            '@other_amount' => 'input[aria-label="input other amount"]',
            '@form_error' => '[aria-invalid="true"]',
            '@next_step' => 'button[aria-label="Go to the Next Step"]',
            '@previous_step' => 'button[aria-label="Go to the Previous Step"]',
        ];
    }
}
