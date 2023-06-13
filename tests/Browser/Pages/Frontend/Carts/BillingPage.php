<?php

namespace Tests\Browser\Pages\Frontend\Carts;

use Laravel\Dusk\Browser;

class BillingPage extends BasketPage
{
    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->waitForText('Billing');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@next_step' => '#btn-step-2-next',
        ];
    }
}
