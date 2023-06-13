<?php

namespace Tests\Browser\Pages\Frontend\Carts;

use Laravel\Dusk\Browser;

class PaymentPage extends BasketPage
{
    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->waitForText('Complete Payment')
            ->assertSee('Standard Shipping')
            ->assertSeeIn('.cart-total', $this->calculateCartTotal())
            ->assertSee('Total');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@pay_button' => '#btn-pay',
        ];
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function selectReferral(Browser $browser, ?int $referralLabelPosition = null): void
    {
        $labelSelector = 'first-child';

        if (is_int($referralLabelPosition)) {
            $labelSelector = "nth-child($referralLabelPosition)";
        }

        $browser->click(".referral_sources .referral_source:$labelSelector");
    }

    public function payWithCreditCard(Browser $browser): void
    {
        $browser
            ->type('number', 4242424242424242)
            ->type('exp', 1133) // MM-YY
            ->type('cvv', 123)
            ->click('@pay_button')
            ->waitForReload(); // wait for the transaction to complete
    }

    private function calculateCartTotal(): float
    {
        return $this->cartItems->map(function ($cartItem) {
            return $cartItem->variant->price * $cartItem->quantity;
        })->sum();
    }
}
