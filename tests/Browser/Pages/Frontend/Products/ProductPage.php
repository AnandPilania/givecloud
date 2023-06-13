<?php

namespace Tests\Browser\Pages\Frontend\Products;

use Ds\Models\Product;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class ProductPage extends Page
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
        return $this->product->url;
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee($this->product->name);
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@quantity' => 'form.product-add-to-cart select[name="quantity"]',
            '@add_to_cart' => 'form.product-add-to-cart .add-product-btns button[type="submit"]',
            '@next_step' => sprintf('a[href="%s"]', route('frontend.carts.view_cart', [], false)),
        ];
    }

    /**
     * Select quantity and submit the form.
     */
    public function addToCart(Browser $browser, int $quantity = 1): void
    {
        $browser
            ->select('@quantity', $quantity)
            ->click('@add_to_cart')
            ->waitForText('Added to your cart!');
    }
}
