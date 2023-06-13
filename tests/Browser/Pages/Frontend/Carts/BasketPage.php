<?php

namespace Tests\Browser\Pages\Frontend\Carts;

use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class BasketPage extends Page
{
    /** @var \Illuminate\Support\Collection */
    protected $cartItems;

    public function __construct(?Collection $cartItems = null)
    {
        $this->cartItems = $cartItems ?: new Collection();
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.carts.view_cart', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());

        foreach ($this->cartItems as $cartItem) {
            $browser
                ->waitForText($cartItem->variant->product->name)
                ->assertSee($cartItem->quantity);
        }
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@next_step' => '#btn-step-1-next',
        ];
    }
}
