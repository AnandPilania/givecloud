<?php

namespace Tests\Browser\Pages\Frontend\Carts;

use Laravel\Dusk\Browser;

class ShippingPage extends BasketPage
{
    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->waitForText('Standard Shipping');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@next_step' => '#btn-step-3-next',
        ];
    }
}
